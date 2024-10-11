<?php

namespace App\Controller\School\Study\Score;

use App\Repository\School\Study\Teacher\TeacherRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class TeacherCoursePlannedTodayController extends AbstractController
{
    private TeacherRepository $teacherRepo;
    private TimeTableModelDayCellRepository $timeTableModelDayCellRepo;
    private Request $req;

    public function __construct(Request                         $request,
                                TeacherRepository               $teacherRepo,
                                TimeTableModelDayCellRepository $timeTableModelDayCellRepo)
    {
        $this->req = $request;
        $this->teacherRepo = $teacherRepo;
        $this->timeTableModelDayCellRepo = $timeTableModelDayCellRepo;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $currentTeacher = $this->teacherRepo->findOneBy(['operator'=> $this->getUser()]);

        $teacherTimeTableRegistrationCourses = [];

        if ($currentTeacher){
            $today = new \DateTime();
            $teacherTimeTableRegistrations = $this->timeTableModelDayCellRepo->findBy(['teacher'=> $currentTeacher, 'date' => $today ]);

            foreach ($teacherTimeTableRegistrations as $teacherTimeTableRegistrationCourse){
                $teacherTimeTableRegistrationCourses[] = [
                    '@id' => '/api/time_table_model_day_cells/'.$teacherTimeTableRegistrationCourse->getId(),
                    'id' => $teacherTimeTableRegistrationCourse->getId(),
                    'date' => $teacherTimeTableRegistrationCourse->getDate() ? $teacherTimeTableRegistrationCourse->getDate()->format('Y-m-d') : '',
                    'startAt' => $teacherTimeTableRegistrationCourse->getStartAt()->format('H:i'),
                    'endAt' => $teacherTimeTableRegistrationCourse->getEndAt()->format('H:i'),
                    'course' => $teacherTimeTableRegistrationCourse->getCourse(),
                    'room' => $teacherTimeTableRegistrationCourse->getCourse()->getPrincipalRoom() ? $teacherTimeTableRegistrationCourse->getCourse()->getPrincipalRoom()->getName() : '',
                    'isValidated' => $teacherTimeTableRegistrationCourse->isIsValidated(),
                    'isScoreValidated' => $teacherTimeTableRegistrationCourse->isIsScoreValidated(),
                    'isScoreNotValidated' => $teacherTimeTableRegistrationCourse->isIsScoreNotValidated(),
                    'modelDay' => $teacherTimeTableRegistrationCourse->getModelDay(),
                    'courseStartTime' => $teacherTimeTableRegistrationCourse->getCourseStartTime() ? $teacherTimeTableRegistrationCourse->getCourseStartTime()->format('H:i') : '',
                    'courseEndTime' => $teacherTimeTableRegistrationCourse->getCourseEndTime() ? $teacherTimeTableRegistrationCourse->getCourseEndTime()->format('H:i') : '',
                ];
            }
        }

        return $this->json(['hydra:member' => $teacherTimeTableRegistrationCourses]);
    }

}
