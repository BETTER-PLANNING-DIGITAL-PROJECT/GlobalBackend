<?php

namespace App\Controller;

use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CalenderCurrentUserController extends AbstractController
{
    public function jsondecode()
    {
        try {
            return file_get_contents('php://input') ?
                json_decode(file_get_contents('php://input'), false) :
                [];
        }catch (\Exception $ex)
        {
            return [];
        }

    }

    private Request $req;
    private EntityManagerInterface $manager;

    private StudentRegistrationRepository $studRegistrationRepo;
    private StudentCourseRegistrationRepository $studCourseRegRepo;
    private TimeTableModelDayCellRepository $timeTableModelDayCellRepo;
    private TimeTableModelRepository $timeTableModelRepo;

    /**
     * @param Request $req
     * @param EntityManagerInterface $manager
     * @param ClassProgramRepository $classProgramRepo
     * @param TimeTableModelDayCellRepository $timeTableModelDayCellRepo
     * @param TimeTableModelRepository $timeTableModelRepo
     * @param StudentRegistrationRepository $studRegistrationRepo
     * @param StudentCourseRegistrationRepository $studCourseRegRepo
     */
    
    public function __construct(Request $req, EntityManagerInterface $manager,TeacherRepository $teacherRepo, TimeTableModelDayCellRepository $timeTableModelDayCellRepo, TimeTableModelRepository $timeTableModelRepo, private readonly TokenStorageInterface $tokenStorage)
    {
        $this->req = $req;
        $this->manager = $manager;
        $this->timeTableModelDayCellRepo = $timeTableModelDayCellRepo;
        $this->timeTableModelRepo = $timeTableModelRepo;
        $this->teacherRepo = $teacherRepo;
    }

   #[Route('/api/calender-current-user/get', name: 'app_calender_current_user_get')]
    public function getCurrentUserCalender(Request $request): JsonResponse
    {
        $currentteacher = $this->teacherRepo->findOneBy(['operator'=> $this->getUser()]);


            $cellObjects = $this->timeTableModelDayCellRepo->findBy(['teacher' => $currentteacher]);

            $modelCell = [];
            foreach ($cellObjects as $cellObject) {
                if ($cellObject->getModel()->isIsValidated() && $cellObject->getModel()->isIsPublished()) {
                $modelCell[] = [
                    'id' => $cellObject->getId(),
                    'startAt' => $cellObject->getStartAt(),
                    'endAt' => $cellObject->getEndAt(),
                    'date' => $cellObject->getDate(),
                    'modelId' => $cellObject->getModel()->getId(),
                    'modelDay' => $cellObject->getmodelDay()->getDay(),
                    'course' => $cellObject->getCourse() ? $cellObject->getCourse()->getNameuvc() : '',
                    'room' => $cellObject->getRoom() ? $cellObject->getRoom()->getName() : '',
                    'teacher' => $cellObject->getTeacher() ? $cellObject->getTeacher()->getName() : ''
                ];
             }

            }
            return new JsonResponse(['hydra:member' => $modelCell]);

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
