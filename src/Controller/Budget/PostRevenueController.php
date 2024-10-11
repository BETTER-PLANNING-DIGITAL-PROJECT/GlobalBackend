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
class PostRevenueController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data,Request $request, BudgetRepository $budgetRepo, NeedsRepository $needsRepo)
    {
        $requestedAmount = $data->getRequestAmount();

        $data->setValidatedAmount($requestedAmount);
        $data->setIsValidated(false);

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
