<?php

namespace App\Controller\School\Study\Score;

use App\Entity\Security\User;
use App\Repository\School\Study\Teacher\TeacherRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class TeacherCoursePlannedController extends AbstractController
{
    private EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager, private readonly TokenStorageInterface $tokenStorage,
                                Request $request,
                                TeacherRepository $teacherRepo,
                                TimeTableModelDayCellRepository $timeTableModelDayCellRepo)
    {
        $this->manager = $manager;
        $this->req = $request;
        $this->teacherRepo = $teacherRepo;
        $this->timeTableModelDayCellRepo = $timeTableModelDayCellRepo;

    }

    public function __invoke(Request $request)
    {
        $currentTeacher = $this->teacherRepo->findOneBy(['operator'=> $this->getUser()]);

        $teacherCoursesPlaned = [];

        if ($currentTeacher){
            $teacherCoursesPlaned = $this->timeTableModelDayCellRepo->findBy(['teacher'=> $currentTeacher]);
        }

        return $teacherCoursesPlaned;
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
