<?php

namespace App\State\Processor\Budget;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CloseBudgetExerciseProcessor implements ProcessorInterface
{
    private Request $request;
    private EntityManagerInterface $manager;


    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
    EntityManagerInterface $manager) {
        $this->manager = $manager;
    }


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $data->setIsClose(true);
        $this->manager->flush();

        return $data;
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
