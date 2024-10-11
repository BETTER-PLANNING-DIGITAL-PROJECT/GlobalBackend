<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Sequence;


use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\ClassWeightingRepository;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\Repository\School\Exam\Configuration\FormulaThRepository;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use App\Repository\School\Exam\Configuration\SchoolWeightingRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Configuration\SpecialityWeightingRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Configuration\ModuleRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Security\Session\YearRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SequenceMarkCalculationController extends AbstractController
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private YearRepository $yearRepository,
        private SchoolRepository $schoolRepository,
        private SchoolClassRepository $classRepository,
        private StudentRegistrationRepository $studentRegistrationRepository,
        private SequenceRepository $sequenceRepository,
        private EvaluationPeriodRepository $evaluationPeriodRepository,
        private ModuleRepository $moduleRepository,
        private ClassProgramRepository $classProgramRepository,
        private StudentCourseRegistrationRepository $studentCourseRegistrationRepository,
        private MarkRepository $markRepository,
        private MarkGradeRepository $markGradeRepository,
        private SchoolWeightingRepository $schoolWeightingRepository,
        private ClassWeightingRepository $classWeightingRepository,
        private SpecialityWeightingRepository $specialityWeightingRepository,
        private FormulaThRepository $formulaThRepository,
        private ExamInstitutionSettingsRepository $examInstitutionSettingsRepository,
        private MarkSequenceCourseCalculatedRepository $markSequenceCourseCalculatedRepository,
        private MarkSequenceGeneralAverageCalculatedRepository $markSequenceGeneralAverageCalculatedRepository,
        private EntityManagerInterface $entityManager
    )
    {

    }

    public function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }

    // Calculer les notes sequentielles
    #[Route('api/school/mark/sequence/calculate/{data}', name: 'school_mark_sequence_calculate_by_students')]
    public function calculateMarks(string $data): JsonResponse
    {
        $markData = json_decode($data, true);

        $yearId = $markData['yearId'];
        $schoolId = $markData['schoolId'];
        $classId = $markData['classId'];
        $students = null;

        if (isset($markData['studentIds'])){
            $studentIds = $markData['studentIds'];
            $students = array_map(fn(int $studentId) => $this->studentRegistrationRepository->find($studentId), $studentIds);
        }
        else{
            $classId = $markData['classId'];
            $class = $this->classRepository->find($classId);
            $students = $this->studentRegistrationRepository->findBy(['currentClass' => $class]);
        }

        $evaluationPeriodId = $markData['evaluationPeriodId'];
        $sequenceId = $markData['sequenceId'];

        $year = $this->yearRepository->find($yearId);
        $school = $this->schoolRepository->find($schoolId);
        $class = $this->classRepository->find($classId);
        $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
        $sequence = $this->sequenceRepository->find($sequenceId);

        $sequenceMarkCalculationControllerUtil = new SequenceMarkCalculationControllerUtil(
            $this->moduleRepository,
            $this->classProgramRepository,
            $this->studentCourseRegistrationRepository,
            $this->markRepository,
            $this->markGradeRepository,
            $this->schoolWeightingRepository,
            $this->classWeightingRepository,
            $this->specialityWeightingRepository,
            $this->formulaThRepository,
            $this->examInstitutionSettingsRepository,
            $this->markSequenceCourseCalculatedRepository,
            $this->markSequenceGeneralAverageCalculatedRepository,
            $this->entityManager
        );

        $user = $this->getUser();
        $institution = $user->getInstitution();
        $result = $sequenceMarkCalculationControllerUtil->calculateAllMarks(
            $institution,
            $year,
            $school,
            $class,
            $evaluationPeriod,
            $sequence,
            $user,
            'Students',
            $students
        );

        return $this->json($result);
    }
}