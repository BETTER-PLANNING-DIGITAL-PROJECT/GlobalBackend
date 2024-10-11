<?php

namespace App\Controller\Billing\Purchase\Return;

use App\Entity\Billing\Purchase\PurchaseSettlement;
use App\Entity\Partner\SupplierHistory;
use App\Entity\Security\User;
use App\Entity\Treasury\BankHistory;
use App\Entity\Treasury\CashDeskHistory;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceRepository;
use App\Repository\Billing\Purchase\PurchaseSettlementRepository;
use App\Repository\Partner\SupplierHistoryRepository;
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
class CreatePurchaseReturnInvoiceSettlementValidateController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(PurchaseReturnInvoiceItemRepository $purchaseReturnInvoiceItemRepository,
                             PurchaseReturnInvoiceRepository $purchaseReturnInvoiceRepository,
                             PurchaseSettlementRepository $purchaseSettlementRepository,
                             EntityManagerInterface $entityManager,
                             CashDeskRepository $cashDeskRepository,
                             CashDeskHistoryRepository $cashDeskHistoryRepository,
                             SupplierHistoryRepository $supplierHistoryRepository,
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

        $purchaseReturnInvoice = $purchaseReturnInvoiceRepository->find($id);
        if (!$purchaseReturnInvoice){
            return new JsonResponse(['hydra:description' => 'This purchase return invoice is not found.'], 404);
        }

        if($request->get('amountPay') <= 0 ){
            return new JsonResponse(['hydra:title' => 'Amount can not be less or equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }
        elseif ($request->get('amountPay') > $purchaseReturnInvoice->getVirtualBalance())
        {
            return new JsonResponse(['hydra:title' => 'Amount can not be more than balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if(($purchaseReturnInvoice->getTtc() > 0) && ($purchaseReturnInvoice->getBalance() == 0) && ($purchaseReturnInvoice->getTtc() == $purchaseReturnInvoice->getAmountPaid())){
            return new JsonResponse(['hydra:title' => 'Purchase return invoice already settle'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if (!$purchaseReturnInvoice->getSupplier())
        {
            return new JsonResponse(['hydra:title' => 'Supplier not found!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
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

        // CREATE SETTLEMENT SECTION
        $purchaseSettlement = new PurchaseSettlement();

        $purchaseSettlement->setPurchaseReturnInvoice($purchaseReturnInvoice);
        $purchaseSettlement->setSupplier($purchaseReturnInvoice->getSupplier());

        $generateSettlementUniqNumber = $purchaseSettlementRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$generateSettlementUniqNumber){
            $uniqueNumber = 'PUR/SET/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $generateSettlementUniqNumber->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'PUR/SET/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }
        $purchaseSettlement->setReference($uniqueNumber);

        $purchaseSettlement->setAmountPay($request->get('amountPay'));
        $purchaseSettlement->setAmountRest($purchaseReturnInvoice->getVirtualBalance() - $request->get('amountPay'));
        $purchaseSettlement->setSettleAt(new \DateTimeImmutable($request->get('settleAt')));

        if ($request->get('bank') !== null && $request->get('bank')){

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('bank'));
            $filterId = intval($filter);
            $bank = $bankRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $purchaseSettlement->setBank($bank);
        }

        $purchaseSettlement->setNote('purchase return invoice settlement validate');
        $purchaseSettlement->setPaymentMethod($paymentMethod);
        $purchaseSettlement->setStatus('settlement');

        $purchaseSettlement->setIsValidate(true);
        $purchaseSettlement->setValidateAt(new \DateTimeImmutable());
        $purchaseSettlement->setValidateBy($this->getUser());

        $purchaseSettlement->setUser($this->getUser());
        $purchaseSettlement->setYear($this->getUser()->getCurrentYear());
        $purchaseSettlement->setBranch($this->getUser()->getBranch());
        $purchaseSettlement->setInstitution($this->getUser()->getInstitution());

        // upload the file and save its filename
        if ($uploadedFile){
            $purchaseSettlement->setPicture($fileUploader->upload($uploadedFile));
            $purchaseSettlement->setFileName($request->get('fileName'));
            $purchaseSettlement->setFileType($request->get('fileType'));
            $purchaseSettlement->setFileSize($request->get('fileSize'));
        }

        // Persist settlement
        $entityManager->persist($purchaseSettlement);


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
            $cashDeskHistory->setDescription('purchase return invoice settlement cash history');
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

            $purchaseSettlement->setCashDesk($userCashDesk);
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
                $bankHistory->setDescription('purchase return invoice settlement bank account history');
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

                $purchaseSettlement->setBankAccount($bankAccount);
            }
        }

        $supplierHistory = new SupplierHistory();

        $supplierHistoryRef = $supplierHistoryRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$supplierHistoryRef){
            $reference = 'SUP/HIS/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $supplierHistoryRef->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $reference = 'SUP/HIS/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $supplierHistory->setSupplier($purchaseReturnInvoice->getSupplier());
        $supplierHistory->setReference($reference);
        $supplierHistory->setDescription('purchase return invoice settlement supplier history');
        $supplierHistory->setDebit(0);
        $supplierHistory->setCredit($amount);

        // Update supplier history balance
        $supplierHistories = $supplierHistoryRepository->findBy(['supplier' => $purchaseSettlement->getSupplier()]);

        $debit = 0; $credit = $amount;

        foreach ($supplierHistories as $item)
        {
            $debit += $item->getDebit();
            $credit += $item->getCredit();
        }

        $balance = $debit - $credit;

        $supplierHistory->setBalance($balance);

        $supplierHistory->setUser($this->getUser());
        $supplierHistory->setBranch($this->getUser()->getBranch());
        $supplierHistory->setInstitution($this->getUser()->getInstitution());
        $supplierHistory->setYear($this->getUser()->getCurrentYear());
        $entityManager->persist($supplierHistory);

        // update supplier
        $purchaseReturnInvoice->getSupplier()->setCredit($purchaseReturnInvoice->getSupplier()->getCredit() + $amount);

        // Update invoice
        $purchaseReturnInvoice?->setAmountPaid($purchaseSettlement->getPurchaseReturnInvoice()->getAmountPaid() + $purchaseSettlement->getAmountPay());
        $purchaseReturnInvoice?->setBalance($purchaseSettlement->getPurchaseReturnInvoice()->getTtc() - $purchaseSettlement->getPurchaseReturnInvoice()->getAmountPaid());
        $purchaseReturnInvoice->setVirtualBalance($purchaseReturnInvoice->getVirtualBalance() - $request->get('amountPay'));

        // other invoice status update
        $purchaseReturnInvoice->setOtherStatus('settlement');

        $entityManager->flush();

        return $this->json(['hydra:member' => $purchaseSettlement]);
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
