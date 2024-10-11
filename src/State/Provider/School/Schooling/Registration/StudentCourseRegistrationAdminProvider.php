<?php

namespace App\State\Provider\School\Schooling\Registration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StudentCourseRegistrationAdminProvider implements ProviderInterface
{
    public function __construct(
        private StudentCourseRegistrationRepository $studentCourseRegistrationRepository,
        private readonly TokenStorageInterface      $tokenStorage
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Retrieve the state from somewhere
        return $this->studentCourseRegistrationRepository->findByStudentMatricule($this->getUser()->getInstitution());
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
