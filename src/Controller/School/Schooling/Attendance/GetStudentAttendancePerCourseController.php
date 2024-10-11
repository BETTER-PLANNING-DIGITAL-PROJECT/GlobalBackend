<?php

namespace App\Controller\School\Schooling\Attendance;

use App\Entity\Security\User;
use App\Repository\School\Study\Program\StudentAttendanceRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetStudentAttendancePerCourseController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request,
                             TeacherRepository $teacherRepository,
                             StudentAttendanceRepository $studentAttendanceRepo,
                             StudentAttendanceRepository $studentAttendanceRepository): JsonResponse
    {
        $currentTeacher = $teacherRepository->findOneBy(['operator'=> $this->getUser()]);
        $attendances = $studentAttendanceRepository->findBy(['teacher' => $currentTeacher]);

        $myAttendances = [];
        foreach ($attendances as $attendance){
            $myAttendances [] = [
                '@id' => "/api/get/attendance/".$attendance->getId(),
                'id' => $attendance->getId(),
                'school' => $attendance->getClassProgram() ? $attendance->getClassProgram()->getSchool()->getName() : '',
                'class' => $attendance->getClass() ? $attendance->getClass()->getCode() : '',
                'course' => $attendance->getClassProgram() ? $attendance->getClassProgram()->getSubject()->getName() : '',
                'present' => $studentAttendanceRepo->countPresentStudent(true, $attendance)[1],
                'absent' => $studentAttendanceRepo->countAbsentStudent(false, $attendance)[1]
            ];
        }

        return $this->json(['hydra:member' => $myAttendances]);

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



