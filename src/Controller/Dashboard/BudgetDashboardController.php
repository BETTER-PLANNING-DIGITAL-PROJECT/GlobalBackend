<?php

namespace App\Controller\Dashboard;
;
use App\Entity\Security\User;
use App\Repository\Budget\BudgetManagerRepository;
use App\Repository\Budget\BudgetRepository;
use App\Repository\Budget\NeedsRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\BankOperationRepository;
use App\Repository\Treasury\BankRepository;
use App\Repository\Treasury\CashDeskOperationRepository;
use App\Repository\Treasury\CashDeskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class BudgetDashboardController extends AbstractController
{
    private EntityManagerInterface $manager;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                Request                                $req,
                                EntityManagerInterface $manager,
                                BudgetManagerRepository $budgetManagerRepository,
                                BudgetRepository $budgetRepository,
                                NeedsRepository $needsRepository,
    )
    {
        $this->manager = $manager;
        $this->budgetManagerRepository = $budgetManagerRepository;
        $this->budgetRepository = $budgetRepository;
        $this->needsRepository = $needsRepository;
    }


    #[Route('/api/get/budget-dashboard/budget-manager-list', name: 'app_get_budget_dashboard_budget_manager_list')]
    public function getManagerList(): JsonResponse
    {
        $managers = $this->budgetManagerRepository->findAll();

        $budgetManager = [];
        $count = 0;

        foreach ($managers as $manager) {
            $budgetManager[] = [
                'id' => $manager->getId(),
                'code' => $manager->getCode(),
                'name' => $manager->getName(),
                'amount' => $manager->getAmount(),
            ];
            $count++;

            // Stop after adding 5 cash desks
            if ($count >= 5) {
                break;
            }
        }

        return new JsonResponse(['hydra:description' => $budgetManager]);
    }


    #[Route('/api/get/budget-income', name: 'app_get_budget_income')]
    public function getBudgetIncome(): JsonResponse
    {
        $budgets = $this->budgetRepository->findAll();
        $totalIncome = 0;
        $totalAllocated = 0;
        $totalSpent = 0;
        $totalLeft = 0;


        foreach ($budgets as $budget) {
            if($budget->getLine()->getHeading()->getNature()->getName() == 'INCOME'){

                $totalIncome += $budget->getValidatedAmount();
                $totalAllocated += $budget->getAllocatedAmount();
                $totalSpent += $budget->getSpentAmount();
                $totalLeft += $budget->getLeftAmount();
            }
        }

        return new JsonResponse(['hydra:description' => $totalIncome, 'allocated' => $totalAllocated, 'spent' => $totalSpent, 'left' => $totalLeft]);
    }

    #[Route('/api/get/budget-expense', name: 'app_get_budget_expense')]
    public function getBudgetExpense(): JsonResponse
    {
        $budgets = $this->budgetRepository->findAll();
        $totalIncome = 0;
        $totalAllocated = 0;
        $totalSpent = 0;
        $totalLeft = 0;


        foreach ($budgets as $budget) {
            if($budget->getLine()->getHeading()->getNature()->getName() == 'EXPENSE'){

                $totalIncome += $budget->getValidatedAmount();
                $totalAllocated += $budget->getAllocatedAmount();
                $totalSpent += $budget->getSpentAmount();
                $totalLeft += $budget->getLeftAmount();
            }
        }

        return new JsonResponse(['hydra:description' => $totalIncome, 'allocated' => $totalAllocated, 'spent' => $totalSpent, 'left' => $totalLeft]);
    }

    #[Route('/api/get/needs-list', name: 'app_get_needs_list')]
    public function getNeedsList(): JsonResponse
    {
        $needs = $this->needsRepository->findAll();

        $budgetNeeds = [];
        $count = 0;

        foreach ($needs as $need) {
            $budgetNeeds[] = [
                'id' => $need->getId(),
                'applicant' => $need->getApplicant(),
                'reason' => $need->getReason(),
                'validatedAmount' => $need->getValidatedAmount(),
            ];
            $count++;

            // Stop after adding 5 cash desks
            if ($count >= 5) {
                break;
            }
        }

        return new JsonResponse(['hydra:description' => $budgetNeeds]);
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
