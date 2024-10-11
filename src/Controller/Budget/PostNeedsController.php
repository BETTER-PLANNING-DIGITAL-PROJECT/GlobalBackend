<?php

namespace App\Controller\Budget;

use App\Entity\Security\User;
use App\Repository\Budget\BudgetRepository;
use App\Repository\Budget\NeedsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PostNeedsController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data,Request $request, BudgetRepository $budgetRepo, NeedsRepository $needsRepo)
    {
        $budget = $data->getBudget();
        $budgetExercise = $data->getExercise();
        $requestedAmount = $data->getRequestAmount();
        $isOpen = $data->isIsOpen();

        $data->setValidatedAmount($requestedAmount);
        $data->setIsValidated(false);
        if ($isOpen) {
            $existingBudget = $budgetRepo->findOneBy(['id' => $budget->getId(), 'exercise' => $budgetExercise->getId(),]);
                if ($existingBudget) {
                    $leftAmount = $existingBudget->getLeftAmount();
                    if ($requestedAmount > $leftAmount) {
                        return new JsonResponse(['hydra:description' => 'Insufficient funds, requested amount is more than the amount left.'], 400);
                    }
                }
        }

        $data->setInstitution($this->getUser()->getInstitution());
        $data->setUser($this->getUser());
        $data->setYear($this->getUser()->getCurrentYear());

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
