<?php

namespace App\Controller\Billing\Purchase\Delivery;

use App\Entity\Billing\Purchase\PurchaseReturnInvoice;
use App\Entity\Billing\Purchase\PurchaseSettlement;
use App\Entity\Inventory\Delivery;
use App\Entity\Inventory\DeliveryItem;
use App\Entity\Inventory\DeliveryItemStock;
use App\Entity\Inventory\StockMovement;
use App\Entity\Partner\SupplierHistory;
use App\Entity\Security\User;
use App\Entity\Treasury\BankHistory;
use App\Entity\Treasury\CashDeskHistory;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemStockRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceRepository;
use App\Repository\Billing\Purchase\PurchaseSettlementRepository;
use App\Repository\Inventory\DeliveryItemRepository;
use App\Repository\Inventory\DeliveryRepository;
use App\Repository\Inventory\StockMovementRepository;
use App\Repository\Inventory\StockRepository;
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
class CreatePurchaseReturnInvoiceDeliverySettlementStockOutValidateController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(PurchaseReturnInvoiceItemRepository $purchaseReturnInvoiceItemRepository,
                             PurchaseReturnInvoiceRepository $purchaseReturnInvoiceRepository,
                             DeliveryRepository $deliveryRepository,
                             DeliveryItemRepository $deliveryItemRepository,
                             StockRepository $stockRepository,
                             PurchaseReturnInvoiceItemStockRepository $purchaseReturnInvoiceItemStockRepository,
                             PaymentMethodRepository $paymentMethodRepository,
                             CashDeskRepository $cashDeskRepository,
                             BankRepository $bankRepository,
                             BankHistoryRepository $bankHistoryRepository,
                             PaymentGatewayRepository $paymentGatewayRepository,
                             PurchaseSettlementRepository $purchaseSettlementRepository,
                             CashDeskHistoryRepository $cashDeskHistoryRepository,
                             BankAccountRepository $bankAccountRepository,
                             SupplierHistoryRepository $supplierHistoryRepository,
                             StockMovementRepository $stockMovementRepository,
                             FileUploader $fileUploader,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {
        $id = $request->get('id');

        $purchaseReturnInvoice = $purchaseReturnInvoiceRepository->find($id);
        if(!($purchaseReturnInvoice instanceof PurchaseReturnInvoice))
        {
            return new JsonResponse(['hydra:title' => 'This data must be type of purchase return invoice.'], 404);
        }

        $existingReference = $deliveryRepository->findOneBy(['otherReference' => $purchaseReturnInvoice->getInvoiceNumber()]);
        if ($existingReference){
            return new JsonResponse(['hydra:title' => 'This purchase return invoice already has a delivery.'], 500);
        }

        $uploadedFile = $request->files->get('file');

        if($purchaseReturnInvoice->getVirtualBalance() <= 0){
            return new JsonResponse(['hydra:title' => 'Amount can not be less or equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }
        $amount = $purchaseReturnInvoice->getVirtualBalance();

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
        elseif ($request->get('bankAccount') !== null && $request->get('bankAccount')) {
            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('bankAccount'));
            $filterId = intval($filter);
            $bankAccount = $bankAccountRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            if (!$bankAccount){
                return new JsonResponse(['hydra:description' => 'Bank account not found !'], 404);
            }
        }
        // END: Filter the uri to just take the id and pass it to our object



        // SETTLEMENT SECTION
        $purchaseSettlement = new PurchaseSettlement();

        // set settlement
        $purchaseSettlement->setPurchaseReturnInvoice($purchaseReturnInvoice);
        $purchaseSettlement->setSupplier($purchaseReturnInvoice->getSupplier());

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

        // Update invoice amount paid - balance
        $purchaseReturnInvoice->setAmountPaid($amount);
        $purchaseReturnInvoice->setBalance(0);
        $purchaseReturnInvoice->setVirtualBalance(0);

        $purchaseReturnInvoice->getSupplier()->setCredit($purchaseSettlement->getSupplier()->getCredit() + $amount);

        // SETTLEMENT SECTION END



        // DELIVERY SECTION

        $generateDeliveryUniqNumber = $deliveryRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$generateDeliveryUniqNumber){
            $uniqueNumber = 'WH/DEL/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $generateDeliveryUniqNumber->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'WH/DEL/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $delivery = new Delivery();

        $delivery->setPurchaseReturnInvoice($purchaseReturnInvoice);
        $delivery->setContact($purchaseReturnInvoice->getSupplier()->getContact());
        // shipping address
        // operation type
        // original document
        $delivery->setReference($uniqueNumber);
        $delivery->setOtherReference($purchaseReturnInvoice->getInvoiceNumber());
        // serial number
        $delivery->setDeliveryAt(new \DateTimeImmutable());
        $delivery->setDescription('purchase return invoice delivery validate');
        $delivery->setIsValidate(true);
        $delivery->setValidateAt(new \DateTimeImmutable());
        $delivery->setValidateBy($this->getUser());
        $delivery->setStatus('delivery');

        $delivery->setIsEnable(true);
        $delivery->setCreatedAt(new \DateTimeImmutable());
        $delivery->setYear($this->getUser()->getCurrentYear());
        $delivery->setUser($this->getUser());
        $delivery->setBranch($this->getUser()->getBranch());
        $delivery->setInstitution($this->getUser()->getInstitution());

        $entityManager->persist($delivery);

        $purchaseReturnInvoiceItems = $purchaseReturnInvoiceItemRepository->findBy(['purchaseReturnInvoice' => $purchaseReturnInvoice]);
        if ($purchaseReturnInvoiceItems)
        {
            foreach ($purchaseReturnInvoiceItems as $purchaseReturnInvoiceItem)
            {
                $deliveryItem = new DeliveryItem();

                $deliveryItem->setDelivery($delivery);
                $deliveryItem->setItem($purchaseReturnInvoiceItem->getItem());
                $deliveryItem->setQuantity($purchaseReturnInvoiceItem->getQuantity());

                $deliveryItem->setIsEnable(true);
                $deliveryItem->setCreatedAt(new \DateTimeImmutable());
                $deliveryItem->setYear($this->getUser()->getCurrentYear());
                $deliveryItem->setUser($this->getUser());
                $deliveryItem->setInstitution($this->getUser()->getInstitution());

                $this->manager->persist($deliveryItem);

                // Faire la sortie de stock
                $purchaseReturnInvoiceItemStocks = $purchaseReturnInvoiceItemStockRepository->findBy(['purchaseReturnInvoiceItem' => $purchaseReturnInvoiceItem]);

                if ($purchaseReturnInvoiceItemStocks)
                {
                    foreach ($purchaseReturnInvoiceItemStocks as $purchaseReturnInvoiceItemStock)
                    {
                        // create delivery item stock
                        $deliveryItemStock = new DeliveryItemStock();

                        $deliveryItemStock->setPurchaseReturnInvoiceItem($purchaseReturnInvoiceItem);
                        $deliveryItemStock->setDeliveryItem($deliveryItem);
                        $deliveryItemStock->setStock($purchaseReturnInvoiceItemStock->getStock());
                        $deliveryItemStock->setQuantity($purchaseReturnInvoiceItemStock->getQuantity());

                        $deliveryItemStock->setCreatedAt(new \DateTimeImmutable());
                        $deliveryItemStock->setUser($this->getUser());
                        $deliveryItemStock->setYear($this->getUser()->getCurrentYear());
                        $deliveryItemStock->setBranch($this->getUser()->getBranch());
                        $deliveryItemStock->setInstitution($this->getUser()->getInstitution());

                        $this->manager->persist($deliveryItemStock);
                        // create delivery item stock end


                        $stock = $purchaseReturnInvoiceItemStock->getStock();

                        $stock->setAvailableQte($stock->getAvailableQte() - $purchaseReturnInvoiceItemStock->getQuantity());
                        $stock->setQuantity($stock->getQuantity() - $purchaseReturnInvoiceItemStock->getQuantity());

                        // Stock movement
                        $stockMovement = new StockMovement();

                        $stockOutRef = $stockMovementRepository->findOneBy(['isOut' => true, 'branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
                        if (!$stockOutRef){
                            $reference = 'WH/OUT/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
                        }
                        else{
                            $filterNumber = preg_replace("/[^0-9]/", '', $stockOutRef->getReference());
                            $number = intval($filterNumber);

                            // Utilisation de number_format() pour ajouter des zéros à gauche
                            $reference = 'WH/OUT/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
                        }

                        $stockMovement->setReference($reference);
                        $stockMovement->setItem($stock->getItem());
                        $stockMovement->setQuantity($purchaseReturnInvoiceItemStock->getQuantity());
                        $stockMovement->setUnitCost($stock->getUnitCost());
                        $stockMovement->setFromWarehouse($stock->getWarehouse());
                        // from location
                        // to warehouse
                        // to location
                        $stockMovement->setStockAt(new \DateTimeImmutable());
                        $stockMovement->setLoseAt($stock->getLoseAt());
                        $stockMovement->setNote('purchase return invoice complete stock out');
                        $stockMovement->setType('purchase return invoice complete stock out');
                        $stockMovement->setIsOut(true);
                        $stockMovement->setStock($stock);

                        $stockMovement->setYear($this->getUser()->getCurrentYear());
                        $stockMovement->setUser($this->getUser());
                        $stockMovement->setCreatedAt(new \DateTimeImmutable());
                        $stockMovement->setIsEnable(true);
                        $stockMovement->setUpdatedAt(new \DateTimeImmutable());
                        $stockMovement->setBranch($this->getUser()->getBranch());
                        $stockMovement->setInstitution($this->getUser()->getInstitution());

                        $entityManager->persist($stockMovement);
                    }
                }

            }
        }

        // DELIVERY SECTION END

        // other invoice status update
        $purchaseReturnInvoice->setOtherStatus('stock out');

        $this->manager->flush();

        return $this->json(['hydra:member' => $delivery]);
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
