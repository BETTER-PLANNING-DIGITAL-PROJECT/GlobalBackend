<?php

namespace App\Controller\Billing\Purchase\Invoice;

use App\Entity\Billing\Purchase\PurchaseSettlement;
use App\Entity\Partner\SupplierHistory;
use App\Entity\Security\User;
use App\Entity\Treasury\BankHistory;
use App\Entity\Treasury\CashDeskHistory;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Billing\Purchase\PurchaseSettlementRepository;
use App\Repository\Partner\CustomerHistoryRepository;
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
class CreatePurchaseInvoiceSettlementValidateController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                             PurchaseInvoiceRepository $purchaseInvoiceRepository,
                             PurchaseSettlementRepository $purchaseSettlementRepository,
                             EntityManagerInterface $entityManager,
                             CashDeskRepository $cashDeskRepository,
                             CashDeskHistoryRepository $cashDeskHistoryRepository,
                             SupplierHistoryRepository $supplierHistoryRepository,
                             PaymentMethodRepository $paymentMethodRepository,
                             BankHistoryRepository $bankHistoryRepository,
                             BankAccountRepository $bankAccountRepository,
                             BankRepository $bankRepository,
                             CustomerHistoryRepository $customerHistoryRepository,
                             FileUploader $fileUploader,
                             PaymentGatewayRepository $paymentGatewayRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $uploadedFile = $request->files->get('file');

        $purchaseInvoice = $purchaseInvoiceRepository->find($id);
        if (!$purchaseInvoice){
            return new JsonResponse(['hydra:description' => 'Purchase Invoice not found.'], 404);
        }

        if($request->get('amountPay') <= 0 ){
            return new JsonResponse(['hydra:title' => 'Amount can not be less or equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }
        elseif ($request->get('amountPay') > $purchaseInvoice->getVirtualBalance())
        {
            return new JsonResponse(['hydra:title' => 'Amount can not be more than balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if(($purchaseInvoice->getTtc() > 0) && ($purchaseInvoice->getBalance() == 0) && ($purchaseInvoice->getTtc() == $purchaseInvoice->getAmountPaid())){
            return new JsonResponse(['hydra:title' => 'Purchase invoice already settle'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if (!$purchaseInvoice->getSupplier())
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

            if ($request->get('amountPay') > $userCashDesk->getBalance())
            {
                return new JsonResponse(['hydra:title' => 'Purchase Invoice Amount can not be more than your cash desk balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
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

                if ($request->get('amountPay') > $bankAccount->getBalance())
                {
                    return new JsonResponse(['hydra:title' => 'Purchase Invoice Amount can not be more than your bank balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
                }
            }
        }
        // END: Filter the uri to just take the id and pass it to our object

        $purchaseSettlement = new PurchaseSettlement();

        $purchaseSettlement->setInvoice($purchaseInvoice);
        $purchaseSettlement->setSupplier($purchaseInvoice->getSupplier());

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
        $purchaseSettlement->setAmountRest($purchaseInvoice->getVirtualBalance() - $request->get('amountPay'));
        $purchaseSettlement->setSettleAt(new \DateTimeImmutable($request->get('settleAt')));

        if ($request->get('bank') !== null && $request->get('bank')){

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('bank'));
            $filterId = intval($filter);
            $bank = $bankRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $purchaseSettlement->setBank($bank);
        }

        $purchaseSettlement->setNote('purchase invoice settlement validate');
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
            $cashDeskHistory->setDescription('purchase invoice settlement cash history');
            $cashDeskHistory->setDebit(0);
            $cashDeskHistory->setCredit($amount);
            // balance : en bas
            $cashDeskHistory->setDateAt(new \DateTimeImmutable());

            $cashDeskHistory->setBranch($this->getUser()->getBranch());
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
                $bankHistory->setDescription('purchase invoice settlement bank account history');
                $bankHistory->setDebit(0);
                $bankHistory->setCredit($amount);
                // balance : en bas
                $bankHistory->setDateAt(new \DateTimeImmutable());

                $bankHistory->setBranch($this->getUser()->getBranch());
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

        $supplierHistory = new SupplierHistory();

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

        $supplierHistory->setSupplier($purchaseSettlement->getSupplier());
        $supplierHistory->setReference($reference);
        $supplierHistory->setDescription('purchase invoice settlement supplier history');
        $supplierHistory->setDebit($amount);
        $supplierHistory->setCredit(0);

        // Update supplier history balance
        $supplierHistories = $supplierHistoryRepository->findBy(['supplier' => $purchaseSettlement->getSupplier()]);

        $debit = $amount; $credit = 0;

        foreach ($supplierHistories as $item)
        {
            $debit += $item->getDebit();
            $credit += $item->getCredit();
        }

        $balance = $credit - $debit;

        $supplierHistory->setBalance($balance);

        $supplierHistory->setUser($this->getUser());
        $supplierHistory->setBranch($this->getUser()->getBranch());
        $supplierHistory->setInstitution($this->getUser()->getInstitution());
        $supplierHistory->setYear($this->getUser()->getCurrentYear());
        $entityManager->persist($supplierHistory);

        // Update supplier
        $purchaseSettlement->getSupplier()->setDebit($purchaseSettlement->getSupplier()->getDebit() + $amount);

        // Update invoice
        $purchaseInvoice?->setAmountPaid($purchaseSettlement->getInvoice()->getAmountPaid() + $purchaseSettlement->getAmountPay());
        $purchaseInvoice?->setBalance($purchaseSettlement->getInvoice()->getTtc() - $purchaseSettlement->getInvoice()->getAmountPaid());
        $purchaseInvoice->setVirtualBalance($purchaseInvoice->getVirtualBalance() - $request->get('amountPay'));

        // other invoice status update
        $purchaseInvoice->setOtherStatus('settlement');

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
