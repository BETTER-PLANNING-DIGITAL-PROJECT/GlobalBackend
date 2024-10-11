<?php

namespace App\Controller\School\Schooling\Student;


use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\School\Study\Program\TeacherCourseRegistration;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use App\Repository\Security\Session\YearRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class StudentFormBController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager, SchoolRepository $schoolRepository, YearRepository $schoolYearRepository,
                                SchoolClassRepository $schoolClassRepository, StudentRegistrationRepository $studentRegistrationRepository, StudentCourseRegistrationRepository $studentCourseRegistrationRepo,
                                private readonly TokenStorageInterface $tokenStorage, StudentRepository $studentRepository,private readonly TeacherCourseRegistrationRepository $teacherCourseRegistrationRepository)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->schoolRepository = $schoolRepository;
        $this->schoolYearRepository = $schoolYearRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->studentRegistrationRepository = $studentRegistrationRepository;
        $this->studentCourseRegistrationRepo = $studentCourseRegistrationRepo;
        $this->studentRepository = $studentRepository;
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
        foreach($studentClasses as $studentClass){
            $allStudentClasses[] = [
                'id'=> $studentClass ->getId(),
                'student'=> $studentClass ->getStudent()->getName(),
                'year'=> $studentClass->getYear()->getId(),
                'class'=> $studentClass->getCurrentClass()->getId() ? [
                    '@id' => "/api/classes/".$studentClass->getCurrentClass()->getId(),
                    '@type' => "Classe",
                    'id' => $studentClass->getCurrentClass()->getId(),
                    'code' => $studentClass->getCurrentClass()->getCode(),

                ] : '',
            ];
        }
        return new JsonResponse(['hydra:member' => $allStudentClasses]);

    }

    #[Route('/api/get/class/by/{yearId}/{schoolId}', name: 'api_get_school_by_year_school', methods: ['GET'])]
    public function getSchoolByYearSchoolSelected(mixed $yearId, mixed $schoolId): JsonResponse
    {
        $year = $this->schoolYearRepository->find($yearId);
        $school = $this->schoolRepository->find($schoolId);

        return $this->json($this->schoolClassRepository->findBy(['year' => $year, 'school' => $school]));

    }

    #[Route('/api/get/student-registration/by-year-school-class', name: 'app_get_student_registration_get_by_year_school_class')]
    public function getStudentRegistrationByYearSchoolClass(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $yearId = $data['yearId'];
        $schoolId = $data['schoolId'];
        $classId = $data['classId'];

        $year = $this->schoolYearRepository->find($yearId);
        $school = $this->schoolRepository->find($schoolId);
        $class = $this->schoolClassRepository->find($classId);
        $studentRegistrations = $this->studentRegistrationRepository->findBy(['currentYear' => $year, 'school' => $school, 'currentClass' => $class]);

        return $this->json(array_map('self::bindStudentRegistration', $studentRegistrations));
    }

    static function bindStudentRegistration(StudentRegistration $studentRegistration): array
    {
        return ['name' => $studentRegistration->getStudent()->getName(),
            'id' => $studentRegistration->getId(),
            'matricule' => $studentRegistration->getStudent()->getMatricule(),
            'firstName' => $studentRegistration->getStudent()->getFirstName(),
            'sex' => $studentRegistration->getStudent()->getSex()->getName(),
            'dob' => $studentRegistration->getStudent()->getDob(),
            'pob' => $studentRegistration->getStudent()->getPob(),
            'studentphone' => $studentRegistration->getStudent()->getStudentphone(),
            'studentemail' => $studentRegistration->getStudent()->getStudentemail(),
            'speciality' => $studentRegistration->getSpeciality() ?  $studentRegistration->getSpeciality()->getName() : '',
        ];
    }

    #[Route('/api/get/student-course-registration/by-student-registration/{id}', name: 'api_get_student_course_registration_by_student_registration', methods: ['GET'])]
    public function getStudentCourseRegistrationByStudentRegistration(int $id): JsonResponse
    {
        $studentRegistrationId = $this->studentRegistrationRepository->find($id);
        $studentCourseRegistrations = $this->studentCourseRegistrationRepo->findBy(['StudRegistration' => $studentRegistrationId]);
        $studentRegistrations = $this->studentRegistrationRepository->findBy(['id' => $studentRegistrationId]);

        return $this->json([
            'mainInfo' =>  array_map('self::bindStudentRegistrations', $studentRegistrations),
            'studentCourseRegistration' =>  array_map('self::bindStudentCourseRegistration', $studentCourseRegistrations)
        ]);

    }

    public function getTeachers(ClassProgram $classProgram,array $timeSlots)
    {
      $teachers = [];
      foreach ($timeSlots as $timeSlot){
          $teacherType = 'teacher' . $timeSlot;
          $teacherCourseRegistration = $this->teacherCourseRegistrationRepository->findOneBy(['course' => $classProgram, 'type' => $teacherType]);
          $teachers[$teacherType] = $teacherCourseRegistration?->getTeacher()->getName();
      }
      return $teachers;
    }

    public function bindStudentCourseRegistration(StudentCourseRegistration $studentCourseRegistration): array
    {
        $classProgram = $studentCourseRegistration->getClassProgram();

        $binding = [
            'id' => $studentCourseRegistration->getId(),
            'name' => $studentCourseRegistration->getStudRegistration()->getStudent() ? $studentCourseRegistration->getStudRegistration()->getStudent()->getName() : '',
            'matricule' => $studentCourseRegistration->getStudRegistration()->getStudent() ? $studentCourseRegistration->getStudRegistration()->getStudent()->getMatricule() : '',
            'speciality' => $studentCourseRegistration->getStudRegistration()->getSpeciality() ? $studentCourseRegistration->getStudRegistration()->getSpeciality()->getName() : '',
            'department' => $studentCourseRegistration->getClass()->getDepartment() ? $studentCourseRegistration->getClass()->getDepartment()->getName() : '',
            'period' => $classProgram?->getEvaluationPeriod()?->getName(),//$studentCourseRegistration->getClassProgram()->getPeriodType() ? $studentCourseRegistration->getClassProgram()->getPeriodType()->getName() : '',
            'academicYear' => $studentCourseRegistration->getStudRegistration()->getStudent()->getYear() ? $studentCourseRegistration->getStudRegistration()->getStudent()->getYear()->getYear() : '',
            'courseCode' => $classProgram ? $classProgram->getCodeuvc() : '',
            'courseName' => $classProgram ? $classProgram->getNameuvc() : '',
            'coeff' => $classProgram ? $classProgram->getCoeff() : '',
            'status' => $classProgram->getNature() ? $classProgram->getNature()->getName() : '',
        ];
        $timeSlots = ['Cm','Tp','Td'];

        $teachers = $this->getTeachers($classProgram, $timeSlots);
        $binding = array_merge($binding, $teachers);
        return $binding;
    }

    static function bindStudentRegistrations(StudentRegistration $studentRegistration): array
    {
        return [
            'id' => $studentRegistration->getId(),
            'name' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getName() : '',
            'firstName' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getFirstName() : '',
            'matricule' => $studentRegistration->getStudent() ? $studentRegistration->getStudent()->getMatricule() : '',
            'speciality' => $studentRegistration->getSpeciality() ? $studentRegistration->getSpeciality()->getName() : '',
            'department' => $studentRegistration->getCurrentClass()->getDepartment() ? $studentRegistration->getCurrentClass()->getDepartment()->getName() : '',
            'academicYear' => $studentRegistration->getStudent()->getYear() ? $studentRegistration->getStudent()->getYear()->getYear() : '',
        ];
    }


    #[Route('/api/get/student-course-registration/by-class', name: 'api_get_student_course_registration_by_class')]
    public function getStudentCourseRegistrationByClass(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $studentRegistrationId = $data['classId'];


        $student = $this->studentRepository->findOneBy(['operator' => $this->getUser()]);
        $studentRegistration = $this->studentRegistrationRepository->findOneBy(['student' => $student]);

        $year = $this->schoolYearRepository->find($studentRegistration->getCurrentYear()->getId());
        $school = $this->schoolRepository->find($studentRegistration->getSchool()->getId());

        $studentCourseRegistrations = $this->studentCourseRegistrationRepo->findBy(['year' => $year, 'school' => $school, 'StudRegistration' => $studentRegistrationId]);
        $studentRegistrations = $this->studentRegistrationRepository->findBy(['id' => $studentRegistration]);

        return $this->json([
            'mainInfo' =>  array_map('self::bindStudentRegistrations', $studentRegistrations),
            'studentCourseRegistration' =>  array_map('self::bindStudentCourseRegistration', $studentCourseRegistrations)
        ]);

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
}



