<?php

namespace App\Controller\Budget;

use App\Entity\Budget\Budget;
use App\Entity\Security\User;
use App\Repository\Budget\BudgetRepository;
use App\Repository\Budget\BudgetReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PostBudgetReviewController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data,Request $request, BudgetReviewRepository $budgetReviewRepo, BudgetRepository $budgetRepo)
    {
     $budget = $data->getBudget();
     $amount = $data->getAmount();
     $isType = $data->isIsType();

     $existingBudgets = $budgetRepo->findAll();

     foreach ($existingBudgets as $existingBudget){
         if($existingBudget->getId() == $budget->getId() && $isType == 1){

          $newAllocatedAmount = $existingBudget->getAllocatedAmount() + $amount;
          $existingBudget->setAllocatedAmount($newAllocatedAmount);
         }
         elseif ($existingBudget->getId() == $budget->getId() && $isType == 0){
             $newAllocatedAmount = $existingBudget->getAllocatedAmount() - $amount;
             $existingBudget->setAllocatedAmount($newAllocatedAmount);
         }

         $newLeftAmount = $existingBudget->getAllocatedAmount() - $existingBudget->getSpentAmount();

//         $existingBudget->setSpentAmount('0');
         $existingBudget->setLeftAmount($newLeftAmount);
         $budgetRepo->save($existingBudget);

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
