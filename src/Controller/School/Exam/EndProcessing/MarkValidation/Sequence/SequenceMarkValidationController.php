<?php

namespace App\Controller\School\Exam\EndProcessing\MarkValidation\Sequence;

use App\Controller\School\Exam\EndProcessing\MarkValidation\Services\Sequence\SequenceMarkValidationGetAllMarksCalculatedUtil;
use App\Controller\School\Exam\EndProcessing\MarkValidation\Services\Sequence\SequenceMarkValidationValidateDeleteUtil;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Module\MarkSequenceModuleCalculationRelationRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SequenceMarkValidationController extends AbstractController
{

    public function __construct(
        private readonly SchoolClassRepository                           $classRepository,
        private readonly ClassProgramRepository $classProgramRepository,
        private readonly StudentRegistrationRepository                           $studentRegistrationRepository,
        private readonly EvaluationPeriodRepository                              $evaluationPeriodRepository,
        private readonly SequenceRepository                                      $sequenceRepository,
        private readonly ExamInstitutionSettingsRepository                       $examInstitutionSettingsRepository,

        private readonly MarkSequenceCourseCalculatedRepository                  $markSequenceCourseCalculatedRepository,
        private readonly MarkSequenceCourseCalculationRelationRepository         $markSequenceCourseCalculationRelationRepository,
        private readonly MarkSequenceModuleCalculatedRepository                  $markSequenceModuleCalculatedRepository,
        private readonly MarkSequenceModuleCalculationRelationRepository         $markSequenceModuleCalculationRelationRepository,
        private readonly MarkSequenceGeneralAverageCalculatedRepository          $markSequenceGeneralAverageCalculatedRepository,
        private readonly MarkSequenceGeneralAverageCalculationRelationRepository $markSequenceGeneralAverageCalculationRelationRepository,
        private readonly EntityManagerInterface                                  $entityManager
    )
    {

    }

    // Afficher les notes sequentielles d'un etudiant
    #[Route('api/school/mark/sequence/get-all-marks/{data}', name: 'school_mark_sequence_get_all_marks')]
    public function getAllMarks(string $data): JsonResponse
    {
        $markData = json_decode($data, true);

        $studentId = $markData['studentId'];
        $evaluationPeriodId = $markData['evaluationPeriodId'];
        $sequenceId = $markData['sequenceId'];

        $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
        $sequence = $this->sequenceRepository->find($sequenceId);
        $student = $this->studentRegistrationRepository->find($studentId);
        $sequenceMarkValidationGetAllMarksCalculatedUtil = new SequenceMarkValidationGetAllMarksCalculatedUtil($this->markSequenceCourseCalculatedRepository, $this->markSequenceModuleCalculatedRepository, $this->markSequenceModuleCalculationRelationRepository, $this->markSequenceGeneralAverageCalculatedRepository);
        $allMarksCalculatedDatas = $sequenceMarkValidationGetAllMarksCalculatedUtil->getAllMarksCalculated($student, $evaluationPeriod, $sequence);

        return $this->json($allMarksCalculatedDatas);
    }

    // Valider ou supprimer les notes
    #[Route('api/school/mark/sequence/validateOrDelete/{data}', name: 'school_mark_sequence_validate_or_delete')]
    public function validateOrDelete(string $data): JsonResponse
    {
        $markData = json_decode($data, true);

        $evaluationPeriodId = $markData['evaluationPeriodId'];
        $sequenceId = $markData['sequenceId'];
        $validate = $markData['validate'];

        $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
        $sequence = $this->sequenceRepository->find($sequenceId);

        $students = null;
        if (isset($markData['studentIds'])) {
            $studentIds = $markData['studentIds'];
            $students = array_map(fn(int $studentId) => $this->studentRegistrationRepository->find($studentId), $studentIds);
        }
        else{
            $classId = $markData['classId'];
            $class = $this->classRepository->find($classId);
            $students = $this->studentRegistrationRepository->findBy(['currentClass' => $class]);
        }

        $institution = $sequence->getInstitution();
        $examInstitutionSettings = $this->examInstitutionSettingsRepository->findOneBy(['institution' => $institution]);
        if (!$examInstitutionSettings) return $this->json('notExamInstitutionSettings');

        $dontClassifyTheExcluded = $examInstitutionSettings->getDontClassifyTheExcluded();
        $calculateSubjectsRanks = $examInstitutionSettings->getCalculateSubjectsRanks();

        $sequenceMarkValidationValidateDeleteUtil = new SequenceMarkValidationValidateDeleteUtil(
            $calculateSubjectsRanks,
            $dontClassifyTheExcluded,
            $validate,
            $evaluationPeriod,
            $sequence,
            $this->classProgramRepository,
            $this->markSequenceCourseCalculatedRepository,
            $this->markSequenceCourseCalculationRelationRepository,
            $this->markSequenceModuleCalculatedRepository,
            $this->markSequenceModuleCalculationRelationRepository,
            $this->markSequenceGeneralAverageCalculatedRepository,
            $this->markSequenceGeneralAverageCalculationRelationRepository,
            $this->entityManager
        );
        $result = $sequenceMarkValidationValidateDeleteUtil->validateOrDeleteMarks('Students', $students);

        return $this->json($result);
    }
}