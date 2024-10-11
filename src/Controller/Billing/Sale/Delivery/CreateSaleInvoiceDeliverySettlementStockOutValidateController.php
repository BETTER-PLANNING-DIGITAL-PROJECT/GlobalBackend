<?php

namespace App\Controller\Billing\Sale\Delivery;

use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Inventory\Delivery;
use App\Entity\Inventory\DeliveryItem;
use App\Entity\Inventory\DeliveryItemStock;
use App\Entity\Inventory\StockMovement;
use App\Entity\Partner\CustomerHistory;
use App\Entity\Security\User;
use App\Entity\Treasury\BankHistory;
use App\Entity\Treasury\CashDeskHistory;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Inventory\DeliveryItemRepository;
use App\Repository\Inventory\DeliveryRepository;
use App\Repository\Inventory\StockMovementRepository;
use App\Repository\Inventory\StockRepository;
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
class CreateSaleInvoiceDeliverySettlementStockOutValidateController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             DeliveryRepository $deliveryRepository,
                             DeliveryItemRepository $deliveryItemRepository,
                             StockRepository $stockRepository,
                             StockMovementRepository $stockMovementRepository,
                             SaleInvoiceItemStockRepository $saleInvoiceItemStockRepository,
                             PaymentMethodRepository $paymentMethodRepository,
                             CashDeskRepository $cashDeskRepository,
                             BankRepository $bankRepository,
                             BankHistoryRepository $bankHistoryRepository,
                             PaymentGatewayRepository $paymentGatewayRepository,
                             SaleSettlementRepository $saleSettlementRepository,
                             CashDeskHistoryRepository $cashDeskHistoryRepository,
                             BankAccountRepository $bankAccountRepository,
                             CustomerHistoryRepository $customerHistoryRepository,
                             FileUploader $fileUploader,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {
        $id = $request->get('id');

        $saleInvoice = $saleInvoiceRepository->find($id);
        if(!($saleInvoice instanceof SaleInvoice))
        {
            return new JsonResponse(['hydra:title' => 'This data must be type of sale invoice.'], 404);
        }

        $existingReference = $deliveryRepository->findOneBy(['otherReference' => $saleInvoice->getInvoiceNumber(), 'branch' => $this->getUser()->getBranch()]);
        if ($existingReference){
            return new JsonResponse(['hydra:title' => 'This sale invoice already has a delivery.'], 500);
        }

        $uploadedFile = $request->files->get('file');

        if($saleInvoice->getVirtualBalance() <= 0){
            return new JsonResponse(['hydra:title' => 'Amount can not be less or equal to zero!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if(($saleInvoice->getBalance() == 0) && ($saleInvoice->getVirtualBalance() == 0)){
            return new JsonResponse(['hydra:title' => 'Sale invoice already settle'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        $amount = $saleInvoice->getVirtualBalance();

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



        // SETTLEMENT SECTION
        $saleSettlement = new SaleSettlement();

        // set settlement
        $saleSettlement->setInvoice($saleInvoice);
        $saleSettlement->setCustomer($saleInvoice->getCustomer());

        if ($request->get('reference') !== null){
            $saleSettlement->setReference($request->get('reference'));
        }
        else{
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
        }

        $saleSettlement->setAmountPay($amount);
        $saleSettlement->setAmountRest(0);
        $saleSettlement->setSettleAt(new \DateTimeImmutable());

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

        // upload the file and save its filename
        if ($uploadedFile){
            $saleSettlement->setPicture($fileUploader->upload($uploadedFile));
            $saleSettlement->setFileName($request->get('fileName'));
            $saleSettlement->setFileType($request->get('fileType'));
            $saleSettlement->setFileSize($request->get('fileSize'));
        }

        // Persist settlement
        $entityManager->persist($saleSettlement);

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

        // Write customer history
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

        $customerHistory->setCustomer($saleSettlement->getCustomer());
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

        // Update invoice amount paid - balance
        $saleInvoice->setAmountPaid($amount);
        $saleInvoice->setBalance(0);
        $saleInvoice->setVirtualBalance(0);

        $saleInvoice->getCustomer()->setCredit($saleSettlement->getCustomer()->getCredit() + $amount);

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

        $delivery->setSaleInvoice($saleInvoice);
        $delivery->setContact($saleInvoice->getCustomer()->getContact());
        // shipping address
        // operation type
        // original document
        $delivery->setReference($uniqueNumber);
        $delivery->setOtherReference($saleInvoice->getInvoiceNumber());
        $delivery->setOriginalDocument($saleInvoice->getInvoiceNumber());
        // serial number
        $delivery->setDeliveryAt(new \DateTimeImmutable());
        $delivery->setDescription('sale invoice delivery validate');
        $delivery->setIsValidate(true);
        $delivery->setValidateAt(new \DateTimeImmutable());
        $delivery->setValidateBy($this->getUser());
        $delivery->setStatus('delivery');
        $delivery->setOtherStatus('stock out');

        $delivery->setIsEnable(true);
        $delivery->setCreatedAt(new \DateTimeImmutable());
        $delivery->setYear($this->getUser()->getCurrentYear());
        $delivery->setUser($this->getUser());
        $delivery->setBranch($this->getUser()->getBranch());
        $delivery->setInstitution($this->getUser()->getInstitution());

        $entityManager->persist($delivery);

        $saleInvoiceItems = $saleInvoiceItemRepository->findBy(['saleInvoice' => $saleInvoice]);
        if ($saleInvoiceItems)
        {
            foreach ($saleInvoiceItems as $saleInvoiceItem)
            {
                $deliveryItem = new DeliveryItem();

                $deliveryItem->setDelivery($delivery);
                $deliveryItem->setItem($saleInvoiceItem->getItem());
                $deliveryItem->setQuantity($saleInvoiceItem->getQuantity());

                $deliveryItem->setIsEnable(true);
                $deliveryItem->setCreatedAt(new \DateTimeImmutable());
                $deliveryItem->setYear($this->getUser()->getCurrentYear());
                $deliveryItem->setUser($this->getUser());
                $deliveryItem->setInstitution($this->getUser()->getInstitution());

                $this->manager->persist($deliveryItem);

                // Faire la sortie de stock
                $saleInvoiceItemStocks = $saleInvoiceItemStockRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);

                if ($saleInvoiceItemStocks)
                {
                    foreach ($saleInvoiceItemStocks as $saleInvoiceItemStock)
                    {
                        // create delivery item stock
                        $deliveryItemStock = new DeliveryItemStock();

                        $deliveryItemStock->setSaleInvoiceItem($saleInvoiceItem);
                        $deliveryItemStock->setDeliveryItem($deliveryItem);
                        $deliveryItemStock->setStock($saleInvoiceItemStock->getStock());
                        $deliveryItemStock->setQuantity($saleInvoiceItemStock->getQuantity());

                        $deliveryItemStock->setCreatedAt(new \DateTimeImmutable());
                        $deliveryItemStock->setUser($this->getUser());
                        $deliveryItemStock->setYear($this->getUser()->getCurrentYear());
                        $deliveryItemStock->setBranch($this->getUser()->getBranch());
                        $deliveryItemStock->setInstitution($this->getUser()->getInstitution());

                        $this->manager->persist($deliveryItemStock);
                        // create delivery item stock end


                        $stock = $saleInvoiceItemStock->getStock();

                        $stock->setAvailableQte($stock->getAvailableQte() - $saleInvoiceItemStock->getQuantity());
                        $stock->setQuantity($stock->getQuantity() - $saleInvoiceItemStock->getQuantity());

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
                        $stockMovement->setQuantity($saleInvoiceItemStock->getQuantity());
                        $stockMovement->setUnitCost($stock->getUnitCost());
                        $stockMovement->setFromWarehouse($stock->getWarehouse());
                        // from location
                        // to warehouse
                        // to location
                        $stockMovement->setStockAt(new \DateTimeImmutable());
                        $stockMovement->setLoseAt($stock->getLoseAt());
                        $stockMovement->setNote('sale invoice complete stock out');
                        $stockMovement->setType('sale invoice complete stock out');
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
        $saleInvoice->setOtherStatus('stock out');

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
