<?php

namespace App\Controller\Budget;

use App\Entity\Security\User;
use App\Repository\Budget\BudgetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class CreateMultipleBudgetByRateController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,private readonly BudgetRepository $budgetRepository,private readonly EntityManagerInterface $manager)
    {
    }

    public function jsondecode()
    {
        try {
            return file_get_contents('php://input') ?
                json_decode(file_get_contents('php://input'), false) :
                [];
        }catch (\Exception $ex)
        {
            return [];
        }

    }

    public function __invoke(Request $request,
                             EntityManagerInterface $manager,
                             BudgetRepository $budgetRepository,)
    {
        $institution = $this->getUser()->getInstitution();

        $requestData = $this->jsondecode();

        $rate = $requestData->rate;
        $budgets = $requestData->budgets;

        foreach ($budgets as $budget)
        {
            $activeBudget = $this->budgetRepository->findOneBy(['id'=> $budget->id]);
            $amount = $activeBudget->getAmount();

            $rate2 = $rate / 100;
            $rateAmount = $rate2 * $amount;

            $activeBudget->setValidatedAmount($rateAmount);
            $activeBudget->setInstitution($institution);
            $this->manager->flush();
        }

        return $this->json(['hydra:member'=> $budgets]);
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



