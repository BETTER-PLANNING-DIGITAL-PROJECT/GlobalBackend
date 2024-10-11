<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\FeeRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\Security\Session\YearRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetStudentFeeByClassController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly FeeRepository $feeRepository,
                                private readonly YearRepository $yearRepository,
                                private readonly StudentRegistrationRepository $studentRegistrationRepository,
                                private readonly StudentRepository $studentRepository)
    {

    }

    public function __invoke(): JsonResponse
    {
        // Get online student
        $student = $this->studentRepository->findOneBy(['operator' => $this->getUser()]);

        if (!$student)
        {
            return new JsonResponse(['hydra:title' => 'You must be a student to continue'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
        }

        // Get current year
        $currentYear = $this->yearRepository->findOneBy(['isCurrent' => true]);

        // Get registration for the student on current year
        $studentRegistration = $this->studentRegistrationRepository->findOneBy(['student' => $student, 'currentYear' => $currentYear]);

        // Get fee for class of student
        $studentFees = $this->feeRepository->findBy(['class' => $studentRegistration->getCurrentClass()]);

        $myStudentFees = [];
        foreach ($studentFees as $studentFee){
            $myStudentFees [] = [
                'id' => $studentFee->getId(),
                //'@id' => "/api/get/fee/".$studentFee->getId(),
                'code' => $studentFee->getCode(),
                'name' => $studentFee->getName(),
                'class' => $studentFee->getClass(),
                'amount' => $studentFee->getAmount(),
                'isPaymentFee' => $studentFee->isIsPaymentFee()
            ];
        }

        return $this->json(['hydra:member' => $myStudentFees]);

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
