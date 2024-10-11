<?php

namespace App\Controller\Budget;

use App\Entity\Security\User;
use App\Repository\Budget\BudgetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PutRevenueController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data,Request $request, BudgetRepository $budgetRepo)
    {
        $requestedAmount = $data->getRequestAmount();
        $validatedAmount = $data->getValidatedAmount();
        $isEdited = $data->isIsEdited();


        if(!$isEdited){
            $data->setIsValidated(true);
            $data->setValidatedAmount($validatedAmount);
        } else {
            $data->setValidatedAmount($requestedAmount);
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
