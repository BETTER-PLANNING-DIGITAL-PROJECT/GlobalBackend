<?php

namespace App\Controller\GetServices;

use App\Controller\GlobalController;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\NoteTypeRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Schooling\Configuration\LevelRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use App\Repository\Security\Session\YearRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GetClassController extends AbstractController
{
    private Request $req;
    private EntityManagerInterface $entityManager;
    private ClassProgramRepository $classProgramRepository;
    private StudentRegistrationRepository $studentRegistrationRepository;
    private StudentCourseRegistrationRepository $studCourseRegRepository;
    private SchoolClassRepository $schoolClassRepository;
    private SchoolRepository $schoolRepository;
    private SequenceRepository $sequenceRepository;
    private MarkRepository $markRepository;
    private YearRepository $yearRepository;
    private StudentRepository $studentRepository;
    private TeacherRepository $teacherRepository;
    private TeacherCourseRegistrationRepository $teacherCourseRegistrationRepository;
    private InstitutionRepository $institutionRepository;

    /**
     * @param Request $req
     * @param EntityManagerInterface $entityManager
     * @param ClassProgramRepository $classProgramRepository
     * @param StudentRegistrationRepository $studentRegistrationRepository
     * @param StudentCourseRegistrationRepository $studCourseRegRepository
     * @param MarkRepository $markRepository
     * @param SchoolClassRepository $schoolClassRepository
     * @param SchoolRepository $schoolRepository
     * @param YearRepository $yearRepository
     * @param StudentRepository $studentRepository
     * @param TeacherRepository $teacherRepository
     * @param TeacherCourseRegistrationRepository $teacherCourseRegistrationRepository
     * @param SequenceRepository $sequenceRepository
     * @param InstitutionRepository $institutionRepository
     */
    public function __construct(Request $req, private readonly TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager,private GlobalController $globalController,private LevelRepository $levelRepository,MarkRepository $markRepository, ClassProgramRepository $classProgramRepository, SequenceRepository $sequenceRepository, StudentRegistrationRepository $studentRegistrationRepository, StudentCourseRegistrationRepository $studCourseRegRepository, SchoolClassRepository $schoolClassRepository, SchoolRepository $schoolRepository, YearRepository $yearRepository, StudentRepository $studentRepository, InstitutionRepository $institutionRepository, TeacherRepository $teacherRepository, TeacherCourseRegistrationRepository $teacherCourseRegistrationRepository, private readonly NoteTypeRepository $noteTypeRepository, private readonly EvaluationPeriodRepository $evaluationPeriodRepository)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->classProgramRepository = $classProgramRepository;
        $this->studentRegistrationRepository = $studentRegistrationRepository;
        $this->studCourseRegRepository = $studCourseRegRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->schoolRepository = $schoolRepository;
        $this->markRepository = $markRepository;
        $this->yearRepository = $yearRepository;
        $this->studentRepository = $studentRepository;
        $this->sequenceRepository = $sequenceRepository;
        $this->teacherRepository = $teacherRepository;
        $this->teacherCourseRegistrationRepository = $teacherCourseRegistrationRepository;
        $this->institutionRepository = $institutionRepository;
    }

    #[Route('/api/get/class/by/school/{schoolId}/level/{levelId}', name: 'api_get_class_by_school_level', methods: ['GET'])]
    public function getClassBySchoolLevelSelected(mixed $schoolId, mixed $levelId): JsonResponse
    {
        $school = $this->schoolRepository->find($schoolId);
        $level = $this->levelRepository->find($levelId);
        return $this->json($this->schoolClassRepository->findBy(['level' => $level, 'school' => $school]));
    }

    #[Route('/api/get/classes/by-year-school', name: 'api_get_classes_by_year_school', methods: ['GET'])]
    public function getClassByYearSelected(Request $request): JsonResponse
    {
        $student = $this->studentRepository->findOneBy(['operator' => $this->getUser()]);
        $studentRegistration = $this->studentRegistrationRepository->findOneBy(['student' => $student]);
        $year = $studentRegistration->getCurrentYear()->getId();
        $school = $studentRegistration->getSchool()->getId();
        $studentClasses = $this->studentRegistrationRepository->findBy(['student' => $student, 'year' => $year, 'school' => $school]);
        $allStudentClasses = [];
        foreach ($studentClasses as $studentClass) {
            $allStudentClasses[] = ['id' => $studentClass->getId(), 'student' => $studentClass->getStudent()->getName(), 'year' => $studentClass->getYear()->getId(), 'class' => $studentClass->getCurrentClass()->getId() ? ['@id' => "/api/classes/" . $studentClass->getCurrentClass()->getId(), '@type' => "Classe", 'id' => $studentClass->getCurrentClass()->getId(), 'code' => $studentClass->getCurrentClass()->getCode(),] : '',];
        }
        return new JsonResponse(['hydra:member' => $allStudentClasses]);

    }


    #[Route('/api/get/class/by/{schoolId}/{yearId}/{sequenceId}/{evaluationPeriodId}/{noteTypeId}', name: 'get_class_by_school_year')]
    public function getClassBySchoolYear(mixed $schoolId, mixed $yearId = null, mixed $sequenceId = null, mixed $noteTypeId = null, mixed $evaluationPeriodId = null): JsonResponse
    {
        $school = $this->schoolRepository->find($schoolId);
        if (!($school instanceof School)) {
            //
        }

        $year = null;
        if (!isset($yearId)) {
            $year = $this->getUser()->getCurrentYear();
            //return new JsonResponse(['hydra:description' => 'The Year not Found.'], 400);
        } else $year = $this->yearRepository->find($yearId);
        if (!($year instanceof Year)) {
            return new JsonResponse(['hydra:description' => 'This Year is not configured in the System.'], 400);
        }

        $currentTeacher = $this->teacherRepository->findOneBy(['operator' => $this->getUser()]);
        if (!isset($currentTeacher)) {
            $classes = $this->schoolClassRepository->findBy(['school' => $school, 'year' => $year]);
            $institution = $year->getInstitution();
            if (isset($sequenceId)) {
                $classesResult = [];
                $noteType = $noteTypeId != 'null' ? $this->noteTypeRepository->find($noteTypeId) : null;
                $sequence = $this->sequenceRepository->find($sequenceId);
                $criteria = ['institution' => $institution, 'year' => $year, 'school' => $school];
                $criteria['noteType'] = $noteType;
                $criteria['sequence'] = $sequence;

                $criteriaStudentCourseRegistration = [];
                $criteriaClassProgram = [];
                $criteriaStudent = [];

                if ($evaluationPeriodId != 'null'){
                    $evaluationPeriod = $this->evaluationPeriodRepository->find($evaluationPeriodId);
                    $criteria['evaluationPeriod'] = $evaluationPeriod;
                    $criteriaStudentCourseRegistration['evaluationPeriod'] = $evaluationPeriod;
                    $criteriaStudent['evaluationPeriod'] = $evaluationPeriod;
                }

                foreach ($classes as $class) {
                    $criteria['class'] = $class;
                    $criteriaStudentCourseRegistration['class'] = $class;
                    $criteriaClassProgram['class'] = $class;
//                    return $this->json($criteriaStudentCourseRegistration);
                    $notStudentCourseRegistrations = $this->studCourseRegRepository->findOneBy($criteriaStudentCourseRegistration) === null;
                    $isOpen = true;
                    $students = $this->studentRegistrationRepository->findBy(['currentClass' => $class]);
                    $notClassPrograms = $this->classProgramRepository->findOneBy($criteriaClassProgram) === null;

                    if (!$notStudentCourseRegistrations) {
                        $i = 0;
                        while ($i < count($students) && $isOpen) {
                            $studentRegistration = $students[$i];
                            $criteria['student'] = $studentRegistration;
                            $criteriaStudent['StudRegistration'] = $studentRegistration;
                            $studentCourseRegistrations = $this->studCourseRegRepository->findBy($criteriaStudent);
                            $j = 0;
                            while ($j < count($studentCourseRegistrations) && $isOpen) {
                                $studentCourseRegistration = $studentCourseRegistrations[$j];
                                $criteria['studentCourseRegistration'] = $studentCourseRegistration;
                                $mark = $this->markRepository->findOneBy($criteria);
                                $isOpen = isset($mark);
                                $j++;
                            }
                            $i++;
                        }
                    }
                    $classesResult[] = ['code' => $class->getCode(), 'id' => $class->getId(), 'isOpen' => $isOpen, 'notStudents' => empty($students), 'notStudentCourseRegistrations' => $notStudentCourseRegistrations, 'notClassPrograms' => $notClassPrograms];
                }
                return $this->json($classesResult);
            }
            return $this->json($classes);
        }
        $teacherCoursesRegistration = $this->teacherCourseRegistrationRepository->findBy(['teacher' => $currentTeacher,'type'=>'teacherMark']);
        $classesId = [];
        $classes = [];
        foreach ($teacherCoursesRegistration as $teacherCourseRegistration) {
            $classProgram = $teacherCourseRegistration->getCourse();
            $class = $classProgram->getClass();
            $classId = $class->getId();
            $schoolCourse = $class->getSchool();
            if ($classProgram->getYear() === $year && $schoolCourse === $school && !in_array($classId, $classesId, true)) {
                $classes[] = $class;
                $classesId[] = $classId;
            }
        }
        return $this->json($classes);
    }

    #[Route('/api/get/class/by/{yearId}/{schoolId}', name: 'api_get_class_by_year_school', methods: ['GET'])]
    public function getClassByYearSchoolSelected(mixed $yearId, mixed $schoolId): JsonResponse
    {
        $year = $this->yearRepository->find($yearId);
        $school = $this->schoolRepository->find($schoolId);
        return $this->json($this->schoolClassRepository->findBy(['year' => $year, 'school' => $school]));
    }


    // SECTION : BY STUDENT

    // Get Classes : when simple user
    // Get Classes : when user is teacher : from teacher course registration
    #[Route('/api/get/class/{schoolId}/{yearId}', name: 'get_classes_by_school_year')]
    public function getClassesBySchoolYear(mixed $schoolId, mixed $yearId = null): JsonResponse
    {
        $school = $this->schoolRepository->find($schoolId);
        if(!($school instanceof School))
        {
            //
        }

        if(isset($yearId))
        {
            $year = $this->yearRepository->find($yearId);
        }
        else{
            $year = $this->getUser()->getCurrentYear();
        }

        if(!($year instanceof Year))
        {
            //
        }

        $currentTeacher = $this->teacherRepository->findOneBy(['operator' => $this->getUser()]);
        if (!isset($currentTeacher)) return $this->json($this->schoolClassRepository->findBy(['school' => $school, 'year' => $year]));

        $teacherCoursesRegistration = $this->teacherCourseRegistrationRepository->findBy(['teacher' => $currentTeacher]);
        $classesId = [];
        foreach ($teacherCoursesRegistration as $teacherCourseRegistration) {
            $classProgram = $teacherCourseRegistration->getCourse();
            $classId = $classProgram->getClass()->getId();
            if ($classProgram->getYear()->getId() === $yearId && !in_array($classId, $classesId, true)) {
                $classes[] = $classProgram->getClass();
                $classesId[] = $classId;
            }
        }
        return $this->json($classes);
    }


    public function getUser(): ?User
    {
        return $this->globalController->getUser();
    }


}
