<?php

namespace App\Controller\School\Schooling\Attendance;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Attendance\StudentAttendanceDetailRepository;
use App\Repository\School\Study\Program\StudentAttendanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetStudentAttendanceDetailController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request,
                             StudentAttendanceDetailRepository $studentAttendanceDetailsRepo,
                             StudentAttendanceRepository $studentAttendanceRepo): JsonResponse
    {
        $id = $request->get('id');
        $attendance = $studentAttendanceRepo->find($id);
        $myAttendances = [];
        if ($attendance)
        {
            $studentAttendanceDetails = $studentAttendanceDetailsRepo->findBy(['studentAttendance'=> $attendance]);

            foreach ($studentAttendanceDetails as $studentAttendanceDetail){
                $myAttendances [] = [
                    'id' => $studentAttendanceDetail->getId(),
                    'student' => $studentAttendanceDetail->getStudent() ? $studentAttendanceDetail->getStudent()->getStudent()->getName() : '',
                    'matricule' => $studentAttendanceDetail->getStudent() ? $studentAttendanceDetail->getStudent()->getStudent()->getMatricule() : '',
                    'studentImg' => $studentAttendanceDetail->getStudent() ? $studentAttendanceDetail->getStudent()->getStudent()->getPicture() : '',
                    'isPresent' => $studentAttendanceDetail->isIsPresent(),
                ];
            }
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



