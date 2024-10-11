<?php

namespace App\Controller\School\Schooling\Attendance;

use App\Entity\Security\User;
use App\Repository\School\Study\Program\StudentAttendanceRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class StudentAttendanceCountPresenceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                Request $request,
                                TeacherRepository $teacherRepo,
                                StudentAttendanceRepository $studentAttendanceRepo)
    {

        $this->req = $request;
        $this->teacherRepo = $teacherRepo;
        $this->studentAttendanceRepo = $studentAttendanceRepo;
    }

    public function __invoke(Request $request, InstitutionRepository $institutionRepository)
    {

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
