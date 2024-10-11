<?php

namespace App\Controller\Billing\Sale\School\Return;

use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Partner\StudentRegistrationHistory;
use App\Entity\Security\User;
use App\Entity\Treasury\BankHistory;
use App\Entity\Treasury\CashDeskHistory;
use App\Repository\Billing\Sale\SaleReturnInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
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
class CreateSchoolSaleReturnInvoiceValidateController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleReturnInvoiceFeeRepository $saleReturnInvoiceFeeRepository,
                             SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                             SaleSettlementRepository $saleSettlementRepository,
                             EntityManagerInterface $entityManager,
                             CashDeskRepository $cashDeskRepository,
                             CashDeskHistoryRepository $cashDeskHistoryRepository,
                             CustomerHistoryRepository $customerHistoryRepository,
                             PaymentMethodRepository $paymentMethodRepository,
                             BankHistoryRepository $bankHistoryRepository,
                             BankAccountRepository $bankAccountRepository,
                             BankRepository $bankRepository,
                             PaymentGatewayRepository $paymentGatewayRepository,
                             StudentRegistrationHistoryRepository $studentRegistrationHistoryRepository,
                             FileUploader $fileUploader,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $uploadedFile = $request->files->get('file');

        $saleReturnInvoice = $saleReturnInvoiceRepository->find($id);
        if (!$saleReturnInvoice){
            return new JsonResponse(['hydra:description' => 'This return invoice is not found.'], 404);
        }

        $saleSettlement = new SaleSettlement();

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('paymentMethod'));
        $filterId = intval($filter);
        $paymentMethod = $paymentMethodRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

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

            if($request->get('amountPay') > $userCashDesk->getBalance()){
                return new JsonResponse(['hydra:title' => 'Insufficient balance in your treasury for this operation'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            $saleSettlement->setCashDesk($userCashDesk);

        }

        if ($paymentMethod->isIsBank())
        {
            if ($request->get('bankAccount') !== null && $request->get('bankAccount')) {
                // START: Filter the uri to just take the id and pass it to our object
                $filter = preg_replace("/[^0-9]/", '', $request->get('bankAccount'));
                $filterId = intval($filter);
                $bankAccount = $bankAccountRepository->find($filterId);
                // END: Filter the uri to just take the id and pass it to our object

                if (!$bankAccount){
                    return new JsonResponse(['hydra:description' => 'Bank account not found !'], 404);
                }

                if($request->get('amountPay') > $bankAccount->getBalance()){
                    return new JsonResponse(['hydra:title' => 'Insufficient balance in your treasury for this operation'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
                }

                $saleSettlement->setBankAccount($bankAccount);

                if ($request->get('bank') !== null && $request->get('bank')){
                    // START: Filter the uri to just take the id and pass it to our object
                    $filter = preg_replace("/[^0-9]/", '', $request->get('bank'));
                    $filterId = intval($filter);
                    $bank = $bankRepository->find($filterId);
                    // END: Filter the uri to just take the id and pass it to our object

                    $saleSettlement->setBank($bank);
                }
            }
        }

        if($request->get('amountPay') <= 0 ){
            return new JsonResponse(['hydra:title' => 'Amount can not be less or equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }


        if ($request->get('amountPay') > $saleReturnInvoice->getVirtualBalance())
        {
            return new JsonResponse(['hydra:title' => 'Amount can not be more than balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if (!$saleReturnInvoice->getStudentRegistration())
        {
            return new JsonResponse(['hydra:title' => 'Student not found!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        $sumSettlementPaid = $saleSettlementRepository->sumReturnSettlementValidatedAmountByInvoice($saleReturnInvoice)[0][1];
        if ($saleReturnInvoice->getSaleInvoice()->getAmountPaid() < $request->get('amountPay') + $sumSettlementPaid){
            return new JsonResponse(['hydra:title' => 'Amount to return cannot be more than the one paid : '. $saleReturnInvoice->getSaleInvoice()->getAmountPaid()], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        // CREATE SETTLEMENT SECTION

        $saleSettlement->setSaleReturnInvoice($saleReturnInvoice);
        $saleSettlement->setStudentRegistration($saleReturnInvoice->getStudentRegistration());

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
        $saleSettlement->setAmountRest($saleReturnInvoice->getVirtualBalance() - $request->get('amountPay'));

        $saleSettlement->setSettleAt(new \DateTimeImmutable($request->get('settleAt')));

        $saleSettlement->setPaymentMethod($paymentMethod);
        $saleSettlement->setNote('sale invoice settlement validate');

        $saleSettlement->setStudentRegistration($saleReturnInvoice->getStudentRegistration());
        $saleSettlement->setClass($saleReturnInvoice->getClass());
        $saleSettlement->setSchool($saleReturnInvoice->getSchool());

        $saleSettlement->setUser($this->getUser());
        $saleSettlement->setYear($this->getUser()->getCurrentYear());
        $saleSettlement->setBranch($this->getUser()->getBranch());
        $saleSettlement->setInstitution($this->getUser()->getInstitution());

        $saleSettlement->setIsValidate(true);
        $saleSettlement->setValidateAt(new \DateTimeImmutable());
        $saleSettlement->setValidateBy($this->getUser());

        // upload the file and save its filename
        if ($uploadedFile){
            $saleSettlement->setPicture($fileUploader->upload($uploadedFile));
            $saleSettlement->setFileName($request->get('fileName'));
            $saleSettlement->setFileType($request->get('fileType'));
            $saleSettlement->setFileSize($request->get('fileSize'));
        }

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
            $cashDeskHistory->setDescription('sale invoice settlement validate');
            $cashDeskHistory->setDebit(0);
            $cashDeskHistory->setCredit($amount);
            // balance : en bas
            $cashDeskHistory->setDateAt(new \DateTimeImmutable());

            $cashDeskHistory->setInstitution($this->getUser()->getInstitution());
            $cashDeskHistory->setUser($this->getUser());
            $cashDeskHistory->setYear($this->getUser()->getCurrentYear());
            $entityManager->persist($cashDeskHistory);

            // Update cash desk daily withdrawal balance
            $userCashDesk->setDailyWithdrawal($userCashDesk->getDailyWithdrawal() + $amount);

            // Update cash desk balance
            $cashDeskHistories = $cashDeskHistoryRepository->findBy(['cashDesk' => $userCashDesk]);

            $debit = 0; $credit = $amount;

            foreach ($cashDeskHistories as $item)
            {
                $debit += $item->getDebit();
                $credit += $item->getCredit();
            }

            $balance = $debit - $credit;

            $cashDeskHistory->setBalance($balance);
            $userCashDesk->setBalance($balance);

            $entityManager->flush();
        }
        elseif ($paymentMethod->isIsBank())
        {
            if ($request->get('bankAccount') !== null && $request->get('bankAccount'))
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
                $bankHistory->setDescription('sale invoice settlement validate');
                $bankHistory->setDebit(0);
                $bankHistory->setCredit($amount);
                // balance : en bas
                $bankHistory->setDateAt(new \DateTimeImmutable());

                $bankHistory->setInstitution($this->getUser()->getInstitution());
                $bankHistory->setYear($this->getUser()->getCurrentYear());
                $bankHistory->setUser($this->getUser());
                $entityManager->persist($bankHistory);

                // Update bank balance
                $bankHistories = $bankHistoryRepository->findBy(['bankAccount' => $bankAccount]);

                $debit = 0; $credit = $amount;

                foreach ($bankHistories as $item)
                {
                    $debit += $item->getDebit();
                    $credit += $item->getCredit();
                }

                $balance = $debit - $credit;

                $bankHistory->setBalance($balance);
                $bankAccount->setBalance($balance);
                $entityManager->flush();
            }
        }

        $studentRegistrationHistory = new StudentRegistrationHistory();
        $studentRegistrationHistory->setStudentRegistration($saleSettlement->getStudentRegistration());
        $studentRegistrationHistory->setReference($saleSettlement->getReference());
        $studentRegistrationHistory->setUser($this->getUser());
        $studentRegistrationHistory->setInstitution($this->getUser()->getInstitution());
        $studentRegistrationHistory->setYear($this->getUser()->getCurrentYear());

        // Update customer history balance
        $studentRegistrationHistories = $studentRegistrationHistoryRepository->findBy(['studentRegistration' => $saleSettlement->getStudentRegistration()]);

        $debit = $amount; $credit = 0;

        foreach ($studentRegistrationHistories as $item)
        {
            $debit += $item->getDebit();
            $credit += $item->getCredit();
        }

        $balance = $debit - $credit;

        $studentRegistrationHistory->setBalance($balance);
        $studentRegistrationHistory->setCredit(0);
        $studentRegistrationHistory->setDebit($amount);
        $studentRegistrationHistory->setDescription($saleSettlement->getNote(). '-'. 'SETTLEMENT');
        $entityManager->persist($studentRegistrationHistory);

        // $saleSettlement->getCustomer()->setDebit($saleSettlement->getCustomer()->getDebit() + $amount);

        // Update invoice
        $saleReturnInvoice?->setAmountPaid($saleSettlement->getSaleReturnInvoice()->getAmountPaid() + $saleSettlement->getAmountPay());
        $saleReturnInvoice?->setBalance($saleSettlement->getSaleReturnInvoice()->getTtc() - $saleSettlement->getSaleReturnInvoice()->getAmountPaid());
        $saleReturnInvoice->setVirtualBalance($saleReturnInvoice->getVirtualBalance() - $request->get('amountPay'));


        // Verifier si le montant a regler est inferieur au panier
        $settlementAmount = $amount;

        // $saleReturnInvoiceFees form $saleInvoice;
        $saleReturnInvoiceFees = $saleReturnInvoiceFeeRepository->findSaleReturnInvoiceFeeByPositionASC($saleReturnInvoice);

        foreach ($saleReturnInvoiceFees as $saleReturnInvoiceFee)
        {
            $amount = $saleReturnInvoiceFee->getAmountTtc();
            $amountPaid = $saleReturnInvoiceFee->getAmountPaid();

            $balance = $amount - $amountPaid;

            // check if $balance is less than $settlementAmount
            if ($balance < $settlementAmount)
            {
                // set amount Paid equal to amount Paid + $balance
                $saleReturnInvoiceFee->setAmountPaid($amountPaid + $balance);

                // set balance to 0 because it is settle
                $saleReturnInvoiceFee->setBalance(0);

                // set is Paid = true
                $saleReturnInvoiceFee->setIsTreat(true);

                $settlementAmount = $settlementAmount - $balance;

                /*// amount = $balance
                if($saleReturnInvoiceFee->getItem()->getFee()->getBudgetLine())
                {
                    // Find corresponding budget
                    // budget line = $saleReturnInvoiceFee->getItem()->getFee()->getBudgetLine()
                    // budget exercise ?
                }*/
            }
            elseif ($balance > $settlementAmount)
            {
                // check if $balance is greater than $settlementAmount

                // set amount Paid equal to amount Paid + $settlementAmount
                $saleReturnInvoiceFee->setAmountPaid($amountPaid + $settlementAmount);
                $saleReturnInvoiceFee->setBalance($balance - $settlementAmount);

                // set is Paid = false
                $saleReturnInvoiceFee->setIsTreat(false);

                $settlementAmount = 0;
                // break;

                // amount = $settlementAmount
            }
            elseif ($balance == $settlementAmount)
            {
                // check if $balance is equal to $settlementAmount

                // set amount Paid equal to amount Paid + $balance
                $saleReturnInvoiceFee->setAmountPaid($amountPaid + $balance);

                // set balance to 0 because it is settle
                $saleReturnInvoiceFee->setBalance(0);

                // set is Paid = true
                $saleReturnInvoiceFee->setIsTreat(true);

                $settlementAmount = 0;
                // break;

                // amount = $balance
            }

        }

        $saleSettlement->setIsTreat(true);
        $saleSettlement->setStatus('settlement');

        $entityManager->flush();


        return $this->json(['hydra:member' => $saleSettlement]);
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

    function generer_numero_unique() {
        // Génère un nombre aléatoire entre 10000 et 99999 (inclus)
        $numero_unique = rand(10000, 99999);
        return $numero_unique;
    }

}
