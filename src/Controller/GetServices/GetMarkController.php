<?php

namespace App\Controller\GetServices;

use App\Controller\GlobalController;
use App\Entity\School\Exam\Operation\Mark;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\Repository\School\Exam\Configuration\NoteTypeRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class GetMarkController extends AbstractController
{
    public function __construct(
        private readonly GlobalController $globalController,
        private readonly SchoolRepository                  $schoolRepository,
        private readonly SchoolClassRepository             $classRepository,
        private readonly ClassProgramRepository            $classProgramRepository,
        private readonly SequenceRepository                $sequenceRepository,
        private readonly EvaluationPeriodRepository        $evaluationPeriodRepository,
        private readonly NoteTypeRepository                $noteTypeRepository,
        private readonly StudentRegistrationRepository     $studentRegistrationRepository,
        private readonly StudentRepository                 $studentRepository,
        private readonly MarkRepository                    $markRepository,
        private readonly ExamInstitutionSettingsRepository $examInstitutionSettingsRepository
    )
    {
    }

    public function getUser(): User
    {
        return $this->globalController->getUser();
    }

    #[Route('/api/get/mark/sequence/{schoolMarkData}', name: 'get_mark_by_sequence')]
    public function getMarkBySequence(string $schoolMarkData): JsonResponse
    {
        $schoolMark = json_decode($schoolMarkData, true);
        $sequenceId = $schoolMark['sequenceId'];
        $property = isset($schoolMark['property']) && $schoolMark['property'] !== null ? $schoolMark['property'] : null;
        $noteType = isset($schoolMark['noteTypeId']) && $schoolMark['noteTypeId'] !== null ? $this->noteTypeRepository->find($schoolMark['noteTypeId']) : null;

        $sequence = $this->sequenceRepository->find($sequenceId);
        $institution = $sequence->getInstitution();


        $criteria = ['institution' => $institution, 'noteType' => $noteType, 'sequence' => $sequence];

        if (isset($schoolMark['evaluationPeriodId'])) {
            $evaluationPeriod = $this->evaluationPeriodRepository->find($schoolMark['evaluationPeriodId']);
            $criteria['evaluationPeriod'] = $evaluationPeriod;
        }

        $class = null;
        $classProgram = null;

        if (isset($schoolMark['classId'])) {
            $classId = $schoolMark['classId'];
            $class = $this->classRepository->find($classId);
            $criteria['class'] = $class;
        }

        if (isset($schoolMark['classProgramId'])) {
            $classProgramId = $schoolMark['classProgramId'];
            $classProgram = $this->classProgramRepository->find($classProgramId);
            $criteria['classProgram'] = $classProgram;
        } else {
            $studRegistration = null;
            if (isset($schoolMark['studentId'])) {
                $studentId = $schoolMark['studentId'];
                $studRegistration = $this->studentRegistrationRepository->find($studentId);
            } else {
                $currentStudent = $this->studentRepository->findOneBy(['operator' => $this->getUser()]);
                $studRegistration = $this->studentRegistrationRepository->findOneBy(['student' => $currentStudent, 'currentYear' => $sequence->getYear()]);
            }
            $criteria['student'] = $studRegistration;
        }

        $examInstitutionSettings = $this->examInstitutionSettingsRepository->findOneBy(['institution' => $institution]);
        $hideNamesIfAnonymityAvailable = $examInstitutionSettings->getHideNamesIfAnonymityAvailable();
        $schoolMarks = $this->markRepository->findBy($criteria);

        // hideNamesIfAnonymityAvailable = false | true (0 | 1)
        $formatter0 = function (Mark $schoolMark) {
            $student = $schoolMark->getStudent()->getStudent();
            $assignmentDate = $schoolMark->getAssignmentDate();
            $assignmentDate = isset($assignmentDate) ? date_format($assignmentDate, 'Y-m-d') : null;
            $classProgram = $schoolMark->getClassProgram();
            return [
                'evaluationPeriodName' => $classProgram->getEvaluationPeriod()->getName(),
                'name' => $student->getFirstName() . " " . $student->getName(),
                'fullName' => $student->getFirstName() . " " . $student->getName() . " " . $student->getMatricule(),
                'studentName' => $student->getFirstName() . " " . $student->getName() . " " . $student->getMatricule(),
                'markEntered' => $schoolMark->getMarkEntered(),
                'matricule' => $student->getMatricule(),
                'id' => $schoolMark->getId(),
                'anonymity code' => $schoolMark->getAnonymityCode(),
                'anonymityCode' => $schoolMark->getAnonymityCode(),
                'mark' => $schoolMark->getMark(),
                'base' => $schoolMark->getBase(),
                'weighting' => $schoolMark->getWeighting(),
                'assignmentDate' => $assignmentDate,
                'description' => $schoolMark->getDescription(),
                'subjectName' => $classProgram->getNameuvc(),
                'isSimulated' => $schoolMark->getIsSimulated(),
                'isOpen' => $schoolMark->isIsOpen()
            ];
        };
        $formatter1 = function (Mark $schoolMark) {
            $student = $schoolMark->getStudent()->getStudent();
            $assignmentDate = $schoolMark->getAssignmentDate();
            $assignmentDate = isset($assignmentDate) ? date_format($assignmentDate, 'Y-m-d') : null;
            $classProgram = $schoolMark->getClassProgram();

            $anonymityCode = $schoolMark->getAnonymityCode();
            $name = $student->getFirstName() . " " . $student->getName();
            $matricule = $student->getMatricule();

            $nameMatricule = $student->getName() . " " . $matricule;
            $fullName = $student->getFirstName() . " " . $nameMatricule;
            if ($anonymityCode) {
                $fullName = $name = $matricule = $anonymityCode;
            }
            return [
                'evaluationPeriodName' => $classProgram->getEvaluationPeriod()->getName(),
                'name' => $name,
                'fullName' => $fullName,
                'studentName' => $fullName,
                'markEntered' => $schoolMark->getMarkEntered(),
                'matricule' => $matricule,
                'id' => $schoolMark->getId(),
                'anonymity code' => $anonymityCode,
                'anonymityCode' => $anonymityCode,
                'mark' => $schoolMark->getMark(),
                'base' => $schoolMark->getBase(),
                'weighting' => $schoolMark->getWeighting(),
                'assignmentDate' => $assignmentDate,
                'description' => $schoolMark->getDescription(),
                'subjectName' => $classProgram->getNameuvc(),
                'isSimulated' => $schoolMark->getIsSimulated(),
                'isOpen' => $schoolMark->isIsOpen()];
        };
        return $this->json(array_map(${'formatter' . (int)($hideNamesIfAnonymityAvailable)}, $schoolMarks));
    }

    #[Route('/api/get/mark/mark-entry/configurations', name: 'get_mark_mark_entry_configurations')]
    public function getMarkEntryConfigurations(): JsonResponse
    {
       $user = $this->getUser();
       $institution = $user->getInstitution();
       $examInstitutionSettings = $this->examInstitutionSettingsRepository->findOneBy(['institution' => $institution]);

        $weightsWhenEnteringMarks = $examInstitutionSettings->getWeightsWhenEnteringMarks();
        $configurations = [
            'weightsWhenEnteringMarks'=> $weightsWhenEnteringMarks,
        ];
        return $this->json($configurations);
    }
}