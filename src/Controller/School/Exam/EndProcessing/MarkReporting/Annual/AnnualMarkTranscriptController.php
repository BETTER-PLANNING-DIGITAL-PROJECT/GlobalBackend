<?php

namespace App\Controller\School\Exam\EndProcessing\MarkReporting\Annual;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\GetConfigurationsUtil;
use App\Controller\School\Exam\EndProcessing\MarkReporting\Annual\AnnualMarkPrimaryTranscriptGetAllMarksCalculatedUtil;
use App\Controller\School\Exam\EndProcessing\MarkValidation\Services\Period\PeriodMarkValidationGetAllMarksCalculatedUtil;
use App\Controller\School\Exam\EndProcessing\MarkValidation\Services\Annual\AnnualMarkValidationValidateDeleteUtil;
use App\Repository\School\Exam\Configuration\ClassWeightingRepository;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\Repository\School\Exam\Configuration\FormulaThRepository;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use App\Repository\School\Exam\Configuration\SchoolWeightingRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Configuration\SpecialityWeightingRepository;
use App\Repository\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Annual\Module\MarkAnnualModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Annual\Module\MarkAnnualModuleCalculationRelationRepository;
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
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use App\Repository\Setting\School\PeriodTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\School\Exam\EndProcessing\MarkReporting\Annual\AnnualMarkTernaryTranscriptGetAllMarksCalculatedUtil;

class AnnualMarkTranscriptController extends AbstractController
{

    public function __construct(

        private readonly PeriodTypeRepository $periodTypeRepository,
        private readonly StudentRegistrationRepository                           $studentRegistrationRepository,
        private readonly EvaluationPeriodRepository                              $evaluationPeriodRepository,
        private readonly StudentCourseRegistrationRepository                     $studentCourseRegistrationRepository,
        private readonly TeacherCourseRegistrationRepository                     $teacherCourseRegistrationRepository,
        private readonly SequenceRepository                              $sequenceRepository,


        private readonly MarkGradeRepository $markGradeRepository,

        private readonly SchoolWeightingRepository                             $schoolWeightingRepository,
        private readonly ClassWeightingRepository                              $classWeightingRepository,
        private readonly SpecialityWeightingRepository                         $specialityWeightingRepository,
        private readonly FormulaThRepository                                   $formulaThRepository,
        private readonly ExamInstitutionSettingsRepository                     $examInstitutionSettingsRepository,

        private readonly MarkSequenceCourseCalculatedRepository                $markSequenceCourseCalculatedRepository,
        private readonly MarkSequenceModuleCalculatedRepository                $markSequenceModuleCalculatedRepository,
        private readonly MarkSequenceGeneralAverageCalculatedRepository        $markSequenceGeneralAverageCalculatedRepository,
        private readonly MarkPeriodCourseCalculatedRepository                  $markPeriodCourseCalculatedRepository,
        private readonly MarkPeriodCourseCalculationRelationRepository          $markPeriodCourseCalculationRelationRepository,
        private readonly MarkPeriodModuleCalculatedRepository                  $markPeriodModuleCalculatedRepository,
        private readonly MarkPeriodModuleCalculationRelationRepository         $markPeriodModuleCalculationRelationRepository,
        private readonly MarkPeriodGeneralAverageCalculatedRepository          $markPeriodGeneralAverageCalculatedRepository,
        private readonly MarkAnnualCourseCalculatedRepository          $markAnnualCourseCalculatedRepository,
        private readonly MarkAnnualModuleCalculatedRepository          $markAnnualModuleCalculatedRepository,
        private readonly MarkAnnualModuleCalculationRelationRepository          $markAnnualModuleCalculationRelationRepository,
        private readonly MarkAnnualGeneralAverageCalculatedRepository          $markAnnualGeneralAverageCalculatedRepository
    )
    {

    }

    function getEvaluationPeriods(string $sequencePonderations): array
    {
        // 1:0:3!3.4-1:0:4!2.3
        $sequencePonderations = explode('-', $sequencePonderations); // ['1:0:3!3.4','1:0:4!2.3']
        $evaluationPeriods = [];
        foreach ($sequencePonderations as $sequencePonderation) {
            $sequencePonderationData = explode('!', $sequencePonderation); // ['1:0:3','3.4']
            $sequenceData = explode(':', $sequencePonderationData[0]); // ['1','0','3']
            $periodTypeWeightingId = $sequenceData[0]; // 1
            $periodType = $this->periodTypeRepository->find($periodTypeWeightingId); // 1
            $divisionNumberId = (int) $sequenceData[1]; // 0
            $evaluationPeriod = $this->evaluationPeriodRepository->findOneBy(['periodType'=>$periodType,'number'=> $divisionNumberId+1]);
            if ($evaluationPeriod) {
                $sequenceId = $sequenceData[2]; // 3
                $sequence = $this->sequenceRepository->find($sequenceId);
                $weighting = isset($sequencePonderationData[1]) && $sequencePonderationData[1] !== 'null' && $sequencePonderationData[1] !== '' ? floatval($sequencePonderationData[1]) : null;
                if (isset($weighting)) {
                    $evaluationPeriods[$evaluationPeriod->getId()][] = $sequence;
                }
            }
        }
        return $evaluationPeriods;
    }

    // Afficher les notes sequentielles d'un etudiant
    #[Route('api/school/mark/annual/transcript/{schoolSystem}/get-all-marks/{data}', name: 'school_mark_annual_transcript_get_all_marks')]
    public function getAllMarks(string $data,string $schoolSystem): JsonResponse
    {
        $markData = json_decode($data, true);

        $studentId = $markData['studentId'];

        $student = $this->studentRegistrationRepository->find($studentId);
        $class = $student->getCurrentClass();

        $year = $student->getCurrentYear();

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
//        4:0:3!null-4:0:4!null-4:0:5!-4:1:3!-4:1:4!-4:1:5!
        if (!$examInstitutionSettings) return $this->json('notExamInstitutionSettings');

        $showExcludedBallots = $examInstitutionSettings->getShowExcludedBallots();
        if (!$showExcludedBallots && $student->getStatus() === 'resigned') return $this->json('resigned');

        $maxWeighting = $configurationsUtil->getMaxWeighting($class);
        if (!$maxWeighting) return $this->json('notMaxWeighting');

        // sequence ponderations

        // { 1:0:3!0.4-1:0:3!0.6 }
        $sequences = [];
        $evaluationPeriods = [];

        $sequencePonderations = $maxWeighting->getSequencePonderations();
        if (!$sequencePonderations) return $this->json('notSequencePonderations');

        // S'assurer que soit c'est vide soit c'est sur le bon format
        $evaluationPeriods = $sequences = [];

        $evaluationPeriods = $this->getEvaluationPeriods($sequencePonderations);
        if (empty($evaluationPeriods)) return $this->json('notEvaluationPeriod');

/*        $sequences = array_merge(...array_values($evaluationPeriods));
        if (empty($sequences)) return 'notSequences';*/

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
                $class = AnnualMarkPrimaryTranscriptGetAllMarksCalculatedUtil::class;
                break;
            case 'secondary':
                $class = AnnualMarkSecondaryTranscriptGetAllMarksCalculatedUtil::class;
                break;
            case 'ternary':
                $class = AnnualMarkTernaryTranscriptGetAllMarksCalculatedUtil::class;
        }
        $evaluationPeriodsArray = $evaluationPeriods;
        $evaluationPeriods = array_map(fn(int $evaluationPeriodId)=>$this->evaluationPeriodRepository->find($evaluationPeriodId),array_keys($evaluationPeriodsArray));
        $sequences = $evaluationPeriodsArray[$evaluationPeriods[0]->getId()];

        // Effectif de la classe
        $classHeadcount = $this->studentRegistrationRepository->count(['currentClass'=>$student->getCurrentClass()]);

        $annualMarkTranscriptGetAllMarksCalculatedUtil = new $class($displaySequencesInBulletins,$displayNumberOfRows,$displayNumberOfRowsOnCourses,$displayTheClassSizeInRows,$calculateSubjectsRanks,$displayMarksNotEnteredInTheBulletin,$showPhotoOnSummary,$classHeadcount,$student,$evaluationPeriods,$sequences,$evaluationPeriodsArray,$this->evaluationPeriodRepository,$this->studentCourseRegistrationRepository,$this->markGradeRepository,$this->markSequenceCourseCalculatedRepository,$this->markSequenceModuleCalculatedRepository,$this->markSequenceGeneralAverageCalculatedRepository,$this->markPeriodCourseCalculatedRepository, $this->markPeriodModuleCalculatedRepository, $this->markPeriodModuleCalculationRelationRepository, $this->markPeriodGeneralAverageCalculatedRepository,
            $this->markAnnualCourseCalculatedRepository,
            $this->markAnnualModuleCalculatedRepository,
            $this->markAnnualModuleCalculationRelationRepository,
            $this->markAnnualGeneralAverageCalculatedRepository,
            $this->studentRegistrationRepository,
            $this->teacherCourseRegistrationRepository
            );
        $allMarksCalculatedDatas = $annualMarkTranscriptGetAllMarksCalculatedUtil->getAllMarksCalculated($student);

        return $this->json($allMarksCalculatedDatas);
    }
}