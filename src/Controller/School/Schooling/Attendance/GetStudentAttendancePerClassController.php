<?php

namespace App\Controller\School\Schooling\Attendance;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Attendance\StudentAttendanceDetailRepository;
use App\Repository\School\Study\Program\StudentAttendanceRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetStudentAttendancePerClassController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request,
                             TeacherRepository $teacherRepository,
                             StudentAttendanceRepository $studentAttendanceRepo,
                             StudentAttendanceDetailRepository $studentAttendanceDetailRepo,
                             StudentAttendanceRepository $studentAttendanceRepository): JsonResponse
    {
        $attendances = $studentAttendanceRepository->findWhereProgramIsNull();

        $myAttendances = [];
        foreach ($attendances as $attendance){
            $myAttendances [] = [
                '@id' => "/api/get/attendance/".$attendance->getId(),
                'id' => $attendance->getId(),
                'class' => $attendance->getClass() ? $attendance->getClass()->getCode() : '',
                'course' => $attendance->getCourse(),
                'callerName' => $attendance->getCallerName(),
                'attendanceDate' => $attendance->getAttendanceDate(),
                'attendanceTime' => $attendance->getAttendanceTime(),
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



