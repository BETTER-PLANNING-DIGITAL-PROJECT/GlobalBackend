<?php

namespace App\Controller\School\Study\Teacher\HomeWork;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Study\Teacher\HomeWorkRegistrationRepository;
use App\Repository\School\Study\Teacher\HomeWorkStudentReplyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetStudentReplyPerStudentController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface $tokenStorage, HomeWorkRegistrationRepository $homeWorkRegistrationRepo, HomeWorkStudentReplyRepository $homeWorkStudentReplyRepo,
                                StudentRepository $studentRepo,StudentRegistrationRepository $studentRegistrationRepo )
    {
        $this->homeWorkStudentReplyRepository = $homeWorkStudentReplyRepo;
        $this->studentRepository = $studentRepo;
        $this->studentRegistrationRepository = $studentRegistrationRepo;
        $this->homeWorkRegistrationRepository = $homeWorkRegistrationRepo;

    }

    public function __invoke(Request $request)
    {
        $student = $this->studentRepository->findOneBy(['operator' => $this->getUser()]);
        $studentRegistration = $this->studentRegistrationRepository->findOneBy(['student' => $student]);
        $studentId = $studentRegistration->getId();

        $homeWorkStudentReplies = $this->homeWorkStudentReplyRepository->findByStudent($student);


       return $this->json(['hydra:member' => $homeWorkStudentReplies]);

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



