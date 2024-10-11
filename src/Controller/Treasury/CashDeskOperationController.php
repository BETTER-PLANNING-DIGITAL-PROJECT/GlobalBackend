<?php
namespace App\Controller\Treasury;

use App\Entity\Security\User;
use App\Entity\Treasury\CashDeskHistory;
use App\Entity\Treasury\CashDeskOperation;
use App\Repository\Setting\Finance\OperationCategoryRepository;
use App\Repository\Treasury\CashDeskHistoryRepository;
use App\Repository\Treasury\CashDeskOperationRepository;
use App\Repository\Treasury\CashDeskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CashDeskOperationController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly CashDeskRepository $cashDeskRepository,
                                private readonly OperationCategoryRepository $operationCategoryRepository,
                                private readonly CashDeskHistoryRepository $cashDeskHistoryRepository,
                                private readonly CashDeskOperationRepository $cashDeskOperationRepository,
                                private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(mixed $data, Request $request): JsonResponse|CashDeskOperation
    {
        // TODO: Implement process() method.
        if (!$data instanceof CashDeskOperation){
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
            return new JsonResponse(['hydra:title' => 'your cash desk is not open!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
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
        $filter = preg_replace("/[^0-9]/", '', $requestData['cashDesk']);
        $filterId = intval($filter);
        $cashDesk = $this->cashDeskRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        if ($operationCategory->getCode() == 'WITHDRAWAL')
        {
            $cashDeskBalance = $cashDesk->getBalance();

            // Check Cash Desk Balance
            if ($cashDeskBalance < $amount)
            {
                return new JsonResponse(['hydra:title' => 'Cash desk balance is not enough!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }
        }

        if ($operationCategory->getCode() == 'DEPOSIT')
        {
            $cashDeskBalance = $vault->getBalance();

            // Check Vault Balance
            if ($cashDeskBalance < $amount)
            {
                return new JsonResponse(['hydra:title' => 'Your balance is not enough!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

        }

        $data->setVault($vault);

        $cashDeskOperation = $this->cashDeskOperationRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$cashDeskOperation){
            $uniqueNumber = 'CASH/OPE/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $cashDeskOperation->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'CASH/OPE/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $data->setReference($uniqueNumber);
        $data->setBranch($this->getUser()->getBranch());
        $data->setInstitution($this->getUser()->getInstitution());
        $data->setUser($this->getUser());

        $this->manager->persist($data);

        if ($operationCategory->getCode() == 'WITHDRAWAL')
        {
            // Write cash desk history
            $cashDeskHistory = new CashDeskHistory();

            $cashDeskHistory->setCashDesk($cashDesk);
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
            $cashDeskHistories = $this->cashDeskHistoryRepository->findBy(['cashDesk' => $vault]);

            $debit = 0; $credit = $amount;

            foreach ($cashDeskHistories  as $item)
            {
                $debit += $item->getDebit();
                $credit += $item->getCredit();
            }

            $balance = $debit - $credit;

            $cashDeskHistory->setBalance($balance);
            $vault->setBalance($balance);

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
