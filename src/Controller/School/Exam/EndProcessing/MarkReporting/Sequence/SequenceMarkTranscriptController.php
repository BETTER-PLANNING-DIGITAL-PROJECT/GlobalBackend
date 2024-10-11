<?php

namespace App\Controller\School\Exam\EndProcessing\MarkReporting\Sequence;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\GetConfigurationsUtil;
use App\Repository\School\Exam\Configuration\ClassWeightingRepository;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\Repository\School\Exam\Configuration\FormulaThRepository;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use App\Repository\School\Exam\Configuration\SchoolWeightingRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Configuration\SpecialityWeightingRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculationRelationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SequenceMarkTranscriptController extends AbstractController
{

    public function __construct(
        private readonly StudentRegistrationRepository                           $studentRegistrationRepository,
        private readonly TeacherCourseRegistrationRepository                           $teacherCourseRegistrationRepository,
        private readonly EvaluationPeriodRepository                              $evaluationPeriodRepository,
        private readonly SequenceRepository                              $sequenceRepository,

        private readonly MarkGradeRepository                            $markGradeRepository,

        private readonly SchoolWeightingRepository                      $schoolWeightingRepository,
        private readonly ClassWeightingRepository                       $classWeightingRepository,
        private readonly SpecialityWeightingRepository                  $specialityWeightingRepository,
        private readonly FormulaThRepository                            $formulaThRepository,
        private readonly ExamInstitutionSettingsRepository              $examInstitutionSettingsRepository,

        private readonly MarkSequenceCourseCalculatedRepository                  $markSequenceCourseCalculatedRepository,
        private readonly MarkSequenceModuleCalculatedRepository                  $markSequenceModuleCalculatedRepository,
        private readonly MarkSequenceGeneralAverageCalculatedRepository         $markSequenceGeneralAverageCalculatedRepository,
        private readonly MarkSequenceCourseCalculationRelationRepository         $markSequenceCourseCalculationRelationRepository,
        private readonly MarkSequenceModuleCalculationRelationRepository         $markSequenceModuleCalculationRelationRepository,
    )
    {

    }

    // Afficher les notes sequentielles d'un etudiant
    #[Route('api/school/mark/sequence/transcript/{schoolSystem}/get-all-marks/{data}', name: 'school_mark_sequence_transcript_get_all_marks')]
    public function getAllMarks(string $data,string $schoolSystem): JsonResponse
    {
        $markData = json_decode($data, true);

        $studentId = $markData['studentId'];
        $evaluationPeriodId = $markData['evaluationPeriodId'];
        $sequenceId = $markData['sequenceId'];

        $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
        $student = $this->studentRegistrationRepository->find($studentId);
        $sequence = $this->sequenceRepository->find($sequenceId);

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

        $displayNumberOfRows = $examInstitutionSettings->getDisplayNumberOfRows();
        $displayNumberOfRowsOnCourses = $examInstitutionSettings->getDisplayNumberOfRowsOnCourses();
        $displayTheClassSizeInRows = $examInstitutionSettings->getDisplayTheClassSizeInRows();
        $calculateSubjectsRanks = $examInstitutionSettings->getCalculateSubjectsRanks();
        $displayMarksNotEnteredInTheBulletin = $examInstitutionSettings->getDisplayMarksNotEnteredInTheBulletin();
        $showPhotoOnSummary = $examInstitutionSettings->getShowPhotoOnSummary();

        $class = null;
        // Il y a seulement les bulletins de notes de sequences pour les etablissements secondaires
        // Donc les cas primary et ternary ne sont pas a traiter pour le moment
        switch ($schoolSystem){
            case 'primary':
                $class = SequenceMarkPrimaryTranscriptGetAllMarksCalculatedUtil::class;
                break;
            case 'secondary':
                $class = SequenceMarkSecondaryTranscriptGetAllMarksCalculatedUtil::class;
                break;
            case 'ternary':
                $class = SequenceMarkTernaryTranscriptGetAllMarksCalculatedUtil::class;
        }

        // Recuperer les periodes d'evaluation precedentes
        $previousEvaluationPeriods = $this->evaluationPeriodRepository->findPreviousEvaluationPeriod($evaluationPeriod);

        // Effectif de la classe
        $classHeadcount = $this->studentRegistrationRepository->count(['currentClass'=>$student->getCurrentClass()]);

        $sequences = $this->sequenceRepository->findPreviousSequences($sequence);
        $sequenceMarkTranscriptGetAllMarksCalculatedUtil = new $class($displayNumberOfRows,$displayNumberOfRowsOnCourses,$displayTheClassSizeInRows,$calculateSubjectsRanks,$displayMarksNotEnteredInTheBulletin,$showPhotoOnSummary,$classHeadcount,$evaluationPeriod,$sequence,$sequences,$previousEvaluationPeriods,$this->markGradeRepository,$this->markSequenceCourseCalculatedRepository,$this->markSequenceModuleCalculatedRepository, $this->markSequenceModuleCalculationRelationRepository,$this->markSequenceGeneralAverageCalculatedRepository,
            $this->studentRegistrationRepository,
        $this->teacherCourseRegistrationRepository);
        $allMarksCalculatedDatas = $sequenceMarkTranscriptGetAllMarksCalculatedUtil->getAllMarksCalculated($student, $evaluationPeriod,$sequence);

        return $this->json($allMarksCalculatedDatas);
    }
}