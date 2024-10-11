<?php

namespace App\Controller\Billing\Sale\Delivery;

use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Inventory\Delivery;
use App\Entity\Inventory\DeliveryItem;
use App\Entity\Inventory\DeliveryItemStock;
use App\Entity\Inventory\StockMovement;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Inventory\DeliveryItemRepository;
use App\Repository\Inventory\DeliveryRepository;
use App\Repository\Inventory\StockMovementRepository;
use App\Repository\Inventory\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class CreateSaleInvoiceStockOutController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceItemStockRepository $saleInvoiceItemStockRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             DeliveryRepository $deliveryRepository,
                             DeliveryItemRepository $deliveryItemRepository,
                             StockRepository $stockRepository,
                             StockMovementRepository $stockMovementRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $data = json_decode($request->getContent(), true);

        $saleInvoice = $saleInvoiceRepository->find($id);

        if(!($saleInvoice instanceof SaleInvoice))
        {
            return new JsonResponse(['hydra:title' => 'This data must be type of sale invoice.'], 404);
        }

        $existingDelivery = $deliveryRepository->findOneBy(['saleInvoice' => $saleInvoice]);
        if ($existingDelivery){
            return new JsonResponse(['hydra:title' => 'This sale invoice already has delivery on it.'], 500);
        }

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
                        $stockMovement->setNote('sale invoice stock out');
                        $stockMovement->setType('sale invoice stock out');
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

        $saleInvoice->setOtherStatus('stock out');
        $delivery->setOtherStatus('stock out');

        $this->manager->flush();

        return $this->json(['hydra:member' => $saleInvoice]);
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
