<?php

namespace App\Controller\Dashboard;
;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\BankOperationRepository;
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
class TreasuryDashboardController extends AbstractController
{
    private EntityManagerInterface $manager;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                Request                                $req,
                                EntityManagerInterface $manager,
                                CashDeskRepository          $cashDeskRepository,
                                BankAccountRepository          $bankAccountRepository,
                                CashDeskOperationRepository          $cashDeskOperationRepository,
                                BankOperationRepository          $bankOperationRepository
    )
    {
        $this->manager = $manager;
        $this->cashDeskRepository = $cashDeskRepository;
        $this->bankAccountRepository = $bankAccountRepository;
        $this->cashDeskOperationRepository = $cashDeskOperationRepository;
        $this->bankOperationRepository = $bankOperationRepository;
    }


    #[Route('/api/treasury/dashboard/cash-desk/balance', name: 'app_treasury_dashboard_cash_desk_balance')]
    public function getCashDeskBalance(): JsonResponse
    {
        // get current user cash desk
        $cashDesk = $this->cashDeskRepository->findOneBy(['operator' => $this->getUser(), 'institution' => $this->getUser()->getInstitution(), 'branch' => $this->getUser()->getBranch()]);

        if($cashDesk){
            $cashDeskBalance[] = [
                'id'=> $cashDesk ->getId(),
                'balance'=> $cashDesk->getBalance(),
                'dailyDeposit'=> $cashDesk->getDailyDeposit(),
                'dailyWithdrawal'=> $cashDesk->getDailyWithdrawal(),
            ];
        }

        /*$currentUser = $this->getUser();
        $cashDesks = $this->cashDeskRepository->findAll();

        $cashDeskBalance = [];
        foreach ($cashDesks as $cashDesk){
            if($cashDesk->getOperator()->getId() === $currentUser->getId()){
                $cashDeskBalance[] = [
                    'id'=> $cashDesk ->getId(),
                    'balance'=> $cashDesk->getBalance(),
                    'dailyDeposit'=> $cashDesk->getDailyDeposit(),
                    'dailyWithdrawal'=> $cashDesk->getDailyWithdrawal(),
                ];

            }
        }*/

        return new JsonResponse(['hydra:description' => $cashDeskBalance]);

    }

    #[Route('/api/treasury/dashboard/other/cash-desk', name: 'app_treasury_dashboard_other_cash_desk')]
    public function getOtherCashDesk(): JsonResponse
    {
        $cashDeskData = [];

        // get current user cash desk
        $cashDesk = $this->cashDeskRepository->findOneBy(['operator' => $this->getUser(), 'institution' => $this->getUser()->getInstitution(), 'branch' => $this->getUser()->getBranch()]);

        // check if current user is a cashier
        if($cashDesk)
        {
            // check if current user is a vault
            if($cashDesk->isIsMain())
            {
                // get cash desk
                $cashDesks = $this->cashDeskRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC'], 10);
                foreach ($cashDesks as $cashDesk)
                {
                    $cashDeskData[] = [
                        'id' => $cashDesk->getId(),
                        'balance' => $cashDesk->getBalance(),
                        'code' => $cashDesk->getCode(),
                        'isOpen' => $cashDesk->isIsOpen(),
                    ];
                }
            }
            else{
                $cashDeskData[] = [
                    'id' => $cashDesk->getId(),
                    'balance' => $cashDesk->getBalance(),
                    'code' => $cashDesk->getCode(),
                    'isOpen' => $cashDesk->isIsOpen(),
                ];
            }
        }

        return new JsonResponse(['hydra:description' => $cashDeskData]);
    }

    #[Route('/api/treasury/dashboard/cash-desk-operation', name: 'app_treasury_dashboard_cash_desk_operation')]
    public function getCashDeskOperation(): JsonResponse
    {
        $cashDeskOperationsData = [];

        // get current user cash desk
        $cashDesk = $this->cashDeskRepository->findOneBy(['operator' => $this->getUser(), 'institution' => $this->getUser()->getInstitution(), 'branch' => $this->getUser()->getBranch()]);

        if($cashDesk)
        {
            // check if current user is a vault
            if($cashDesk->isIsMain())
            {
                // last five cash desk operations
                $cashDeskOperations = $this->cashDeskOperationRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC'], 10);
            }
            else{
                // last five cash desk operations
                $cashDeskOperations = $this->cashDeskOperationRepository->findBy(['cashDesk' => $cashDesk, 'branch' => $this->getUser()->getBranch()], ['id' => 'DESC'], 10);
            }

            foreach ($cashDeskOperations as $cashDeskOperation)
            {
                $cashDeskOperationsData[] = [
                    'id'=> $cashDeskOperation ->getId(),
                    'reference'=> $cashDeskOperation->getReference(),
                    'description'=> $cashDeskOperation->getDescription(),
                    'amount'=> $cashDeskOperation->getAmount(),
                    'isValidate'=> $cashDeskOperation->isIsValidate(),
                    'createdAt'=> $cashDeskOperation->getCreatedAt()?->format('Y-m-d'),
                    'cashDesk' => [
                        '@type' => "CashDesks",
                        'id' => $cashDeskOperation->getCashDesk() ? $cashDeskOperation->getCashDesk()->getId() : '',
                        'code' => $cashDeskOperation->getCashDesk()->getCode(),
                        'balance' => $cashDeskOperation->getCashDesk()->getBalance(),
                    ],
                    'operationCategory' => [
                        '@id' => "/api/operationCategories/".$cashDeskOperation->getOperationCategory()->getId(),
                        '@type' => "operationCategories",
                        'id' => $cashDeskOperation->getOperationCategory() ? $cashDeskOperation->getOperationCategory()->getId() : '',
                        'code' => $cashDeskOperation->getOperationCategory()->getCode(),
                        'name' => $cashDeskOperation->getOperationCategory()->getName(),
                    ],
                    'validatedBy' => [
                        '@type' => "User",
                        'id' => $cashDeskOperation->getValidateBy() ? $cashDeskOperation->getValidateBy()->getId() : '',
                        'userName' => $cashDeskOperation->getValidateBy() ? $cashDeskOperation->getValidateBy()->getUsername() : '',
                    ],
                ];
            }
        }

        return new JsonResponse(['hydra:description' => $cashDeskOperationsData]);

    }

    #[Route('/api/treasury/dashboard/bank-account', name: 'app_treasury_dashboard_bank_account')]
    public function getBankAccount(): JsonResponse
    {
        $bankAccounts = $this->bankAccountRepository->findBy([], ['id' => 'DESC'], 10);

        $bankAccountData = [];

        foreach ($bankAccounts as $bankAccount){
            $bankAccountData[] = [
                    'id'=> $bankAccount ->getId(),
                    'accountNumber'=> $bankAccount->getAccountNumber(),
                    'balance'=> $bankAccount->getBalance(),
                    'bank' => [
                        '@id' => "/api/banks/".$bankAccount->getBank()->getId(),
                        '@type' => "Banks",
                        'id' => $bankAccount->getBank()->getId(),
                        'code' => $bankAccount->getBank()->getCode(),
                        'name' => $bankAccount->getBank()->getName(),
                    ],
                ];
        }

        return new JsonResponse(['hydra:description' => $bankAccountData]);

    }

    #[Route('/api/treasury/dashboard/bank-operation', name: 'app_treasury_dashboard_bank_operation')]
    public function getBankOperation(): JsonResponse
    {
        $currentUser = $this->getUser();
        $bankOperations = $this->bankOperationRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC'], 10);

        $bankOperationsData = [];

        foreach ($bankOperations as $bankOperation){
            if ($bankOperation->getBankAccount()->getBank()->getUser()->getId() === $currentUser->getId()){
                $bankOperationsData[] = [
                    'id'=> $bankOperation ->getId(),
                    'reference'=> $bankOperation ->getReference(),
                    'description'=> $bankOperation ->getDescription(),
                    'amount'=> $bankOperation ->getAmount(),
                    'isValidate'=> $bankOperation ->isIsValidate(),
                    'createdAt'=> $bankOperation->getCreatedAt()?->format('Y-m-d'),
                    'bankAccount' => [
                        '@type' => "BankAccount",
                        'id' => $bankOperation->getBankAccount() ? $bankOperation->getBankAccount()->getId() : '',
                        'accountName' => $bankOperation->getBankAccount()->getAccountName(),
                        'accountNumber' => $bankOperation->getBankAccount()->getAccountNumber(),
                        'balance' => $bankOperation->getBankAccount()->getBalance(),
                    ],
                    'operationCategory' => [
                        '@type' => "operationCategories",
                        'id' => $bankOperation->getOperationCategory() ? $bankOperation->getOperationCategory()->getId() : '',
                        'code' => $bankOperation->getOperationCategory() ? $bankOperation->getOperationCategory()->getCode() : '',
                        'name' => $bankOperation->getOperationCategory()->getName(),
                    ],
                    'validatedBy' => [
                        '@type' => "User",
                        'id' => $bankOperation->getValidateBy() ? $bankOperation->getValidateBy()->getId() : '',
                        'userName' => $bankOperation->getValidateBy() ? $bankOperation->getValidateBy()->getUsername() : '',
                    ],
                ];

            }
        }

        return new JsonResponse(['hydra:description' => $bankOperationsData]);
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
