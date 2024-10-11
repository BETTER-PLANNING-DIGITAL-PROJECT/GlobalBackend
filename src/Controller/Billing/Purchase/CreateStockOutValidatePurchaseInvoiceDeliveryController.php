<?php

namespace App\Controller\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseInvoice;
use App\Entity\Inventory\Delivery;
use App\Entity\Inventory\DeliveryItem;
use App\Entity\Inventory\StockMovement;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemStockRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
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
class CreateStockOutValidatePurchaseInvoiceDeliveryController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly EntityManagerInterface $manager
    )
    {
    }

    public function __invoke(PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                             PurchaseInvoiceItemStockRepository $purchaseInvoiceItemStockRepository,
                             PurchaseInvoiceRepository $purchaseInvoiceRepository,
                             DeliveryRepository $deliveryRepository,
                             DeliveryItemRepository $deliveryItemRepository,
                             StockRepository $stockRepository,
                             StockMovementRepository $stockMovementRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $data = json_decode($request->getContent(), true);

        $invoice = $purchaseInvoiceRepository->find($id);

        if(!($invoice instanceof PurchaseInvoice))
        {
            // Warning
            return new JsonResponse(['hydra:title' => 'This data must be type of invoice.'], 404);
        }

        if(!$invoice)
        {
            // Warning
            return new JsonResponse(['hydra:title' => 'This invoice is not found.'], 404);
        }

        $existingReference = $deliveryRepository->findOneBy(['otherReference' => $invoice->getInvoiceNumber()]);
        if ($existingReference){
            return new JsonResponse(['hydra:title' => 'This feature already generated.'], 500);
        }

        $generateDeliveryUniqNumber = $deliveryRepository->findOneBy([], ['id' => 'DESC']);

        if (!$generateDeliveryUniqNumber){
            $uniqueNumber = 'PUR/DEL/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $generateDeliveryUniqNumber->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'PUR/DEL/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $delivery = new Delivery();
        //$delivery->setInvoice($invoice);
        $delivery->setContact($invoice->getSupplier()->getContact());
        $delivery->setReference($uniqueNumber);
        $delivery->setOtherReference($invoice->getInvoiceNumber());
        $delivery->setDeliveryAt(new \DateTimeImmutable());
        // description
        // validate
        $delivery->setIsValidate(true);
        $delivery->setValidateAt(new \DateTimeImmutable());
        $delivery->setValidateBy($this->getUser());
        $delivery->setStatus('delivery');

        $delivery->setIsEnable(true);
        $delivery->setCreatedAt(new \DateTimeImmutable());
        $delivery->setYear($this->getUser()->getCurrentYear());
        $delivery->setUser($this->getUser());
        $delivery->setInstitution($this->getUser()->getInstitution());

        // other invoice status update
        $invoice->setOtherStatus('delivery');

        $entityManager->persist($delivery);

        $purchaseInvoiceItems = $purchaseInvoiceItemRepository->findBy(['purchaseInvoice' => $invoice]);

        if ($purchaseInvoiceItems)
        {
            foreach ($purchaseInvoiceItems as $purchaseInvoiceItem)
            {
                $deliveryItem = new DeliveryItem();
                $deliveryItem->setDelivery($delivery);
                $deliveryItem->setItem($purchaseInvoiceItem->getItem());
                $deliveryItem->setQuantity($purchaseInvoiceItem->getQuantity());

                $deliveryItem->setIsEnable(true);
                $deliveryItem->setCreatedAt(new \DateTimeImmutable());
                $deliveryItem->setYear($this->getUser()->getCurrentYear());
                $deliveryItem->setUser($this->getUser());
                $deliveryItem->setInstitution($purchaseInvoiceItem->getInstitution());

                $this->manager->persist($deliveryItem);

                // Faire la sortie de stock
                $purchaseInvoiceItemStocks = $purchaseInvoiceItemStockRepository->findBy(['purchaseInvoiceItem' => $purchaseInvoiceItem]);

                if ($purchaseInvoiceItemStocks)
                {
                    foreach ($purchaseInvoiceItemStocks as $purchaseInvoiceItemStock)
                    {
                        $stock = $purchaseInvoiceItemStock->getStock();
                        $stock->setQuantity($stock->getQuantity() - $deliveryItem->getQuantity());
                        $stock->setAvailableQte($stock->getAvailableQte() - $deliveryItem->getQuantity());
                        // $stock->setReserveQte($deliveryItem->getQuantity());


                        // Stock movement section

                        // reference
                        $stockMovementRef = $stockMovementRepository->findOneBy(['isOut' => true], ['id' => 'DESC']);
                        if (!$stockMovementRef){
                            $reference = 'WH/IN/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
                        }
                        else{
                            $filterNumber = preg_replace("/[^0-9]/", '', $stockMovementRef->getReference());
                            $number = intval($filterNumber);

                            // Utilisation de number_format() pour ajouter des zéros à gauche
                            $reference = 'WH/IN/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
                        }

                        $stockMovement = new StockMovement();
                        $stockMovement->setReference($reference);
                        $stockMovement->setItem($deliveryItem->getItem());
                        $stockMovement->setQuantity($deliveryItem->getQuantity());
                        $stockMovement->setUnitCost($stock->getUnitCost());
                        $stockMovement->setFromWarehouse($stock->getWarehouse());
                        // from location
                        $stockMovement->setStockAt(new \DateTimeImmutable());
                        $stockMovement->setLoseAt($stock->getLoseAt());
                        $stockMovement->setNote($stock->getNote());
                        $stockMovement->setIsOut(true);

                        $stockMovement->setStock($stock);

                        $stockMovement->setYear($this->getUser()->getCurrentYear());
                        $stockMovement->setUser($this->getUser());
                        $stockMovement->setCreatedAt(new \DateTimeImmutable());
                        $stockMovement->setIsEnable(true);
                        $stockMovement->setUpdatedAt(new \DateTimeImmutable());
                        $stockMovement->setInstitution($this->getUser()->getInstitution());

                        $entityManager->persist($stockMovement);
                    }
                }
            }
        }

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
