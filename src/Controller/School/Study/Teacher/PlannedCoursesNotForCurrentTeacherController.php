<?php

namespace App\Controller\School\Study\Teacher;

use App\Entity\Security\User;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PlannedCoursesNotForCurrentTeacherController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                Request $request, TimeTableModelDayCellRepository $tableModelDayCellRepo
                               )
    {
        $this->req = $request;
        $this->tableModelDayCellRepo = $tableModelDayCellRepo;
    }

    public function __invoke(TimeTableModelDayCellRepository $tableModelDayCellRepo, Request $request): JsonResponse
    {
        $allCourses = $tableModelDayCellRepo->findTeacherCoursesDiffBySelectedCourseId($request->get('id'));

        $filteredCoursesArray = [];
        foreach ($allCourses as $course) {
                $filteredCoursesArray[] = [
                    '@id' => '/api/get/timetable-model/'.$course->getId(),
                    'id' => $course->getId(),
                    'nameuvc' => $course->getCourse()->getNameuvc() . " - " . $course->getDate()->format('y-m-d') . " - " . $course->getStartAt()->format('H:i') . " - " . $course->getEndAt()->format('H:i') . " - " . $course->getTeacher()->getName(),
                    'date' => $course->getDate(),
                    'startAt' => $course->getStartAt(),
                    'endAt' => $course->getEndAt(),
                    'teacherName' => $course->getTeacher()->getName(),
                ];
        }
        return new JsonResponse(['hydra:member' => $filteredCoursesArray]);
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
