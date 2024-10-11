<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Annual;


use App\Entity\Security\User;
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
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Configuration\ModuleRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Security\Session\YearRepository;
use App\Repository\Setting\School\PeriodTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AnnualMarkCalculationController extends AbstractController
{
    public function __construct(
        private TokenStorageInterface                          $tokenStorage,
        private YearRepository                                 $yearRepository,
        private SchoolRepository                               $schoolRepository,
        private SchoolClassRepository                          $classRepository,
        private StudentRegistrationRepository                  $studentRegistrationRepository,
        private SequenceRepository                             $sequenceRepository,
        private PeriodTypeRepository                           $periodTypeRepository,
        private ClassProgramRepository                         $classProgramRepository,
        private ModuleRepository                               $moduleRepository,
        private EvaluationPeriodRepository                     $evaluationPeriodRepository,
        private StudentCourseRegistrationRepository            $studentCourseRegistrationRepository,
        private MarkSequenceGeneralAverageCalculatedRepository $markSequenceGeneralAverageCalculatedRepository,
        private MarkPeriodCourseCalculatedRepository           $markPeriodCourseCalculatedRepository,
        private MarkGradeRepository                            $markGradeRepository,
        private SchoolWeightingRepository                      $schoolWeightingRepository,
        private ClassWeightingRepository                       $classWeightingRepository,
        private SpecialityWeightingRepository                  $specialityWeightingRepository,
        private FormulaThRepository                            $formulaThRepository,
        private ExamInstitutionSettingsRepository              $examInstitutionSettingsRepository,
        private MarkSequenceCourseCalculatedRepository         $markSequenceCourseCalculatedRepository,
        private MarkPeriodGeneralAverageCalculatedRepository   $markPeriodGeneralAverageCalculatedRepository,
        private MarkAnnualCourseCalculatedRepository           $markAnnualCourseCalculatedRepository,
        private MarkAnnualGeneralAverageCalculatedRepository   $markAnnualGeneralAverageCalculatedRepository,
        private EntityManagerInterface                         $entityManager
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

    // Calculer les notes annuelles
    #[Route('api/school/mark/annual/calculate/{schoolSystem}/{data}', name: 'school_mark_annual_calculate_by_students')]
    public function calculateMarks(string $data,string $schoolSystem): JsonResponse
    {
        $markData = json_decode($data, true);

        $yearId = $markData['yearId'];
        $schoolId = $markData['schoolId'];
        $classId = $markData['classId'];
        $class = $this->classRepository->find($classId);
        $students = null;

        if (isset($markData['studentIds'])){
            $studentIds = $markData['studentIds'];
            $students = array_map(fn(int $studentId) => $this->studentRegistrationRepository->find($studentId), $studentIds);
        }
        else{
            $students = $this->studentRegistrationRepository->findBy(['currentClass' => $class]);
        }

        $year = $this->yearRepository->find($yearId);
        $school = $this->schoolRepository->find($schoolId);

        $annualMarkCalculationControllerUtil = new AnnualMarkCalculationControllerUtil(
            $this->periodTypeRepository,
            $this->classProgramRepository,
            $this->moduleRepository,
            $this->evaluationPeriodRepository,
            $this->sequenceRepository,
            $this->studentCourseRegistrationRepository,
            $this->markGradeRepository,
            $this->schoolWeightingRepository,
            $this->classWeightingRepository,
            $this->specialityWeightingRepository,
            $this->formulaThRepository,
            $this->examInstitutionSettingsRepository,
            $this->markSequenceCourseCalculatedRepository,
            $this->markSequenceGeneralAverageCalculatedRepository,
            $this->markPeriodCourseCalculatedRepository,
            $this->markPeriodGeneralAverageCalculatedRepository,
            $this->markAnnualCourseCalculatedRepository,
            $this->markAnnualGeneralAverageCalculatedRepository,
            $this->entityManager
        );

        $user = $this->getUser();
        $institution = $user->getInstitution();
        $result = $annualMarkCalculationControllerUtil->calculateAllMarks(
            $institution,
            $year,
            $school,
            $class,
            $user,
            'Students',
            $students,
            $schoolSystem
        );

        return $this->json($result);
    }
}