<?php

namespace App\Controller\Budget;

use App\Entity\Budget\Budget;
use App\Entity\Security\User;
use App\Repository\Budget\BudgetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PostBudgetController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data,Request $request, BudgetRepository $budgetRepo)
    {
        $line = $data->getLine();
        $exercise = $data->getExercise();
        $institution = $this->getUser()->getInstitution();
        $amount = $data->getAmount();
        $validatedAmount = $data->getValidatedAmount();

        $existingBudget = $budgetRepo->findOneBy(['line' => $line, 'exercise' => $exercise, 'institution' => $institution]);

        if ($existingBudget) {
            // Budget combination already exists
            return new JsonResponse(['hydra:description' => 'This budget line already exists for this exercise.'], 400);
        } else {
            // Create a new budget entry
            $newBudget = new Budget();
            $newBudget->setLine($line);
            $newBudget->setExercise($exercise);
            $newBudget->setInstitution($institution);
            $newBudget->setAmount($amount);
            $newBudget->setValidatedAmount($validatedAmount);
            $newBudget->setUser($this->getUser());
            $newBudget->setYear($this->getUser()->getCurrentYear());

            return $newBudget;
        }

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
