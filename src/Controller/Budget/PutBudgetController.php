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
class PutBudgetController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data,Request $request, BudgetRepository $budgetRepo)
    {
        $line = $data->getLine();
        $exercise = $data->getExercise();
        $validatedAmount = $data->getValidatedAmount();
        $institution = $this->getUser()->getInstitution();

        $existingBudget = $budgetRepo->findOneBy(['line' => $line, 'exercise' => $exercise, 'institution' => $institution]);
        if ($existingBudget && ($existingBudget != $data)) {
            return new JsonResponse(['hydra:description' => 'This budget line already exists for this exercise.'], 400);
        }
        // Check if validatedAmount is set before updating allocatedAmount
        if ($validatedAmount !== null) {
            $data->setAllocatedAmount($validatedAmount);
            $data->setSpentAmount('0');
            $data->setLeftAmount($validatedAmount);
        }
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
