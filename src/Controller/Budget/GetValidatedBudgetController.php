<?php

namespace App\Controller\Budget;

use App\Entity\Security\User;
use App\Repository\Budget\BudgetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetValidatedBudgetController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(Request $request, BudgetRepository $budgetRepo):JsonResponse
    {
        $budgets = $budgetRepo->findAll();

        $myBudgets = [];
        foreach ($budgets as $budget){
            if($budget->getValidatedAmount() !== null  && !$budget->getLine()->isIsType()) {
                $myBudgets [] = [
                    '@id' => "/api/get/budget/" . $budget->getId(),
                    '@type' => 'Budget',
                    'id' => $budget->getId(),
                    'amount' => $budget->getAmount(),
                    'validatedAmount' => $budget->getValidatedAmount(),
                    'allocatedAmount' => $budget->getAllocatedAmount(),
                    'spentAmount' => $budget->getSpentAmount(),
                    'leftAmount' => $budget->getLeftAmount(),
                    'line' => $budget->getLine(),
                    'exercise' => $budget->getExercise(),
                ];
            }
        }

        return $this->json(['hydra:member' => $myBudgets]);

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
