<?php

namespace App\Controller\School\Exam\Mark;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\GetConfigurationsUtil;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\NoteType;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Mark;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\ClassWeightingRepository;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\Repository\School\Exam\Configuration\FormulaThRepository;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use App\Repository\School\Exam\Configuration\NoteTypeRepository;
use App\Repository\School\Exam\Configuration\SchoolWeightingRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Configuration\SpecialityWeightingRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GenerateMarkController extends AbstractController
{
    public function __construct(
        private readonly TokenStorageInterface               $tokenStorage,
        private readonly EntityManagerInterface              $entityManager,
        private readonly SchoolClassRepository               $classRepository,
        private readonly SequenceRepository                  $sequenceRepository,
        private readonly NoteTypeRepository                  $noteTypeRepository,
        private readonly EvaluationPeriodRepository          $evaluationPeriodRepository,
        private readonly ClassProgramRepository              $classProgramRepository,
        private readonly StudentCourseRegistrationRepository $studentCourseRegistrationRepository,
        private readonly MarkRepository                      $markRepository,
        private readonly StudentRegistrationRepository       $studentRegistrationRepository,

        private readonly MarkGradeRepository                 $markGradeRepository,

        private readonly SchoolWeightingRepository           $schoolWeightingRepository,
        private readonly ClassWeightingRepository            $classWeightingRepository,
        private readonly SpecialityWeightingRepository       $specialityWeightingRepository,
        private readonly FormulaThRepository                 $formulaThRepository,
        private readonly ExamInstitutionSettingsRepository   $examInstitutionSettingsRepository,
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

    #[Route('/api/mark/generateMarks/{schoolMarkData}', name: 'school_mark_generate_marks')]
    public function generateMark(string $schoolMarkData): JsonResponse
    {
        $schoolMarkData = json_decode($schoolMarkData, true);
        $year = null;
        $school = null;
        $class = null;
        if (isset($schoolMarkData['classId'])) {
            $classId = $schoolMarkData['classId'];
            $class = $this->classRepository->find($classId);
            $year = $class->getYear();
            $school = $class->getSchool();
        } else if (isset($schoolMarkData['classIds'])) {
            $classId = $schoolMarkData['classIds'][0];
            $class = $this->classRepository->find($classId);
            $year = $class->getYear();
            $school = $class->getSchool();
        }
        $sequenceId = $schoolMarkData['sequenceId'];

        $sequence = $this->sequenceRepository->find($sequenceId);
        $institution = $sequence->getInstitution();

        $configurationsUtil = new GetConfigurationsUtil(
            $year,
            $this->formulaThRepository,
            $this->examInstitutionSettingsRepository,
            $this->schoolWeightingRepository,
            $this->classWeightingRepository,
            $this->specialityWeightingRepository,
            $this->markGradeRepository
        );

        $maxWeighting = $configurationsUtil->getMaxWeighting($class);
        $examInstitutionSettings = $configurationsUtil->getExamInstitutionSettings();

        $entryBase = null;
        $markEntryBaseIsCoeff = $examInstitutionSettings->getMarkEntryBaseIsCoeff();
        if ($maxWeighting && $maxWeighting->getEntryBase() !== null){
            $entryBase = $maxWeighting->getEntryBase();
        }
        else if ($examInstitutionSettings->getEntryBase() !== null) {
            $entryBase = $examInstitutionSettings->getEntryBase();
        }

        $noteType = isset($schoolMarkData['noteTypeId']) && $schoolMarkData['noteTypeId'] !== null ? $this->noteTypeRepository->find($schoolMarkData['noteTypeId']) : null;

        $user = $this->getUser();

        $criteria = ['institution' => $institution, 'year' => $year, 'school' => $school];
        $criteria['noteType'] = $noteType;
        $criteria['sequence'] = $sequence;

        $evaluationPeriod = null;
        if (isset($schoolMarkData['evaluationPeriodId'])) {
            $evaluationPeriod = $this->evaluationPeriodRepository->find($schoolMarkData['evaluationPeriodId']);
            $criteria['evaluationPeriod'] = $evaluationPeriod;
        }

        if (isset($schoolMarkData['classProgramIds'])) {
            $isOpenFn = function (StudentCourseRegistration $studentCourseRegistration) use ($criteria) {
                $criteria['studentCourseRegistration'] = $studentCourseRegistration;
                $mark = $this->markRepository->findOneBy($criteria);
                $isOpen = isset($mark);
                return $isOpen;
            };
            $classProgramIds = $schoolMarkData['classProgramIds'];
            foreach ($classProgramIds as $classProgramId) {
                $classProgram = $this->classProgramRepository->find($classProgramId);
                $criteria['classProgram'] = $classProgram;
                $studentCourseRegistrations = $this->studentCourseRegistrationRepository->findBy(['classProgram' => $classProgram]);
                foreach ($studentCourseRegistrations as $studentCourseRegistration) {
                    $student = $studentCourseRegistration->getStudRegistration();
                    $criteria['student'] = $student;
                    if (!$isOpenFn($studentCourseRegistration)) {
                        $evaluationPeriod = $classProgram->getEvaluationPeriod();
                        $criteriaIsOpen = ['evaluationPeriod' => $evaluationPeriod, 'sequence' => $sequence, 'classProgram' => $classProgram];
                        $criteriaEntryBase = $criteriaIsOpen;
                        $criteriaEntryBase['noteType'] = $noteType;
                        $mark = $this->markRepository->findOneBy($criteriaIsOpen);
                        $isOpen = false;
                        $base = ($entryBase) ?? ($markEntryBaseIsCoeff ? $classProgram->getCoeff() : 20);
                        if ($mark){
                            $isOpen = $mark->isIsOpen();
                            $mark = $this->markRepository->findOneBy($criteriaEntryBase);
                            if ($mark) $base = $mark->getBase();
                        }
                        $this->createSchoolMark($institution, $sequence, $class, $studentCourseRegistration, $classProgram, $student, $noteType, $year, $school, $evaluationPeriod, $user,$base,$isOpen);
                    }
                }
            }
        } else if (isset($schoolMarkData['classIds'])) {
            $classIds = $schoolMarkData['classIds'];
            foreach ($classIds as $classId) {
                $class = $this->classRepository->find($classId);
                $entryBase = 20;
                $maxWeighting = $configurationsUtil->getMaxWeighting($class);
                if ($maxWeighting && $maxWeighting->getEntryBase() !== null){
                    $entryBase = $maxWeighting->getEntryBase();
                }
                else if ($examInstitutionSettings->getEntryBase() !== null) {
                    $entryBase = $examInstitutionSettings->getEntryBase();
                }
                $studentIds = array_map(fn(StudentRegistration $studentRegistration) => $studentRegistration->getId(), $this->studentRegistrationRepository->findBy(['currentClass' => $class]));
                $this->createSchoolMarksStudent($studentIds, $criteria, $class, $evaluationPeriod, $institution, $sequence, $noteType, $year, $school, $user,$entryBase,$markEntryBaseIsCoeff);
            }
        } else {
            $studentIds = $schoolMarkData['studentIds']; // ?? array_map(fn(StudentRegistration $studentRegistration) => $studentRegistration->getId(), $this->studentRegistrationRepository->findBy(['currentClass' => $class]));
            $this->createSchoolMarksStudent($studentIds, $criteria, $class, $evaluationPeriod, $institution, $sequence, $noteType, $year, $school, $user,$entryBase,$markEntryBaseIsCoeff);
        }
        $this->entityManager->flush();
        return $this->json([]);
    }

    public function createSchoolMark(?Institution $institution, ?Sequence $sequence, ?SchoolClass $class, StudentCourseRegistration $studentCourseRegistration, ?ClassProgram $classProgram, ?StudentRegistration $student, ?NoteType $noteType, ?Year $year, ?School $school, ?EvaluationPeriod $evaluationPeriod, ?User $user,float $base,bool $isOpen = false): void
    {
        $schoolMark = new Mark();
        $schoolMark->setInstitution($institution);
        $schoolMark->setSequence($sequence);
        $schoolMark->setClass($class);
        $schoolMark->setStudentCourseRegistration($studentCourseRegistration);
        $schoolMark->setClassProgram($classProgram);
        $module = $classProgram->getModule();
        $schoolMark->setModule($module);
        $schoolMark->setStudent($student);
        $schoolMark->setMark(null);
        $schoolMark->setMarkEntered(null);
        $schoolMark->setBase($base);
        $schoolMark->setIsOpen($isOpen);
        $schoolMark->setNoteType($noteType);
        $schoolMark->setYear($year);
        $schoolMark->setSchool($school);
        $schoolMark->setUser($user);
        $schoolMark->setEvaluationPeriod($evaluationPeriod);
        $this->entityManager->persist($schoolMark);
    }

    public function createSchoolMarksStudent(array $studentIds, array $criteria, ?SchoolClass $class, ?EvaluationPeriod $evaluationPeriod, ?Institution $institution, ?Sequence $sequence, ?NoteType $noteType, ?Year $year, ?School $school, ?User $user,?float $base,bool $markEntryBaseIsCoeff): void
    {
        $isOpenFn = function (StudentCourseRegistration $studentCourseRegistration) use ($criteria) {
            $criteria['studentCourseRegistration'] = $studentCourseRegistration;
            $mark = $this->markRepository->findOneBy($criteria);
            $isOpen = isset($mark);
            return $isOpen;
        };

        $criteriaStudentCourseRegistration = [];

        if ($evaluationPeriod) {
            $criteriaStudentCourseRegistration['evaluationPeriod'] = $evaluationPeriod;
        }

        foreach ($studentIds as $studentId) {
            $student = $this->studentRegistrationRepository->find($studentId);
            $criteria['student'] = $student;
            $criteriaStudentCourseRegistration['StudRegistration'] = $student;
            $studentCourseRegistrations = $this->studentCourseRegistrationRepository->findBy($criteriaStudentCourseRegistration);
            foreach ($studentCourseRegistrations as $studentCourseRegistration) {
                if (!$isOpenFn($studentCourseRegistration)) {
                    $classProgram = $studentCourseRegistration->getClassProgram();
                    $evaluationPeriod = $studentCourseRegistration->getEvaluationPeriod();
                    $criteriaIsOpen = ['evaluationPeriod' => $evaluationPeriod, 'sequence' => $sequence, 'classProgram' => $classProgram];
                    $criteriaEntryBase = $criteriaIsOpen;
                    $criteriaEntryBase['noteType'] = $noteType;
                    $mark = $this->markRepository->findOneBy($criteriaIsOpen);
                    $isOpen = false;
                    $entryBase = ($base) ?? ($markEntryBaseIsCoeff ? $classProgram->getCoeff() : 20);
                    if ($mark){
                        $isOpen = $mark->isIsOpen();
                        $mark = $this->markRepository->findOneBy($criteriaEntryBase);
                        if ($mark) $entryBase = $mark->getBase();
                    }
                    $this->createSchoolMark($institution, $sequence, $class, $studentCourseRegistration, $classProgram, $student, $noteType, $year, $school, $evaluationPeriod, $user,$entryBase,$isOpen);
                }
            }
        }
    }
}