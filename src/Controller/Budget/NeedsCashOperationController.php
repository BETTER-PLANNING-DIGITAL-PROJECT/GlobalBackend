<?php
namespace App\Controller\Budget;

use App\Entity\Budget\BudgetHistory;
use App\Entity\Budget\Needs;
use App\Entity\Security\User;
use App\Entity\Treasury\CashDeskHistory;
use App\Entity\Treasury\CashDeskOperation;
use App\Repository\Budget\BudgetHistoryRepository;
use App\Repository\Budget\BudgetRepository;
use App\Repository\Setting\Finance\OperationCategoryRepository;
use App\Repository\Treasury\CashDeskHistoryRepository;
use App\Repository\Treasury\CashDeskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NeedsCashOperationController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly CashDeskRepository $cashDeskRepository,
                                private readonly OperationCategoryRepository $operationCategoryRepository,
                                private readonly CashDeskHistoryRepository $cashDeskHistoryRepository,
                                private readonly BudgetHistoryRepository $budgetHistoryRepository,
                                private readonly BudgetRepository $budgetRepository,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(mixed $data, Request $request): JsonResponse|Needs
    {
        $requestedData = json_decode($this->request->getContent(), true);
//        dd($requestedData);

        if(!$data instanceof Needs){
            return new JsonResponse(['hydra:description' => 'needs not found.'], 404);
        }

       // get gurrent user cash desk
        $cashDesk = $this->cashDeskRepository->findOneBy(['operator' => $this->getUser()]);
//        dd($cashDesk);

        if (!$cashDesk)
        {
            return new JsonResponse(['hydra:title' => 'Sorry you dont have a cash desk'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if (!$cashDesk->isIsOpen())
        {
            return new JsonResponse(['hydra:title' => 'Current cash desk is close'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if (!$cashDesk->isIsEnable())
        {
            return new JsonResponse(['hydra:title' => 'Current cash desk is not enabled'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }


        $amount = $data->getValidatedAmount();
        if (!is_numeric($amount))
        {
            return new JsonResponse(['hydra:title' => 'Amount should be a number'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }
        elseif ($amount < 0)
        {
            return new JsonResponse(['hydra:title' => 'Amount should not be less than zero '], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }
        elseif ($amount == 0)
        {
            return new JsonResponse(['hydra:title' => 'Amount should not be equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

    $cashDeskBalance = $cashDesk->getBalance();


    // Check Vault Balance
    if ($cashDeskBalance < $amount)
    {
        return new JsonResponse(['hydra:title' => 'Cash desk balance is not enough'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
    }

        // Write cash desk history
        $cashDeskHistory = new CashDeskHistory();
        $cashDeskHistory->setCashDesk($cashDesk);
        $cashDeskHistory->setReference($data->getReference());
        $cashDeskHistory->setDescription('budget transaction');
        $cashDeskHistory->setDebit(0);
        $cashDeskHistory->setCredit($amount);
        // balance : en bas
        $cashDeskHistory->setDateAt(new \DateTimeImmutable());

        $cashDeskHistory->setInstitution($this->getUser()->getInstitution());
        $cashDeskHistory->setUser($this->getUser());
        $this->manager->persist($cashDeskHistory);

        // Update cash desk daily withdrawal balance
        $cashDesk->setDailyWithdrawal($cashDesk->getDailyWithdrawal() + $amount);

        // Update cash desk balance
        $cashDeskHistories = $this->cashDeskHistoryRepository->findBy(['cashDesk' => $cashDesk]);

        $debit = 0; $credit = $amount;

        foreach ($cashDeskHistories as $item)
        {
            $debit += $item->getDebit();
            $credit += $item->getCredit();
        }

        $balance = $debit - $credit;

        $cashDeskHistory->setBalance($balance);
        $cashDesk->setBalance($balance);

        // Write vault history
        $budgetHistory = new BudgetHistory();
        $budgetHistory->setBudget($data->getBudget());
        $budgetHistory->setReference($data->getReference());
        $budgetHistory->setDescription('cash transaction');
        $budgetHistory->setDebit($amount);
        $budgetHistory->setCredit(0);
        // balance : en bas
        $budgetHistory->setDateAt(new \DateTimeImmutable());

        $budgetHistory->setInstitution($this->getUser()->getInstitution());
        $budgetHistory->setYear($this->getUser()->getCurrentYear());
        $budgetHistory->setUser($this->getUser());
        $this->manager->persist($budgetHistory);

        // Update vault balance
        $budgetHistories = $this->budgetHistoryRepository->findBy(['budget' => $data]);

        $debit = $amount; $credit = 0;

        foreach ($budgetHistories  as $item)
        {
            $debit += $item->getDebit();
            $credit += $item->getCredit();
        }

        $balance = $debit - $credit;

        $budgetHistory->setBalance($balance);

        // budget update
        $budget = $this->budgetRepository->findOneBy(['id' => $data->getBudget()]);

        $budget->setSpentAmount($budget->getSpentAmount() + $data->getValidatedAmount());
        $budget->setLeftAmount($budget->getLeftAmount() - $data->getValidatedAmount());


        $data->setCashDesk($cashDesk);
        $new = new \DateTimeImmutable($requestedData['settled_At']);
        $data->setSettledAt($new);
        $data->setSettledBy($this->getUser());

        $this->manager->flush();

        return $data;
    }


    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
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
