<?php

namespace App\Controller\Billing\Purchase\Reception;

use App\Entity\Billing\Purchase\PurchaseInvoice;
use App\Entity\Billing\Purchase\PurchaseInvoiceItemStock;
use App\Entity\Inventory\Reception;
use App\Entity\Inventory\ReceptionItem;
use App\Entity\Inventory\ReceptionItemStock;
use App\Entity\Inventory\Stock;
use App\Entity\Inventory\StockMovement;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Inventory\LocationRepository;
use App\Repository\Inventory\ReceptionRepository;
use App\Repository\Inventory\StockMovementRepository;
use App\Repository\Inventory\StockRepository;
use App\Repository\Inventory\WarehouseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class CreatePurchaseInvoiceStockInController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly EntityManagerInterface $manager
    )
    {
    }

    public function __invoke(PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                             PurchaseInvoiceRepository $purchaseInvoiceRepository,
                             StockRepository $stockRepository,
                             ReceptionRepository $receptionRepository,
                             WarehouseRepository $warehouseRepository,
                             StockMovementRepository $stockMovementRepository,
                             LocationRepository $locationRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $purchaseInvoice = $purchaseInvoiceRepository->find($id);

        if(!($purchaseInvoice instanceof PurchaseInvoice))
        {
            return new JsonResponse(['hydra:title' => 'This data must be type of purchase invoice.'], 404);
        }

        $existingDelivery = $receptionRepository->findOneBy(['purchaseInvoice' => $purchaseInvoice]);
        if ($existingDelivery){
            return new JsonResponse(['hydra:title' => 'This purchase invoice already has reception on it.'], 500);
        }

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

                if ($request->get('loseAt') !== null && !is_null($request->get('loseAt'))){
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
                $stockMovement->setType('purchase invoice stock in');
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

        $purchaseInvoice->setOtherStatus('stock in');
        $reception->setOtherStatus('stock in');

        $this->manager->flush();

        return $this->json(['hydra:member' => $purchaseInvoice]);
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
