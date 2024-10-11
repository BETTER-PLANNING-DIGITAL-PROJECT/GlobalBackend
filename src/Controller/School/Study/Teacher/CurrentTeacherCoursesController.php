<?php

namespace App\Controller\School\Study\Teacher;

use App\Entity\Security\User;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class CurrentTeacherCoursesController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                Request $request,
                                TeacherRepository $teacherRepo,
                                TeacherCourseRegistrationRepository $teacherCourseRegistrationRepo)
    {

        $this->req = $request;
        $this->teacherRepo = $teacherRepo;
        $this->teacherCourseRegistrationRepo = $teacherCourseRegistrationRepo;
    }

    public function __invoke(Request $request, InstitutionRepository $institutionRepository)
    {
        $currentTeacher = $this->teacherRepo->findOneBy(['operator' => $this->getUser()]);

        return $this->teacherCourseRegistrationRepo->findByCurrentTeacher($currentTeacher);

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
