<?php

namespace App\Controller\School\Exam\EndProcessing\PromotionGraduation;


use App\Controller\School\Exam\EndProcessing\MarkCalculation\Sequence\SequenceMarkCalculationControllerUtil;
use App\Entity\School\Exam\Configuration\PromotionCondition;
use App\Entity\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculated;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\ClassWeightingRepository;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\Repository\School\Exam\Configuration\FormulaConditionRepository;
use App\Repository\School\Exam\Configuration\FormulaThRepository;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use App\Repository\School\Exam\Configuration\PromotionConditionRepository;
use App\Repository\School\Exam\Configuration\SchoolWeightingRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Configuration\SpecialityWeightingRepository;
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCalculatedRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculatedRepository;
use App\Repository\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculatedRepository;
use App\Repository\School\Schooling\Configuration\LevelRepository;
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

class PromotionController extends AbstractController
{
    public function __construct(
        private TokenStorageInterface                          $tokenStorage,
        private YearRepository                                 $yearRepository,
        private SchoolRepository                               $schoolRepository,
        private LevelRepository                                $levelRepository,
        private SchoolClassRepository                          $classRepository,
        private StudentRegistrationRepository                  $studentRegistrationRepository,
        private SequenceRepository                             $sequenceRepository,
        private EvaluationPeriodRepository                     $evaluationPeriodRepository,
        private ModuleRepository                               $moduleRepository,
        private ClassProgramRepository                         $classProgramRepository,
        private StudentCourseRegistrationRepository            $studentCourseRegistrationRepository,
        private MarkRepository                                 $markRepository,
        private MarkGradeRepository                            $markGradeRepository,
        private SchoolWeightingRepository                      $schoolWeightingRepository,
        private ClassWeightingRepository                       $classWeightingRepository,
        private SpecialityWeightingRepository                  $specialityWeightingRepository,
        private FormulaThRepository                            $formulaThRepository,
        private ExamInstitutionSettingsRepository              $examInstitutionSettingsRepository,
        private MarkSequenceCourseCalculatedRepository         $markSequenceCourseCalculatedRepository,
        private MarkSequenceGeneralAverageCalculatedRepository $markSequenceGeneralAverageCalculatedRepository,

        private MarkAnnualGeneralAverageCalculatedRepository   $markAnnualGeneralAverageCalculatedRepository,
        private FormulaConditionRepository                     $formulaConditionRepository,
        private PromotionConditionRepository                   $promotionConditionRepository,

        private EntityManagerInterface                         $entityManager,

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


    // Verification des ouvertures de notes sur le promotion
    #[Route('api/school/mark/annual/promotion/check-marks/{data}', name: 'school_mark_annual_promotion_check_marks',)]
    public function checkPromotionMarksRoute(string $data): JsonResponse
    {
        $markData = json_decode($data, true);
        $classes = null;
        if (isset($markData['classId'])) {
            $classId = $markData['classId'];
            $class = $this->classRepository->find($classId);
            $classes = [$class];
        } else {
            $schoolId = $markData['schoolId'];
            $levelId = $markData['levelId'];

            $school = $this->schoolRepository->find($schoolId);
            $level = $this->levelRepository->find($levelId);
            $classes = $this->classRepository->findBy(['school' => $school, 'level' => $level]);
        }

        $isOpen = false;
        $i = 0;
        $class = null;
        while ($i < count($classes) && !$isOpen) {
            $class = $classes[$i];
            $isOpen = $this->markRepository->findOneBy(['class' => $class, 'isOpen' => true]) !== null;
            $i++;
        }
        $result = ['isOpen' => $isOpen, 'class' => $class];
        return $this->json($result);
    }

    // Passage des etudiants dans une classe superieure ou fin de cycle
    #[Route('api/school/mark/promote/{data}', name: 'school_mark_promote')]
    public function promote(string $data): JsonResponse
    {
        $markData = json_decode($data, true);

        $schoolId = $markData['schoolId'];
        $levelId = $markData['levelId'];
//        $classId = $markData['classId'];
        $students = null;

        $school = $this->schoolRepository->find($schoolId);
        $level = $this->levelRepository->find($levelId);

        $formulaCriteria = ['school' => $school, 'level' => $level, 'isMain' => true];
        $formulaCondition = $this->formulaConditionRepository->findOneBy($formulaCriteria);

        if (!isset($formulaCondition)) return $this->json('not formula condition');

        if (isset($markData['studentIds'])) {
            $studentIds = $markData['studentIds'];
            $students = array_map(fn(int $studentId) => $this->studentRegistrationRepository->find($studentId), $studentIds);
        } else if (isset($markData['classId'])) {
            $classId = $markData['classId'];
            $class = $this->classRepository->find($classId);
            $students = $this->studentRegistrationRepository->findBy(['currentClass' => $class]);
        } else {
            $students = $this->studentRegistrationRepository->findBy(['school' => $school]);
        }

        $logFn = $formulaCondition->getLogFn();
        $attributeValues = $formulaCondition->getAttributeValues();
        if ($attributeValues) {
//            $attributeValues = '{"val":1}';
            $attributeValues = json_decode($attributeValues, true);
//            dd($attributeValues);
            extract($attributeValues);
        }

        foreach ($students as $student) {
            $markAnnualGeneralAverageCalculated = $this->markAnnualGeneralAverageCalculatedRepository->findOneBy(['student' => $student]);
            if (isset($markAnnualGeneralAverageCalculated)) {

                $log = eval($logFn);
//                dd($log);
                $isPromoted = !isset($log);
                $markAnnualGeneralAverageCalculated->setIsPromoted($isPromoted);
                $markAnnualGeneralAverageCalculated->setFailureReason($log);
                $markAnnualGeneralAverageCalculated->setPromotionFormulaCondition($formulaCondition);
            }
        }

        $this->entityManager->flush();

        $result = 'success';
        return $this->json($result);
    }
}