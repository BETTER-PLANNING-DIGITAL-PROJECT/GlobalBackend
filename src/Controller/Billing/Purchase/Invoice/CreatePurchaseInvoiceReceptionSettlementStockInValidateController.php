<?php

namespace App\Controller\Billing\Purchase\Invoice;

use App\Entity\Billing\Purchase\PurchaseInvoice;
use App\Entity\Billing\Purchase\PurchaseInvoiceItemStock;
use App\Entity\Billing\Purchase\PurchaseSettlement;
use App\Entity\Inventory\Reception;
use App\Entity\Inventory\ReceptionItem;
use App\Entity\Inventory\ReceptionItemStock;
use App\Entity\Inventory\Stock;
use App\Entity\Inventory\StockMovement;
use App\Entity\Partner\SupplierHistory;
use App\Entity\Security\User;
use App\Entity\Treasury\BankHistory;
use App\Entity\Treasury\CashDeskHistory;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Billing\Purchase\PurchaseSettlementRepository;
use App\Repository\Inventory\LocationRepository;
use App\Repository\Inventory\ReceptionItemRepository;
use App\Repository\Inventory\ReceptionRepository;
use App\Repository\Inventory\StockMovementRepository;
use App\Repository\Inventory\StockRepository;
use App\Repository\Inventory\WarehouseRepository;
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
class CreatePurchaseInvoiceReceptionSettlementStockInValidateController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                             PurchaseInvoiceRepository $purchaseInvoiceRepository,
                             ReceptionRepository $receptionRepository,
                             ReceptionItemRepository $receptionItemRepository,
                             StockRepository $stockRepository,
                             PaymentMethodRepository $paymentMethodRepository,
                             CashDeskRepository $cashDeskRepository,
                             LocationRepository $locationRepository,
                             BankRepository $bankRepository,
                             BankHistoryRepository $bankHistoryRepository,
                             PaymentGatewayRepository $paymentGatewayRepository,
                             PurchaseSettlementRepository $purchaseSettlementRepository,
                             CashDeskHistoryRepository $cashDeskHistoryRepository,
                             BankAccountRepository $bankAccountRepository,
                             SupplierHistoryRepository $supplierHistoryRepository,
                             StockMovementRepository $stockMovementRepository,
                             WarehouseRepository $warehouseRepository,
                             FileUploader $fileUploader,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {
        $id = $request->get('id');

        $purchaseInvoice = $purchaseInvoiceRepository->find($id);
        if(!($purchaseInvoice instanceof PurchaseInvoice))
        {
            return new JsonResponse(['hydra:title' => 'This data must be type of purchase invoice.'], 404);
        }

        $existingReference = $receptionRepository->findOneBy(['otherReference' => $purchaseInvoice->getInvoiceNumber(), 'branch' => $this->getUser()->getBranch()]);
        if ($existingReference){
            return new JsonResponse(['hydra:title' => 'This purchase invoice already has a reception.'], 500);
        }

        $uploadedFile = $request->files->get('file');

        if($purchaseInvoice->getVirtualBalance() <= 0){
            return new JsonResponse(['hydra:title' => 'Amount can not be less or equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if(($purchaseInvoice->getBalance() == 0) && ($purchaseInvoice->getVirtualBalance() == 0)){
            return new JsonResponse(['hydra:title' => 'Purchase invoice already settle'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        $amount = $purchaseInvoice->getVirtualBalance();

        // Warehouse
        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('warehouse'));
        $filterId = intval($filter);
        $warehouse = $warehouseRepository->find($filterId);
        if (!$warehouse){
            return new JsonResponse(['hydra:description' => 'Warehouse not found !'], 404);
        }

        // Location

        if ($request->get('stockAt') === null){
            return new JsonResponse(['hydra:description' => 'Stock Date not found !'], 404);
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
                return new JsonResponse(['hydra:title' => 'You cash desk is not open!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            if ($purchaseInvoice->getVirtualBalance() > $userCashDesk->getBalance())
            {
                return new JsonResponse(['hydra:title' => 'Purchase Invoice Amount can not be more than your cash desk balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }
        }
        elseif ($request->get('bankAccount') !== null && $request->get('bankAccount')) {
            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('bankAccount'));
            $filterId = intval($filter);
            $bankAccount = $bankAccountRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            if (!$bankAccount){
                return new JsonResponse(['hydra:description' => 'Bank account not found !'], 404);
            }

            if ($purchaseInvoice->getVirtualBalance() > $bankAccount->getBalance())
            {
                return new JsonResponse(['hydra:title' => 'Purchase Invoice Amount can not be more than your bank balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }
        }
        // END: Filter the uri to just take the id and pass it to our object



        // SETTLEMENT SECTION
        $purchaseSettlement = new PurchaseSettlement();

        // set settlement
        $purchaseSettlement->setInvoice($purchaseInvoice);
        $purchaseSettlement->setSupplier($purchaseInvoice->getSupplier());

        if ($request->get('reference') !== null){
            $purchaseSettlement->setReference($request->get('reference'));
        }
        else{
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
        }

        $purchaseSettlement->setAmountPay($amount);
        $purchaseSettlement->setAmountRest(0);
        $purchaseSettlement->setSettleAt(new \DateTimeImmutable());

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

        // Validate Settlement
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

                $purchaseSettlement->setBankAccount($bankAccount);
            }
        }

        // Write supplier history
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

        $balance = $credit - $debit  ;

        $supplierHistory->setBalance($balance);

        $supplierHistory->setUser($this->getUser());
        $supplierHistory->setBranch($this->getUser()->getBranch());
        $supplierHistory->setInstitution($this->getUser()->getInstitution());
        $supplierHistory->setYear($this->getUser()->getCurrentYear());
        $entityManager->persist($supplierHistory);

        // Update invoice amount paid - balance
        $purchaseInvoice->setAmountPaid($amount);
        $purchaseInvoice->setBalance(0);
        $purchaseInvoice->setVirtualBalance(0);

        $purchaseInvoice->getSupplier()->setDebit($purchaseSettlement->getSupplier()->getDebit() + $amount);

        // SETTLEMENT SECTION END



        // RECEPTION SECTION

        $generateDeliveryUniqNumber = $receptionRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$generateDeliveryUniqNumber){
            $uniqueNumber = 'WH/REC/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $generateDeliveryUniqNumber->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'WH/REC/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $reception = new Reception();

        $reception->setPurchaseInvoice($purchaseInvoice);
        $reception->setContact($purchaseInvoice->getSupplier()->getContact());
        // shipping address
        // operation type
        // original document
        $reception->setReference($uniqueNumber);
        $reception->setOtherReference($purchaseInvoice->getInvoiceNumber());
        $reception->setOriginalDocument($purchaseInvoice->getInvoiceNumber());
        // serial number
        $reception->setReceiveAt(new \DateTimeImmutable());
        $reception->setDescription('purchase invoice reception validate');
        $reception->setIsValidate(true);
        $reception->setValidateAt(new \DateTimeImmutable());
        $reception->setValidateBy($this->getUser());
        $reception->setStatus('reception');
        $reception->setOtherStatus('stock in');

        $reception->setIsEnable(true);
        $reception->setCreatedAt(new \DateTimeImmutable());
        $reception->setYear($this->getUser()->getCurrentYear());
        $reception->setUser($this->getUser());
        $reception->setBranch($this->getUser()->getBranch());
        $reception->setInstitution($this->getUser()->getInstitution());

        $entityManager->persist($reception);

        $purchaseInvoiceItems = $purchaseInvoiceItemRepository->findBy(['purchaseInvoice' => $purchaseInvoice]);
        if ($purchaseInvoiceItems)
        {
            foreach ($purchaseInvoiceItems as $purchaseInvoiceItem)
            {
                $receptionItem = new ReceptionItem();

                $receptionItem->setReception($reception);
                $receptionItem->setItem($purchaseInvoiceItem->getItem());
                $receptionItem->setQuantity($purchaseInvoiceItem->getQuantity());

                $receptionItem->setIsEnable(true);
                $receptionItem->setCreatedAt(new \DateTimeImmutable());
                $receptionItem->setYear($this->getUser()->getCurrentYear());
                $receptionItem->setUser($this->getUser());
                $receptionItem->setInstitution($this->getUser()->getInstitution());

                $this->manager->persist($receptionItem);

                $stock = new Stock();

                $stockRef = $stockRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
                if (!$stockRef){
                    $reference = 'WH/ST/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
                }
                else{
                    $filterNumber = preg_replace("/[^0-9]/", '', $stockRef->getReference());
                    $number = intval($filterNumber);

                    // Utilisation de number_format() pour ajouter des zéros à gauche
                    $reference = 'WH/ST/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
                }

                $stock->setReference($reference);

                $stock->setItem($purchaseInvoiceItem->getItem());
                $stock->setQuantity($purchaseInvoiceItem->getQuantity());
                $stock->setAvailableQte($purchaseInvoiceItem->getQuantity());
                $stock->setReserveQte(0);

                // We will do calculation
                // avc
                // fifo
                $stock->setUnitCost($purchaseInvoiceItem->getPu());

                $stock->setWarehouse($warehouse);
                if ($request->get('location') !== null){
                    // START: Filter the uri to just take the id and pass it to our object
                    $filter = preg_replace("/[^0-9]/", '', $request->get('location'));
                    $filterId = intval($filter);
                    $location = $locationRepository->find($filterId);
                    $stock->setLocation($location);
                    // END: Filter the uri to just take the id and pass it to our object
                }

                if ($request->get('stockAt') !== null){
                    $stock->setStockAt(new \DateTimeImmutable($request->get('stockAt')));
                }

                if ($request->get('loseAt') !== null){
                    $stock->setLoseAt(new \DateTimeImmutable($request->get('loseAt')));
                }

                $stock->setNote($request->get('note'));

                $stock->setBranch($this->getUser()->getBranch());
                $stock->setUser($this->getUser());
                $stock->setInstitution($this->getUser()->getInstitution());
                $stock->setYear($this->getUser()->getCurrentYear());

                $entityManager->persist($stock);


                // Stock movement
                $stockMovement = new StockMovement();

                // reference
                $stockMovementRef = $stockMovementRepository->findOneBy(['isOut' => false, 'branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
                if (!$stockMovementRef){
                    $reference = 'WH/IN/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
                }
                else{
                    $filterNumber = preg_replace("/[^0-9]/", '', $stockMovementRef->getReference());
                    $number = intval($filterNumber);

                    // Utilisation de number_format() pour ajouter des zéros à gauche
                    $reference = 'WH/IN/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
                }

                $stockMovement->setReference($reference);
                $stockMovement->setItem($purchaseInvoiceItem->getItem());
                $stockMovement->setQuantity($purchaseInvoiceItem->getQuantity());
                // We will do calculation here
                // avc
                // fifo
                $stockMovement->setUnitCost($purchaseInvoiceItem->getPu());
                $stockMovement->setToWarehouse($warehouse);
                // to location
                // from warehouse
                // from location

                if ($request->get('stockAt') !== null){
                    $stockMovement->setStockAt(new \DateTimeImmutable($request->get('stockAt')));
                }

                if ($request->get('loseAt') !== null){
                    $stockMovement->setLoseAt(new \DateTimeImmutable($request->get('loseAt')));
                }

                $stockMovement->setNote($request->get('note'));
                $stockMovement->setType('purchase invoice complete stock in');
                $stockMovement->setIsOut(false);
                $stockMovement->setStock($stock);

                $stockMovement->setYear($this->getUser()->getCurrentYear());
                $stockMovement->setUser($this->getUser());
                $stockMovement->setCreatedAt(new \DateTimeImmutable());
                $stockMovement->setIsEnable(true);
                $stockMovement->setUpdatedAt(new \DateTimeImmutable());
                $stockMovement->setBranch($this->getUser()->getBranch());
                $stockMovement->setInstitution($this->getUser()->getInstitution());

                $entityManager->persist($stockMovement);

                // purchase invoice item stock
                $purchaseInvoiceItemStock = new PurchaseInvoiceItemStock();

                $purchaseInvoiceItemStock->setPurchaseInvoiceItem($purchaseInvoiceItem);
                $purchaseInvoiceItemStock->setStock($stock);
                $purchaseInvoiceItemStock->setQuantity($purchaseInvoiceItem->getQuantity());

                $purchaseInvoiceItemStock->setUser($this->getUser());
                $purchaseInvoiceItemStock->setInstitution($this->getUser()->getInstitution());
                $purchaseInvoiceItemStock->setYear($this->getUser()->getCurrentYear());
                $purchaseInvoiceItemStock->setBranch($this->getUser()->getBranch());

                $entityManager->persist($purchaseInvoiceItemStock);

                // create reception item stock
                $receptionItemStock = new ReceptionItemStock();

                $receptionItemStock->setReceptionItem($receptionItem);
                // $receptionItemStock->setDeliveryItem($receptionItem);
                $receptionItemStock->setStock($stock);
                $receptionItemStock->setQuantity($purchaseInvoiceItem->getQuantity());

                $receptionItemStock->setCreatedAt(new \DateTimeImmutable());
                $receptionItemStock->setUser($this->getUser());
                $receptionItemStock->setYear($this->getUser()->getCurrentYear());
                $receptionItemStock->setBranch($this->getUser()->getBranch());
                $receptionItemStock->setInstitution($this->getUser()->getInstitution());

                $this->manager->persist($receptionItemStock);
                // create reception item stock end
            }
        }

        // Reception SECTION END

        // other invoice status update
        $purchaseInvoice->setOtherStatus('stock in');
        $reception->setOtherStatus('stock in');

        $this->manager->flush();

        return $this->json(['hydra:member' => $reception]);
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
