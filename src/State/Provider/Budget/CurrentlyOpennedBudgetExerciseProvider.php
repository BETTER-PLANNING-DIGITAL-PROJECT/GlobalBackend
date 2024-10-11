<?php

namespace App\State\Provider\Budget;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Security\User;
use App\Repository\Budget\BudgetExerciseRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CurrentlyOpennedBudgetExerciseProvider implements ProviderInterface
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly BudgetExerciseRepository $budgetExerciseRepo)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $budgetExercises = $this->budgetExerciseRepo->findAll();

        $openBudgetExercise = [];
        foreach ($budgetExercises as $budgetExercise){
            if($budgetExercise->isIsClose() === false){
                $openBudgetExercise[] = [
                    '@id' => "/api/get/budget-exercise/".$budgetExercise->getId(),
                    '@type' => 'BudgetExercise',
                    'id'=> $budgetExercise ->getId(),
                    'code'=> $budgetExercise->getCode(),
                    'startAt'=> $budgetExercise ->getStartAt(),
                    'endAt'=> $budgetExercise->getEndAt(),
                    'year'=> $budgetExercise->getYear(),
                ];
             }
        }
        return new JsonResponse(['hydra:member' => $openBudgetExercise]);
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
