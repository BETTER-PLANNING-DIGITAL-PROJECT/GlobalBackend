<?php
namespace App\Controller\Treasury;

use App\Entity\Security\User;
use App\Entity\Treasury\BankHistory;
use App\Entity\Treasury\BankOperation;
use App\Entity\Treasury\CashDeskHistory;
use App\Repository\Setting\Finance\OperationCategoryRepository;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\BankHistoryRepository;
use App\Repository\Treasury\BankOperationRepository;
use App\Repository\Treasury\CashDeskHistoryRepository;
use App\Repository\Treasury\CashDeskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BankOperationController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly CashDeskRepository $cashDeskRepository,
                                private readonly BankHistoryRepository $bankHistoryRepository,
                                private readonly OperationCategoryRepository $operationCategoryRepository,
                                private readonly BankAccountRepository $bankAccountRepository,
                                private readonly CashDeskHistoryRepository $cashDeskHistoryRepository,
                                private readonly BankOperationRepository $bankOperationRepository,
                                private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(mixed $data, Request $request): JsonResponse|BankOperation
    {
        // TODO: Implement process() method.
        if (!$data instanceof BankOperation){
            return new JsonResponse(['hydra:title' => 'Invalid entity process'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        // Get user cash desk
        $vault = $this->cashDeskRepository->findOneBy(['operator' => $this->getUser(), 'branch' => $this->getUser()->getBranch(), 'institution' => $this->getUser()->getInstitution()]);
        if (!$vault)
        {
            return new JsonResponse(['hydra:title' => 'You are not a cashier!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        // Check if current user is vault
        if (!$vault->isIsMain())
        {
            return new JsonResponse(['hydra:title' => 'Sorry you are not a main cashier'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if ($vault->isIsOpen() === false)
        {
            return new JsonResponse(['hydra:title' => 'Your cash desk is not open!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        $requestData = json_decode($request->getContent(), true);

        $amount = $requestData['amount'];
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

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $requestData['operationCategory']);
        $filterId = intval($filter);
        $operationCategory = $this->operationCategoryRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $requestData['bankAccount']);
        $filterId = intval($filter);
        $bankAccount = $this->bankAccountRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        if ($operationCategory->getCode() == 'WITHDRAWAL')
        {
            $bankAccountBalance = $bankAccount->getBalance();

            // Check Bank Account Balance
            if ($bankAccountBalance < $amount)
            {
                return new JsonResponse(['hydra:title' => 'Bank account balance is not enough!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }
        }

        if ($operationCategory->getCode() == 'DEPOSIT')
        {
            $vaultBalance = $vault->getBalance();

            // Check Vault Balance
            if ($vaultBalance < $amount)
            {
                return new JsonResponse(['hydra:title' => 'Your balance is not enough!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }
        }

        $bankOperation = $this->bankOperationRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$bankOperation){
            $uniqueNumber = 'BANK/OPE/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $bankOperation->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'BANK/OPE/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }
        $data->setReference($uniqueNumber);
        $data->setVault($vault);
        $data->setBranch($this->getUser()->getBranch());
        $data->setInstitution($this->getUser()->getInstitution());
        $data->setUser($this->getUser());

        // Validation
        $data->setIsValidate(true);
        $data->setValidateBy($this->getUser());
        $data->setValidateAt(new \DateTimeImmutable());

        $this->manager->persist($data);

        if ($operationCategory->getCode() == 'WITHDRAWAL')
        {
            // Write bank history
            $bankHistory = new BankHistory();
            $bankHistory->setBankAccount($bankAccount);
            $bankHistory->setReference($data->getReference());
            $bankHistory->setDescription($data->getDescription());
            $bankHistory->setDebit(0);
            $bankHistory->setCredit($amount);
            // balance : en bas
            $bankHistory->setDateAt(new \DateTimeImmutable());

            $bankHistory->setBranch($this->getUser()->getBranch());
            $bankHistory->setInstitution($this->getUser()->getInstitution());
            $bankHistory->setUser($this->getUser());
            $this->manager->persist($bankHistory);

            // Update bank account balance
            $bankHistories = $this->bankHistoryRepository->findBy(['bankAccount' => $bankAccount]);

            $debit = 0; $credit = $amount;

            foreach ($bankHistories as $item)
            {
                $debit += $item->getDebit();
                $credit += $item->getCredit();
            }

            $balance = $debit - $credit;

            $bankHistory->setBalance($balance);
            $bankAccount->setBalance($balance);

            // Write vault history
            $cashDeskHistory = new CashDeskHistory();
            $cashDeskHistory->setCashDesk($vault);
            $cashDeskHistory->setReference($data->getReference());
            $cashDeskHistory->setDescription($data->getDescription());
            $cashDeskHistory->setDebit($amount);
            $cashDeskHistory->setCredit(0);
            // balance : en bas
            $cashDeskHistory->setDateAt(new \DateTimeImmutable());

            $cashDeskHistory->setBranch($this->getUser()->getBranch());
            $cashDeskHistory->setInstitution($this->getUser()->getInstitution());
            $cashDeskHistory->setUser($this->getUser());
            $this->manager->persist($cashDeskHistory);

            // Update vault daily deposit balance
            $vault->setDailyDeposit($vault->getDailyDeposit() + $amount);

            // Update vault balance
            $vaultHistories = $this->cashDeskHistoryRepository->findBy(['cashDesk' => $vault]);

            $debit = $amount; $credit = 0;

            foreach ($vaultHistories as $item)
            {
                $debit += $item->getDebit();
                $credit += $item->getCredit();
            }

            $balance = $debit - $credit;

            $cashDeskHistory->setBalance($balance);
            $vault->setBalance($balance);
        }

        if ($operationCategory->getCode() == 'DEPOSIT')
        {
            // Write vault history
            $cashDeskHistory = new CashDeskHistory();
            $cashDeskHistory->setCashDesk($vault);
            $cashDeskHistory->setReference($data->getReference());
            $cashDeskHistory->setDescription($data->getDescription());
            $cashDeskHistory->setDebit(0);
            $cashDeskHistory->setCredit($amount);
            // balance : en bas
            $cashDeskHistory->setDateAt(new \DateTimeImmutable());

            $cashDeskHistory->setBranch($this->getUser()->getBranch());
            $cashDeskHistory->setInstitution($this->getUser()->getInstitution());
            $cashDeskHistory->setUser($this->getUser());
            $this->manager->persist($cashDeskHistory);

            // Update vault daily withdrawal balance
            $vault->setDailyWithdrawal($vault->getDailyWithdrawal() + $amount);

            // Update vault balance
            $vaultHistories = $this->cashDeskHistoryRepository->findBy(['cashDesk' => $vault]);

            $debit = 0; $credit = $amount;

            foreach ($vaultHistories as $item)
            {
                $debit += $item->getDebit();
                $credit += $item->getCredit();
            }

            $balance = $debit - $credit;

            $cashDeskHistory->setBalance($balance);
            $vault->setBalance($balance);


            // Write bank history
            $bankHistory = new BankHistory();
            $bankHistory->setBankAccount($bankAccount);
            $bankHistory->setReference($data->getReference());
            $bankHistory->setDescription($data->getDescription());
            $bankHistory->setDebit($amount);
            $bankHistory->setCredit(0);
            // balance : en bas
            $bankHistory->setDateAt(new \DateTimeImmutable());

            $bankHistory->setBranch($this->getUser()->getBranch());
            $bankHistory->setInstitution($this->getUser()->getInstitution());
            $bankHistory->setUser($this->getUser());
            $this->manager->persist($bankHistory);

            // Update bank balance
            $bankHistories = $this->bankHistoryRepository->findBy(['bankAccount' => $bankAccount]);

            $debit = $amount; $credit = 0;

            foreach ($bankHistories as $item)
            {
                $debit += $item->getDebit();
                $credit += $item->getCredit();
            }

            $balance = $debit - $credit;

            $bankHistory->setBalance($balance);
            $bankAccount->setBalance($balance);
        }

        $this->manager->flush();

        return $data;
        // return $this->processor->process($data, $operation, $uriVariables, $context);
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
