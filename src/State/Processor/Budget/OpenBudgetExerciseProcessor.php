<?php

namespace App\State\Processor\Budget;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Security\User;
use App\Repository\Budget\BudgetExerciseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OpenBudgetExerciseProcessor implements ProcessorInterface
{
    private Request $request;
    private EntityManagerInterface $manager;
    private  BudgetExerciseRepository $budgetExerciseRepo;


    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
    EntityManagerInterface $manager, BudgetExerciseRepository $budgetExerciseRepo) {
        $this->manager = $manager;
        $this->budgetExerciseRepo = $budgetExerciseRepo;
    }


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // Set the current exercise as open
        $data->setIsClose(false);

        // Close other exercises
        $existingBudgetExercises = $this->budgetExerciseRepo->findAll();
        foreach ($existingBudgetExercises as $existingBudgetExercise) {
            if ($existingBudgetExercise !== $data) {
                $existingBudgetExercise->setIsClose(true);
            }
        }

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
