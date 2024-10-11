<?php

namespace App\State\Provider\School\Schooling\Registration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StudentCurrentClassProvider implements ProviderInterface
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                StudentRepository $studentRepository, StudentRegistrationRepository $studentRegistrationRepository)
    {
        $this->studentRepository = $studentRepository;
        $this->studentRegistrationRepository = $studentRegistrationRepository;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $currentStudent = $this->studentRepository->findOneBy(['operator'=> $this->getUser()]);
        $studentRegistration = $this->studentRegistrationRepository->findOneBy(['student' => $currentStudent, 'currentYear' => $this->getUser()->getCurrentYear()]);

        return [$studentRegistration->getCurrentClass()];
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
