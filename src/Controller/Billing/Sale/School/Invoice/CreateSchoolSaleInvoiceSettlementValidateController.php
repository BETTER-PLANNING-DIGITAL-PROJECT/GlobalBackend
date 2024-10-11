<?php

namespace App\Controller\Billing\Sale\School\Invoice;

use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Partner\StudentRegistrationHistory;
use App\Entity\Security\User;
use App\Entity\Treasury\BankHistory;
use App\Entity\Treasury\CashDeskHistory;
use App\Repository\Billing\Sale\SaleInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Partner\CustomerHistoryRepository;
use App\Repository\Partner\StudentRegistrationHistoryRepository;
use App\Repository\Setting\Finance\PaymentGatewayRepository;
use App\Repository\Setting\Finance\PaymentMethodRepository;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\BankHistoryRepository;
use App\Repository\Treasury\BankRepository;
use App\Repository\Treasury\CashDeskHistoryRepository;
use App\Repository\Treasury\CashDeskRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class CreateSchoolSaleInvoiceSettlementValidateController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleInvoiceFeeRepository $saleInvoiceFeeRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             SaleSettlementRepository $saleSettlementRepository,
                             EntityManagerInterface $entityManager,
                             CashDeskRepository $cashDeskRepository,
                             CashDeskHistoryRepository $cashDeskHistoryRepository,
                             CustomerHistoryRepository $customerHistoryRepository,
                             StudentRegistrationHistoryRepository $studentRegistrationHistoryRepository,
                             PaymentMethodRepository $paymentMethodRepository,
                             BankHistoryRepository $bankHistoryRepository,
                             BankAccountRepository $bankAccountRepository,
                             BankRepository $bankRepository,
                             FileUploader $fileUploader,
                             PaymentGatewayRepository $paymentGatewayRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $uploadedFile = $request->files->get('file');

        $invoice = $saleInvoiceRepository->find($id);
        if (!$invoice){
            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }

        $saleSettlement = new SaleSettlement();

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('paymentMethod'));
        $filterId = intval($filter);
        $paymentMethod = $paymentMethodRepository->find($filterId);

        if (!$paymentMethod){
            return new JsonResponse(['hydra:description' => 'Payment method not found.'], 404);
        }

        if ($paymentMethod->isIsCashDesk())
        {
            $userCashDesk = $cashDeskRepository->findOneBy(['operator' => $this->getUser(), 'institution' => $this->getUser()->getInstitution()]);
            if (!$userCashDesk)
            {
                return new JsonResponse(['hydra:title' => 'You are not a cashier!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            if (!$userCashDesk->isIsOpen())
            {
                return new JsonResponse(['hydra:title' => 'You cash desk is not open!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

        }
        // END: Filter the uri to just take the id and pass it to our object

        /*if ($request->get('paymentGateway') !== null && $request->get('paymentGateway')){

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('paymentGateway'));
            $filterId = intval($filter);
            $paymentGateway = $paymentGatewayRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $saleSettlement->setPaymentGateway($paymentGateway);
        }*/

        if ($request->get('bank') !== null && $request->get('bank')){

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('bank'));
            $filterId = intval($filter);
            $bank = $bankRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $saleSettlement->setBank($bank);
        }

        if($request->get('amountPay') <= 0 ){
            return new JsonResponse(['hydra:title' => 'Amount can not be less or equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if ($request->get('amountPay') > $invoice->getVirtualBalance())
        {
            return new JsonResponse(['hydra:title' => 'Amount can not be more than balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if (!$invoice->getStudentRegistration())
        {
            return new JsonResponse(['hydra:title' => 'Student not found!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }


        // CREATE SETTLEMENT SECTION

        // Set settlement
        $saleSettlement->setPaymentMethod($paymentMethod);
        $saleSettlement->setSettleAt(new \DateTimeImmutable($request->get('settleAt')));
        $saleSettlement->setInvoice($invoice);

        $saleSettlement->setUser($this->getUser());
        $saleSettlement->setYear($this->getUser()->getCurrentYear());
        $saleSettlement->setBranch($this->getUser()->getBranch());
        $saleSettlement->setInstitution($this->getUser()->getInstitution());

        $saleSettlement->setStudentRegistration($invoice->getStudentRegistration());
        $saleSettlement->setClass($invoice->getClass());
        $saleSettlement->setSchool($invoice->getSchool());
        //$saleSettlement->setCustomer($invoice->getCustomer());

        $generateSettlementUniqNumber = $saleSettlementRepository->findOneBy([], ['id' => 'DESC']);

        if (!$generateSettlementUniqNumber){
            $uniqueNumber = 'SAL/SET/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $generateSettlementUniqNumber->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'SAL/SET/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $saleSettlement->setReference($uniqueNumber);

        $saleSettlement->setAmountPay($request->get('amountPay'));
        $saleSettlement->setAmountRest($invoice->getVirtualBalance() - $request->get('amountPay'));

        // Update invoice
        $invoice->setVirtualBalance($invoice->getVirtualBalance() - $saleSettlement->getAmountPay());

        // Persist settlement
        $entityManager->persist($saleSettlement);


        // VALIDATE SETTLEMENT SECTION

        $amount = $request->get('amountPay');

        if ($paymentMethod->isIsCashDesk())
        {
            $userCashDesk = $cashDeskRepository->findOneBy(['operator' => $this->getUser(), 'institution' => $this->getUser()->getInstitution()]);
            if (!$userCashDesk)
            {
                return new JsonResponse(['hydra:title' => 'user_is_not_cash_desk'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            // Write cash desk history
            $cashDeskHistory = new CashDeskHistory();
            $cashDeskHistory->setCashDesk($userCashDesk);
            $cashDeskHistory->setReference($saleSettlement->getReference());
            $cashDeskHistory->setDescription('invoice settlement');
            $cashDeskHistory->setDebit($amount);
            $cashDeskHistory->setCredit(0);
            // balance : en bas
            $cashDeskHistory->setDateAt(new \DateTimeImmutable());

            //$cashDeskHistory->setBranch($this->getUser()->getBranch());
            $cashDeskHistory->setInstitution($this->getUser()->getInstitution());
            $cashDeskHistory->setUser($this->getUser());
            $cashDeskHistory->setYear($this->getUser()->getCurrentYear());
            $entityManager->persist($cashDeskHistory);

            // Update cash desk daily deposit balance
            $userCashDesk->setDailyDeposit($userCashDesk->getDailyDeposit() + $amount);

            // Update cash desk balance
            $cashDeskHistories = $cashDeskHistoryRepository->findBy(['cashDesk' => $userCashDesk]);

            $debit = $amount; $credit = 0;

            foreach ($cashDeskHistories as $item)
            {
                $debit += $item->getDebit();
                $credit += $item->getCredit();
            }

            $balance = $debit - $credit;

            $cashDeskHistory->setBalance($balance);
            $userCashDesk->setBalance($balance);

            $entityManager->flush();

            $saleSettlement->setCashDesk($userCashDesk);
        }
        elseif ($request->get('bankAccount') !== null && $request->get('bankAccount'))
        {
            // Write bank history
            $bankHistory = new BankHistory();

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('bankAccount'));
            $filterId = intval($filter);
            $bankAccount = $bankAccountRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $bankHistory->setBankAccount($bankAccount);
            $bankHistory->setReference($saleSettlement->getReference());
            $bankHistory->setDescription('invoice settlement');
            $bankHistory->setDebit($amount);
            $bankHistory->setCredit(0);
            // balance : en bas
            $bankHistory->setDateAt(new \DateTimeImmutable());

            //$bankHistory->setBranch($this->getUser()->getBranch());
            $bankHistory->setInstitution($this->getUser()->getInstitution());
            $bankHistory->setYear($this->getUser()->getCurrentYear());
            $bankHistory->setUser($this->getUser());
            $entityManager->persist($bankHistory);

            // Update bank balance
            $bankHistories = $bankHistoryRepository->findBy(['bankAccount' => $bankAccount]);

            $debit = $amount; $credit = 0;

            foreach ($bankHistories as $item)
            {
                $debit += $item->getDebit();
                $credit += $item->getCredit();
            }

            $balance = $debit - $credit;

            $bankHistory->setBalance($balance);
            $bankAccount->setBalance($balance);
            $entityManager->flush();

            $saleSettlement->setBankAccount($bankAccount);
        }

        $studentRegistrationHistory = new StudentRegistrationHistory();
        $studentRegistrationHistory->setStudentRegistration($saleSettlement->getStudentRegistration());
        $studentRegistrationHistory->setReference($saleSettlement->getReference());
        $studentRegistrationHistory->setUser($this->getUser());
        $studentRegistrationHistory->setInstitution($this->getUser()->getInstitution());
        $studentRegistrationHistory->setYear($this->getUser()->getCurrentYear());

        // Update customer history balance
        $studentRegistrationHistories = $studentRegistrationHistoryRepository->findBy(['studentRegistration' => $saleSettlement->getStudentRegistration()]);

        $debit = 0; $credit = $amount;

        foreach ($studentRegistrationHistories as $item)
        {
            $debit += $item->getDebit();
            $credit += $item->getCredit();
        }

        $balance = $credit - $debit  ;

        $studentRegistrationHistory->setBalance($balance);
        $studentRegistrationHistory->setCredit($amount);
        $studentRegistrationHistory->setDebit(0);
        $studentRegistrationHistory->setDescription('settlement ' . $saleSettlement->getNote());
        $entityManager->persist($studentRegistrationHistory);


        $saleSettlement->getInvoice()?->setAmountPaid($saleSettlement->getInvoice()->getAmountPaid() + $saleSettlement->getAmountPay());
        $saleSettlement->getInvoice()?->setBalance($saleSettlement->getInvoice()->getTtc() - $saleSettlement->getInvoice()->getAmountPaid());

        // upload the file and save its filename
        if ($uploadedFile){
            $saleSettlement->setPicture($fileUploader->upload($uploadedFile));
            $saleSettlement->setFileName($request->get('fileName'));
            $saleSettlement->setFileType($request->get('fileType'));
            $saleSettlement->setFileSize($request->get('fileSize'));
        }

        $saleSettlement->setIsValidate(true);
        $saleSettlement->setValidateAt(new \DateTimeImmutable());
        $saleSettlement->setValidateBy($this->getUser());

        // $saleSettlement->getCustomer()->setCredit($saleSettlement->getCustomer()->getCredit() + $amount);


        // Verifier si le montant a regler est inferieur au panier
        $settlementAmount = $amount;

        // $saleInvoiceFees form $saleInvoice;
        $saleInvoiceFees = $saleInvoiceFeeRepository->findSaleInvoiceFeeByPositionASC($invoice);

        foreach ($saleInvoiceFees as $saleInvoiceFee)
        {
            $amount = $saleInvoiceFee->getAmountTtc();
            $amountPaid = $saleInvoiceFee->getAmountPaid();

            $balance = $amount - $amountPaid;

            // check if $balance is less than $settlementAmount
            if ($balance < $settlementAmount)
            {
                // set amount Paid equal to amount Paid + $balance
                $saleInvoiceFee->setAmountPaid($amountPaid + $balance);

                // set balance to 0 because it is settle
                $saleInvoiceFee->setBalance(0);

                // set is Paid = true
                $saleInvoiceFee->setIsTreat(true);

                $settlementAmount = $settlementAmount - $balance;

                // amount = $balance
                if($saleInvoiceFee->getFee()->getBudgetLine())
                {
                    // Find corresponding budget
                    // budget line = $saleInvoiceItem->getItem()->getFee()->getBudgetLine()
                    // budget exercise ?
                }
            }
            elseif ($balance > $settlementAmount)
            {
                // check if $balance is greater than $settlementAmount

                // set amount Paid equal to amount Paid + $settlementAmount
                $saleInvoiceFee->setAmountPaid($amountPaid + $settlementAmount);
                $saleInvoiceFee->setBalance($balance - $settlementAmount);

                // set is Paid = false
                $saleInvoiceFee->setIsTreat(false);

                $settlementAmount = 0;
                // break;

                // amount = $settlementAmount
            }
            elseif ($balance == $settlementAmount)
            {
                // check if $balance is equal to $settlementAmount

                // set amount Paid equal to amount Paid + $balance
                $saleInvoiceFee->setAmountPaid($amountPaid + $balance);

                // set balance to 0 because it is settle
                $saleInvoiceFee->setBalance(0);

                // set is Paid = true
                $saleInvoiceFee->setIsTreat(true);

                $settlementAmount = 0;
                // break;

                // amount = $balance
            }

        }

        $saleSettlement->setIsTreat(true);

        $entityManager->flush();

        return $this->json(['hydra:member' => $saleSettlement]);
    }

    /*public function budgetTransaction(Budget $budget, EntityManagerInterface $entityManager, $amount)
    {
        $budgetTransaction = new Needs();

        $budgetTransaction->setReference('123');
        $budgetTransaction->setBudget($budget);
        $budgetTransaction->setExercise($budget->getExercise());
        $budgetTransaction->setValidatedAmount($amount);
        $budgetTransaction->setReason('Income');

        $budgetTransaction->setInstitution($this->getUser()->getInstitution());
        $budgetTransaction->setUser($this->getUser());
        $budgetTransaction->setYear($this->getUser()->getCurrentYear());

        $entityManager->flush();
    }*/

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

    function generer_numero_unique() {
        // Génère un nombre aléatoire entre 10000 et 99999 (inclus)
        $numero_unique = rand(10000, 99999);
        return $numero_unique;
    }

}
