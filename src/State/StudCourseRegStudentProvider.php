<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StudCourseRegStudentProvider implements ProviderInterface
{
    public function __construct(
        private StudentCourseRegistrationRepository $studCourseRegRepository,
        private InstitutionRepository               $institutionRepository,
        private StudentRepository                   $studentRepo,
        private readonly TokenStorageInterface      $tokenStorage
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {

        // Retrieve the state from somewhere
        $student = $this->studentRepo->findOneBy(['operator' => $this->getUser()])->getId();
//        dd($this->studCourseRegRepository->findByStudentId($this->getUser()->getInstitution(),$student));

        return $this->studCourseRegRepository->findByStudentId($this->getUser()->getInstitution(),$student);


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
