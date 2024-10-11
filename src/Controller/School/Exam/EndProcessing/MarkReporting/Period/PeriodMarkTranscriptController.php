<?php

namespace App\Controller\School\Exam\EndProcessing\MarkReporting\Period;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\GetConfigurationsUtil;
use App\Controller\School\Exam\EndProcessing\MarkValidation\Services\Period\PeriodMarkValidationGetAllMarksCalculatedUtil;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Exam\Configuration\ClassWeightingRepository;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\Repository\School\Exam\Configuration\FormulaThRepository;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use App\Repository\School\Exam\Configuration\SchoolWeightingRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Configuration\SpecialityWeightingRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCourseCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageSequenceCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\Module\MarkPeriodModuleCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculatedRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PeriodMarkTranscriptController extends AbstractController
{

    public function __construct(
        private readonly StudentRegistrationRepository                           $studentRegistrationRepository,
        private readonly TeacherCourseRegistrationRepository                           $teacherCourseRegistrationRepository,
        private readonly EvaluationPeriodRepository                              $evaluationPeriodRepository,
        private readonly SequenceRepository                              $sequenceRepository,

        private readonly MarkGradeRepository                            $markGradeRepository,

        private readonly SchoolWeightingRepository                             $schoolWeightingRepository,
        private readonly ClassWeightingRepository                              $classWeightingRepository,
        private readonly SpecialityWeightingRepository                         $specialityWeightingRepository,
        private readonly FormulaThRepository                                   $formulaThRepository,
        private readonly ExamInstitutionSettingsRepository                     $examInstitutionSettingsRepository,

        private readonly MarkSequenceCourseCalculatedRepository                $markSequenceCourseCalculatedRepository,
        private readonly MarkSequenceModuleCalculatedRepository                $markSequenceModuleCalculatedRepository,
        private readonly MarkSequenceGeneralAverageCalculatedRepository        $markSequenceGeneralAverageCalculatedRepository,
        private readonly MarkPeriodCourseCalculatedRepository                  $markPeriodCourseCalculatedRepository,
        private readonly MarkPeriodModuleCalculatedRepository                  $markPeriodModuleCalculatedRepository,
        private readonly MarkPeriodModuleCalculationRelationRepository         $markPeriodModuleCalculationRelationRepository,
        private readonly MarkPeriodGeneralAverageCalculatedRepository          $markPeriodGeneralAverageCalculatedRepository
    )
    {

    }

    function getSequenceWeightings(string $sequencePonderations, int $periodTypeId, int $divisionNumber): array
    {
        // 1:0:3!3.4-1:0:4!2.3
        $sequencePonderations = explode('-', $sequencePonderations); // ['1:0:3!3.4','1:0:4!2.3']
        $sequenceWeightings = [];
        $sequenceWeightings['sequences'] = [];
        foreach ($sequencePonderations as $sequencePonderation) {
            $sequencePonderationData = explode('!', $sequencePonderation); // ['1:0:3','3.4']
            $sequenceData = explode(':', $sequencePonderationData[0]); // ['1','0','3']
            $periodTypeWeightingId = $sequenceData[0]; // 1
            $divisionNumberId = $sequenceData[1]; // 0
            if ($periodTypeWeightingId == $periodTypeId && $divisionNumberId == $divisionNumber) {
                $sequenceId = $sequenceData[2]; // 3
                $weighting = isset($sequencePonderationData[1]) && $sequencePonderationData[1] !== 'null' && $sequencePonderationData[1] !== '' ? floatval($sequencePonderationData[1]) : null;
                if (isset($weighting)) {
                    $sequence = $this->sequenceRepository->find($sequenceId);
                    $sequenceWeightings['sequences'][] = $sequence;
                }
            }
        }
        return $sequenceWeightings;
    }

    // Afficher les notes sequentielles d'un etudiant
    #[Route('api/school/mark/period/transcript/{schoolSystem}/get-all-marks/{data}', name: 'school_mark_period_transcript_get_all_marks')]
    public function getAllMarks(string $data,string $schoolSystem): JsonResponse
    {
        $markData = json_decode($data, true);

        $studentId = $markData['studentId'];
        $evaluationPeriodId = $markData['evaluationPeriodId'];

        $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
        $student = $this->studentRegistrationRepository->find($studentId);

        // sequences

        $year = $student->getCurrentYear();
        $class = $student->getCurrentClass();

        $configurationsUtil = new GetConfigurationsUtil(
            $year,
            $this->formulaThRepository,
            $this->examInstitutionSettingsRepository,
            $this->schoolWeightingRepository,
            $this->classWeightingRepository,
            $this->specialityWeightingRepository,
            $this->markGradeRepository
        );

        $examInstitutionSettings = $configurationsUtil->getExamInstitutionSettings();

        if (!$examInstitutionSettings) return $this->json('notExamInstitutionSettings');

        $showExcludedBallots = $examInstitutionSettings->getShowExcludedBallots();
        if (!$showExcludedBallots && $student->getStatus() === 'resigned') return $this->json('resigned');

        $maxWeighting = $configurationsUtil->getMaxWeighting($class);
        if (!$maxWeighting) return $this->json('notMaxWeighting');

        // sequence ponderations
        $periodType = $maxWeighting->getPeriodType();
        $numberOfDivision = $maxWeighting->getNumberOfDivision();
        $number = $evaluationPeriod->getNumber();
        if ($periodType !== $evaluationPeriod->getPeriodType()) {
            return $this->json('differentPeriodType');
        } else if ($numberOfDivision < $number) return $this->json('differentNumberOfDivision');

        // { 1:0:3!0.4-1:0:3!0.6 }
        $sequences = [];
        $weightings = [];

        $sequencePonderations = $maxWeighting->getSequencePonderations();
        if (!$sequencePonderations) return $this->json('notSequencePonderations');

        // S'assurer que soit c'est vide soit c'est sur le bon format

        $sequenceWeightings = $this->getSequenceWeightings($sequencePonderations, $periodType->getId(), $number - 1);
        $sequenceDatas = $sequenceWeightings;
        $sequences = (array) $sequenceDatas['sequences'];
        if (empty($sequences)) return $this->json('notSequences');

        $displaySequencesInBulletins = $examInstitutionSettings->getDisplaySequencesInBulletins();
        $displayNumberOfRows = $examInstitutionSettings->getDisplayNumberOfRows();
        $displayNumberOfRowsOnCourses = $examInstitutionSettings->getDisplayNumberOfRowsOnCourses();
        $displayTheClassSizeInRows = $examInstitutionSettings->getDisplayTheClassSizeInRows();
        $calculateSubjectsRanks = $examInstitutionSettings->getCalculateSubjectsRanks();
        $displayMarksNotEnteredInTheBulletin = $examInstitutionSettings->getDisplayMarksNotEnteredInTheBulletin();
        $showPhotoOnSummary = $examInstitutionSettings->getShowPhotoOnSummary();

        $class = null;
        switch ($schoolSystem){
            case 'primary':
                $class = PeriodMarkPrimaryTranscriptGetAllMarksCalculatedUtil::class;
                break;
            case 'secondary':
                $class = PeriodMarkSecondaryTranscriptGetAllMarksCalculatedUtil::class;
                break;
            case 'ternary':
                $class = PeriodMarkTernaryTranscriptGetAllMarksCalculatedUtil::class;
        }

        // Recuperer les periodes d'evaluation precedentes
        $previousEvaluationPeriods = $this->evaluationPeriodRepository->findPreviousEvaluationPeriod($evaluationPeriod);

        // Effectif de la classe
        $classHeadcount = $this->studentRegistrationRepository->count(['currentClass'=>$student->getCurrentClass()]);

        $periodMarkTranscriptGetAllMarksCalculatedUtil = new $class($displaySequencesInBulletins,$displayNumberOfRows,$displayNumberOfRowsOnCourses,$displayTheClassSizeInRows,$calculateSubjectsRanks,$displayMarksNotEnteredInTheBulletin,$showPhotoOnSummary,$classHeadcount,$evaluationPeriod,$sequences,$previousEvaluationPeriods,$this->markGradeRepository,$this->markSequenceCourseCalculatedRepository,$this->markSequenceModuleCalculatedRepository,$this->markSequenceGeneralAverageCalculatedRepository,$this->markPeriodCourseCalculatedRepository, $this->markPeriodModuleCalculatedRepository, $this->markPeriodModuleCalculationRelationRepository, $this->markPeriodGeneralAverageCalculatedRepository,
            $this->studentRegistrationRepository,
        $this->teacherCourseRegistrationRepository);
        $allMarksCalculatedDatas = $periodMarkTranscriptGetAllMarksCalculatedUtil->getAllMarksCalculated($student, $evaluationPeriod);

        return $this->json($allMarksCalculatedDatas);
    }
}