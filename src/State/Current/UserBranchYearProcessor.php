<?php

namespace App\State\Current;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Security\Interface\Module;
use App\Entity\Security\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserBranchYearProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage
                                )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        // Get current user

        if ($data instanceof Module){
            $data->setUser($this->getUser());
        }

        // Handle the state
//        if ($data instanceof Campus or $data instanceof Building or $data instanceof Room or $data instanceof ServiceDept or $data instanceof Program or $data instanceof Speciality or $data instanceof ClassCategory  or $data instanceof SchoolClass or $data instanceof Option or $data instanceof Year) {
//            $data->setInstitution($institution);
//        }

        $this->processor->process($data, $operation, $uriVariables, $context);
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
