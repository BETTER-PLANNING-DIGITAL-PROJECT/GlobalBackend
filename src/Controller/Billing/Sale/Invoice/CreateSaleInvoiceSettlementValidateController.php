<?php

namespace App\Controller\Billing\Sale\Invoice;

use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Partner\CustomerHistory;
use App\Entity\Security\User;
use App\Entity\Treasury\BankHistory;
use App\Entity\Treasury\CashDeskHistory;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Partner\CustomerHistoryRepository;
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
class CreateSaleInvoiceSettlementValidateController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             SaleSettlementRepository $saleSettlementRepository,
                             EntityManagerInterface $entityManager,
                             CashDeskRepository $cashDeskRepository,
                             CashDeskHistoryRepository $cashDeskHistoryRepository,
                             CustomerHistoryRepository $customerHistoryRepository,
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

        $saleInvoice = $saleInvoiceRepository->find($id);
        if (!$saleInvoice){
            return new JsonResponse(['hydra:description' => 'Sale Invoice not found.'], 404);
        }

        if($request->get('amountPay') <= 0 ){
            return new JsonResponse(['hydra:title' => 'Amount can not be less or equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }
        elseif ($request->get('amountPay') > $saleInvoice->getVirtualBalance())
        {
            return new JsonResponse(['hydra:title' => 'Amount can not be more than balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if(($saleInvoice->getTtc() > 0) && ($saleInvoice->getBalance() == 0) && ($saleInvoice->getTtc() == $saleInvoice->getAmountPaid())){
            return new JsonResponse(['hydra:title' => 'Sale invoice already settle'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if (!$saleInvoice->getCustomer())
        {
            return new JsonResponse(['hydra:title' => 'Customer not found!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        // Payment Method
        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('paymentMethod'));
        $filterId = intval($filter);
        $paymentMethod = $paymentMethodRepository->find($filterId);

        if (!$paymentMethod){
            return new JsonResponse(['hydra:description' => 'Payment method not found !'], 404);
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
                return new JsonResponse(['hydra:title' => 'Your cash desk is not open!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }
        }
        elseif ($paymentMethod->isIsBank())
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
            }
        }
        // END: Filter the uri to just take the id and pass it to our object

        $saleSettlement = new SaleSettlement();

        $saleSettlement->setInvoice($saleInvoice);
        $saleSettlement->setCustomer($saleInvoice->getCustomer());

        $generateSettlementUniqNumber = $saleSettlementRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
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
        $saleSettlement->setAmountRest($saleInvoice->getVirtualBalance() - $request->get('amountPay'));
        $saleSettlement->setSettleAt(new \DateTimeImmutable($request->get('settleAt')));

        if ($request->get('bank') !== null && $request->get('bank')){

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('bank'));
            $filterId = intval($filter);
            $bank = $bankRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $saleSettlement->setBank($bank);
        }

        $saleSettlement->setNote('sale invoice settlement validate');
        $saleSettlement->setPaymentMethod($paymentMethod);
        $saleSettlement->setStatus('settlement');

        $saleSettlement->setIsValidate(true);
        $saleSettlement->setValidateAt(new \DateTimeImmutable());
        $saleSettlement->setValidateBy($this->getUser());

        $saleSettlement->setUser($this->getUser());
        $saleSettlement->setYear($this->getUser()->getCurrentYear());
        $saleSettlement->setBranch($this->getUser()->getBranch());
        $saleSettlement->setInstitution($this->getUser()->getInstitution());
        // $saleSettlement->setIsTreat(true);

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
            // Write cash desk history
            $cashDeskHistory = new CashDeskHistory();

            $cashDeskHistoryRef = $cashDeskHistoryRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
            if (!$cashDeskHistoryRef){
                $reference = 'CASH/HIS/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
            }
            else{
                $filterNumber = preg_replace("/[^0-9]/", '', $cashDeskHistoryRef->getReference());
                $number = intval($filterNumber);

                // Utilisation de number_format() pour ajouter des zéros à gauche
                $reference = 'CASH/HIS/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
            }

            $cashDeskHistory->setCashDesk($userCashDesk);
            $cashDeskHistory->setReference($reference);
            $cashDeskHistory->setDescription('sale invoice settlement cash history');
            $cashDeskHistory->setDebit($amount);
            $cashDeskHistory->setCredit(0);
            // balance : en bas
            $cashDeskHistory->setDateAt(new \DateTimeImmutable());

            $cashDeskHistory->setBranch($this->getUser()->getBranch());
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

            $saleSettlement->setCashDesk($userCashDesk);
        }
        elseif ($paymentMethod->isIsBank())
        {
            if ($request->get('bankAccount') !== null && $request->get('bankAccount'))
            {
                // Write bank history
                $bankHistory = new BankHistory();

                $bankHistoryRef = $bankHistoryRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
                if (!$bankHistoryRef){
                    $reference = 'BNK/HIS/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
                }
                else{
                    $filterNumber = preg_replace("/[^0-9]/", '', $bankHistoryRef->getReference());
                    $number = intval($filterNumber);

                    // Utilisation de number_format() pour ajouter des zéros à gauche
                    $reference = 'BNK/HIS/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
                }

                $bankHistory->setBankAccount($bankAccount);
                $bankHistory->setReference($reference);
                $bankHistory->setDescription('sale invoice settlement bank account history');
                $bankHistory->setDebit($amount);
                $bankHistory->setCredit(0);
                // balance : en bas
                $bankHistory->setDateAt(new \DateTimeImmutable());

                $bankHistory->setBranch($this->getUser()->getBranch());
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

                $saleSettlement->setBankAccount($bankAccount);
            }
        }

        $customerHistory = new CustomerHistory();

        $customerHistoryRef = $customerHistoryRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$customerHistoryRef){
            $reference = 'CLT/HIS/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $customerHistoryRef->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $reference = 'CLT/HIS/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $customerHistory->setCustomer($saleInvoice->getCustomer());
        $customerHistory->setReference($reference);
        $customerHistory->setDescription('sale invoice settlement customer history');
        $customerHistory->setDebit(0);
        $customerHistory->setCredit($amount);

        // Update customer history balance
        $customerHistories = $customerHistoryRepository->findBy(['customer' => $saleSettlement->getCustomer()]);

        $debit = 0; $credit = $amount;

        foreach ($customerHistories as $item)
        {
            $debit += $item->getDebit();
            $credit += $item->getCredit();
        }

        $balance = $debit - $credit;

        $customerHistory->setBalance($balance);

        $customerHistory->setUser($this->getUser());
        $customerHistory->setBranch($this->getUser()->getBranch());
        $customerHistory->setInstitution($this->getUser()->getInstitution());
        $customerHistory->setYear($this->getUser()->getCurrentYear());
        $entityManager->persist($customerHistory);

        // update customer
        $saleInvoice->getCustomer()->setCredit($saleInvoice->getCustomer()->getCredit() + $amount);

        // Update invoice
        $saleInvoice?->setAmountPaid($saleSettlement->getInvoice()->getAmountPaid() + $saleSettlement->getAmountPay());
        $saleInvoice?->setBalance($saleSettlement->getInvoice()->getTtc() - $saleSettlement->getInvoice()->getAmountPaid());
        $saleInvoice->setVirtualBalance($saleInvoice->getVirtualBalance() - $request->get('amountPay'));

        // other invoice status update
        $saleInvoice->setOtherStatus('settlement');

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

}
