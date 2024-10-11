<?php

namespace App\Controller\School\Schooling\Student;

use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelRepository;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CurrentStudentCalenderController extends AbstractController
{

    private Request $req;
    private EntityManagerInterface $manager;

    private StudentRegistrationRepository $studentRegistrationRepo;
    private StudentCourseRegistrationRepository $studCourseRegRepo;
    private TimeTableModelDayCellRepository $timeTableModelDayCellRepo;
    private StudentRepository $studentRepository;
    private TimeTableModelRepository $tableModelRepository;

    
    public function __construct(Request $req, EntityManagerInterface $manager,StudentRegistrationRepository $studentRegistrationRepo, StudentRepository $studentRepository, TimeTableModelDayCellRepository $timeTableModelDayCellRepo, TimeTableModelRepository $timeTableModelRepo, TimeTableModelRepository $tableModelRepository, private readonly TokenStorageInterface $tokenStorage)
    {
        $this->req = $req;
        $this->manager = $manager;
        $this->timeTableModelDayCellRepo = $timeTableModelDayCellRepo;
        $this->timeTableModelRepo = $timeTableModelRepo;
        $this->studentRepository = $studentRepository;
        $this->tableModelRepository = $tableModelRepository;
        $this->studentRegistrationRepo = $studentRegistrationRepo;
    }

   #[Route('/api/get/calender/current/student', name: 'app_get_calender_current_student')]
    public function getCurrentStudentCalender(Request $request): JsonResponse
    {
        $currentStudent = $this->studentRepository->findOneBy(['operator'=> $this->getUser()]);
        $studentRegistration = $this->studentRegistrationRepo->findOneBy(['student' => $currentStudent]);

        $modelIds = $this->tableModelRepository->findBy(['speciality' => $studentRegistration->getCurrentClass()->getSpeciality(), 'level' => $studentRegistration->getCurrentClass()->getLevel()]);


        $modelCells = [];
        foreach($modelIds as $modelId) {
            if ($modelId->isIsValidated() && $modelId->isIsPublished()) {
                $cellObjects = $this->timeTableModelDayCellRepo->findBy(['model' => $modelId]);
                foreach ($cellObjects as $cellObject) {
                    $modelCells[] = [
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
        }

        return new JsonResponse(['hydra:member' => $modelCells]);
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
