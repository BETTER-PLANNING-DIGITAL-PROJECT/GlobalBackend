<?php

namespace App\Controller\Dashboard;
;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class StudyDashboardController extends AbstractController
{
    private EntityManagerInterface $manager;
    private StudentRegistrationRepository $studentRegistrationRepository;
    private TimeTableModelDayCellRepository $tableModelDayCellRepository;
    private ClassProgramRepository $classProgramRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                Request                                $req,
                                EntityManagerInterface $manager,
                                StudentCourseRegistrationRepository    $studentCourseRegistrationRepository,
                                StudentRegistrationRepository          $studentRegistrationRepository,
                                ClassProgramRepository                 $classProgramRepository,
                                SchoolClassRepository                  $schoolClassRepository,
                                StudentRepository                      $studentRepository,
                                TimeTableModelDayCellRepository         $tableModelDayCellRepository,
    )
    {
        $this->manager = $manager;
        $this->studentCourseRegistrationRepository = $studentCourseRegistrationRepository;
        $this->studentRegistrationRepository = $studentRegistrationRepository;
        $this->classProgramRepository = $classProgramRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->studentRepository = $studentRepository;
        $this->tableModelDayCellRepository = $tableModelDayCellRepository;
    }


    #[Route('/api/study/dashboard/courses', name: 'app_study_dashboard_courses')]
    public function TotalNumberOfCourses(): JsonResponse
    {
        $numberOfCourses = $this->classProgramRepository->countCourses();
        return new JsonResponse(['hydra:description' => $numberOfCourses]);

    }

    #[Route('/api/study/dashboard/today/courses', name: 'app_study_dashboard_today_courses')]
    public function TotalNumberOfTodayCourses(): JsonResponse
    {
        $todayCourses = $this->tableModelDayCellRepository->countTodayCourses();
        return new JsonResponse(['hydra:description' => $todayCourses]);

    }


    #[Route('/api/study/dashboard/upcoming/courses', name: 'app_study_dashboard_upcoming_courses')]
    public function UpcomingCourses(): JsonResponse
    {
        $upcomingCourses = $this->tableModelDayCellRepository->getUpcomingCourses();

        $courses = [];
        foreach($upcomingCourses as $upcomingCourse){
            $courses[] = [
                'id'=> $upcomingCourse ->getId(),
                'modelDay'=> $upcomingCourse->getModelDay(),
                'startAt'=> $upcomingCourse->getStartAt(),
                'endAt'=> $upcomingCourse->getEndAt(),
                'date'=> $upcomingCourse->getDate(),
                'course' => [
                    '@id' => "/api/class_program/".$upcomingCourse->getCourse()->getId(),
                    '@type' => "ClassProgram",
                    'id' => $upcomingCourse->getCourse()->getId(),
                    'code' => $upcomingCourse->getCourse()->getCodeuvc(),
                    'name' => $upcomingCourse->getCourse()->getNameuvc(),
                ],
                'teacher' => [
                    '@id' => "/api/teachers/".$upcomingCourse->getTeacher()->getId(),
                    '@type' => "Teacher",
                    'id' => $upcomingCourse->getTeacher()->getId(),
                    'name' => $upcomingCourse->getTeacher()->getName(),
                    'firstName' => $upcomingCourse->getTeacher()->getFirstName(),
                ],
                'room'=> $upcomingCourse->getRoom(),
                'model'=> $upcomingCourse->getModel(),
            ];
        }
        return new JsonResponse(['hydra:description' => $courses]);
    }


    #[Route('/api/study/dashboard/class-programs', name: 'app_study_dashboard_class_programs')]
    public function ListOfCourses(): JsonResponse
    {
        $listOfCourses = $this->classProgramRepository->findAll();

        $listCourses = [];
        foreach($listOfCourses as $listOfCourse){
            $listCourses[] = [
                'id'=> $listOfCourse ->getId(),
                'codeuvc'=> $listOfCourse->getCodeuvc(),
                'nameuvc'=> $listOfCourse->getNameuvc(),
                'class' => [
                    '@id' => "/api/classes/".$listOfCourse->getClass()->getId(),
                    '@type' => "Classes",
                    'id' => $listOfCourse->getClass()->getId(),
                    'code' => $listOfCourse->getClass()->getCode(),
                    'name' => $listOfCourse->getClass()->getDescription(),
                ],
                'school' => [
                    '@id' => "/api/schools/".$listOfCourse->getSchool()->getId(),
                    '@type' => "Schools",
                    'id' => $listOfCourse->getSchool()->getId(),
                    'code' => $listOfCourse->getSchool()->getCode(),
                    'name' => $listOfCourse->getSchool()->getName(),
                ],
            ];
        }

        return new JsonResponse(['hydra:description' => $listCourses]);

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
