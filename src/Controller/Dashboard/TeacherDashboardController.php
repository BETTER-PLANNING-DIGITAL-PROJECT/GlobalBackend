<?php

namespace App\Controller\Dashboard;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Study\Teacher\CoursePermutationRepository;
use App\Repository\School\Study\Teacher\CoursePostponementRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use App\Repository\Security\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class TeacherDashboardController extends AbstractController
{
    private EntityManagerInterface $manager;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                Request                                $req, EntityManagerInterface $manager,
                                TeacherRepository    $teacherRepository, CoursePostponementRepository $coursePostponementRepository,
    CoursePermutationRepository $coursePermutationRepository, TimeTableModelDayCellRepository $timeTableModelDayCellRepository)
    {
        $this->manager = $manager;
        $this->teacherRepository = $teacherRepository;
        $this->coursePostponementRepository = $coursePostponementRepository;
        $this->coursePermutationRepository = $coursePermutationRepository;
        $this->timeTableModelDayCellRepository = $timeTableModelDayCellRepository;
    }


    #[Route('/api/get/teacher/dashboard/number-of-teachers', name: 'app_get_teacher_dashboard_number_of_teachers')]
    public function NumberOfTeachers(): JsonResponse
    {

        $teacherCount= $this->teacherRepository->countTeachers();
        return new JsonResponse(['hydra:description' => $teacherCount]);

    }

    #[Route('/api/get/teacher/dashboard/course-postponement-request', name: 'app_get_teacher_dashboard_course_postponement_request')]
    public function CoursePostponementRequest(): JsonResponse
    {

        $postponementCount= $this->coursePostponementRepository->countCoursePostponement();
        return new JsonResponse(['hydra:description' => $postponementCount]);

    }

    #[Route('/api/get/teacher/dashboard/course-swap-request', name: 'app_get_teacher_dashboard_course_swap_request')]
    public function CourseSwapRequest(): JsonResponse
    {

        $SwapCount= $this->coursePermutationRepository->countCourseSwap();
        return new JsonResponse(['hydra:description' => $SwapCount]);

    }

    #[Route('/api/get/teacher/dashboard/today-courses', name: 'app_get_teacher_dashboard_today_courses')]
    public function TodayCourses(): JsonResponse
    {
        $today = new \DateTime();
        $teacherTimeTableRegistrations = $this->timeTableModelDayCellRepository->findBy(['date' => $today ]);

        $teacherTimeTableRegistrationCourses = [];
        $count = 0;

        foreach ($teacherTimeTableRegistrations as $teacherTimeTableRegistrationCourse){
            $teacherTimeTableRegistrationCourses[] = [
                '@id' => '/api/time_table_model_day_cells/'.$teacherTimeTableRegistrationCourse->getId(),
                'id' => $teacherTimeTableRegistrationCourse->getId(),
                'date' => $teacherTimeTableRegistrationCourse->getDate() ? $teacherTimeTableRegistrationCourse->getDate()->format('Y-m-d') : '',
                'startAt' => $teacherTimeTableRegistrationCourse->getStartAt()->format('H:i'),
                'endAt' => $teacherTimeTableRegistrationCourse->getEndAt()->format('H:i'),
                'course' => $teacherTimeTableRegistrationCourse->getCourse()->getNameuvc(),
                'room' => $teacherTimeTableRegistrationCourse->getCourse()->getPrincipalRoom() ? $teacherTimeTableRegistrationCourse->getCourse()->getPrincipalRoom()->getName() : '',
                'isValidated' => $teacherTimeTableRegistrationCourse->isIsValidated(),
                'isScoreValidated' => $teacherTimeTableRegistrationCourse->isIsScoreValidated(),
                'isScoreNotValidated' => $teacherTimeTableRegistrationCourse->isIsScoreNotValidated(),
                'modelDay' => $teacherTimeTableRegistrationCourse->getModelDay(),
                'teacher' => $teacherTimeTableRegistrationCourse->getTeacher()->getName(),
                'courseStartTime' => $teacherTimeTableRegistrationCourse->getCourseStartTime() ? $teacherTimeTableRegistrationCourse->getCourseStartTime()->format('H:i') : '',
                'courseEndTime' => $teacherTimeTableRegistrationCourse->getCourseEndTime() ? $teacherTimeTableRegistrationCourse->getCourseEndTime()->format('H:i') : '',
            ];
            $count++;

            // Stop after adding 5 cash desks
            if ($count >= 5) {
                break;
            }
        }
//        dd($teacherTimeTableRegistrationCourses);

        return $this->json(['hydra:member' => $teacherTimeTableRegistrationCourses]);

    }

    #[Route('/api/get/teacher/dashboard/recent-teacher', name: 'app_get_teacher_dashboard_recent_teacher')]
    public function RecentTeacher(): JsonResponse
    {
        $teachers = $this->teacherRepository
            ->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC') // Order by creation date descending
            ->setMaxResults(5) // Limit to 5 results
            ->getQuery()
            ->getResult();

        $recentTeachers = [];
        foreach ($teachers as $teacher) {
            $recentTeachers[] = [
                'id' => $teacher->getId(),
                'name' => $teacher->getName(),
                'registrationNumber' => $teacher->getRegistrationNumber(),
                'phone' => $teacher->getPhone(),
                'email' => $teacher->getEmail(),
                'address' => $teacher->getAddress(),
            ];
        }

        return new JsonResponse(['hydra:description' => $recentTeachers]);
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
