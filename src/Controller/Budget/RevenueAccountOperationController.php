<?php
namespace App\Controller\Budget;

use App\Entity\Budget\BudgetHistory;
use App\Entity\Budget\Needs;
use App\Entity\Budget\Revenue;
use App\Entity\Security\User;
use App\Entity\Treasury\BankHistory;
use App\Entity\Treasury\CashDeskHistory;
use App\Repository\Budget\BudgetHistoryRepository;
use App\Repository\Budget\BudgetRepository;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\BankRepository;
use App\Repository\Treasury\CashDeskHistoryRepository;
use App\Repository\Treasury\CashDeskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RevenueAccountOperationController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly CashDeskRepository $cashDeskRepository,
                                private readonly BankRepository $bankRepository,
                                private readonly BankAccountRepository $bankAccountRepository,
                                private readonly CashDeskHistoryRepository $cashDeskHistoryRepository,
                                private readonly BudgetHistoryRepository $budgetHistoryRepository,
                                private readonly BudgetRepository $budgetRepository,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(mixed $data, Request $request): JsonResponse|Revenue
    {
        $requestedData = json_decode($this->request->getContent(), true);

        if(!$data instanceof Revenue){
            return new JsonResponse(['hydra:description' => 'revenue not found.'], 404);
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

//    $cashDeskBalance = $cashDesk->getBalance();
//
//
//    // Check Vault Balance
//    if ($cashDeskBalance < $amount)
//    {
//        return new JsonResponse(['hydra:title' => 'Cash desk balance is not enough'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
//    }

        $bankAccount = $this->bankAccountRepository->find($this->getIdFromApiResourceId($requestedData['bankAccount']));

        // Write cash desk history
        $bankHistory = new BankHistory();
        $bankHistory->setBankAccount($bankAccount);
        $bankHistory->setReference($data->getReference());
        $bankHistory->setDescription('budget income transaction');
        $bankHistory->setDebit($amount);
        $bankHistory->setCredit(0);
        // balance : en bas
        $bankHistory->setDateAt(new \DateTimeImmutable());

        $bankHistory->setInstitution($this->getUser()->getInstitution());
        $bankHistory->setUser($this->getUser());
        $this->manager->persist($bankHistory);

        // Update cash desk daily withdrawal balance
        $cashDesk->setDailyDeposit($cashDesk->getDailyDeposit() + $amount);

        // Update cash desk balance
        $cashDeskHistories = $this->cashDeskHistoryRepository->findBy(['cashDesk' => $cashDesk]);

        $debit = $amount; $credit = 0;

        foreach ($cashDeskHistories as $item)
        {
            $debit += $item->getDebit();
            $credit += $item->getCredit();
        }

        $balance = $debit - $credit;

        $bankHistory->setBalance($balance);
        $cashDesk->setBalance($balance);

        // Write vault history
        $budgetHistory = new BudgetHistory();
        $budgetHistory->setBudget($data->getBudget());
        $budgetHistory->setReference($data->getReference());
        $budgetHistory->setDescription('account transaction');
        $budgetHistory->setDebit(0);
        $budgetHistory->setCredit($amount);
        // balance : en bas
        $budgetHistory->setDateAt(new \DateTimeImmutable());

        $budgetHistory->setInstitution($this->getUser()->getInstitution());
        $budgetHistory->setYear($this->getUser()->getCurrentYear());
        $budgetHistory->setUser($this->getUser());
        $this->manager->persist($budgetHistory);

        // Update vault balance
        $budgetHistories = $this->budgetHistoryRepository->findBy(['budget' => $data]);

        $debit = 0; $credit = $amount;

        foreach ($budgetHistories  as $item)
        {
            $debit += $item->getDebit();
            $credit += $item->getCredit();
        }

        $balance = $debit + $credit;

        $budgetHistory->setBalance($balance);

        // budget update
        $budget = $this->budgetRepository->findOneBy(['id' => $data->getBudget()]);

        $budget->setSpentAmount($budget->getSpentAmount() + $data->getValidatedAmount());
        $budget->setLeftAmount($budget->getLeftAmount() + $data->getValidatedAmount());


        if (isset($requestedData['bank'])){
            $bank = $this->bankRepository->find($this->getIdFromApiResourceId($requestedData['bank']));
            $data->setBank($bank);
        }
        if (isset($requestedData['bankAccount'])){
            $bankAccount = $this->bankAccountRepository->find($this->getIdFromApiResourceId($requestedData['bankAccount']));
            $data->setBankAccount($bankAccount);
        }

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
