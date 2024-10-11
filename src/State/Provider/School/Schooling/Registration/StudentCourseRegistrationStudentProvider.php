<?php

namespace App\State\Provider\School\Schooling\Registration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StudentCourseRegistrationStudentProvider implements ProviderInterface
{
    public function __construct(
        private StudentCourseRegistrationRepository $studentCourseRegistrationRepository,
        private StudentRepository                   $studentRepository,
        private readonly TokenStorageInterface      $tokenStorage
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Retrieve the state from somewhere
        $student = $this->studentRepository->findOneBy(['operator' => $this->getUser()])->getId();

        return $this->studentCourseRegistrationRepository->findByStudentId($this->getUser()->getInstitution(),$student);
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
