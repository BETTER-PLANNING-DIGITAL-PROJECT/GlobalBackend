<?php

namespace App\Controller\Billing\Purchase\Delivery;

use App\Entity\Billing\Purchase\PurchaseReturnInvoice;
use App\Entity\Inventory\Delivery;
use App\Entity\Inventory\DeliveryItem;
use App\Entity\Inventory\DeliveryItemStock;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemStockRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceRepository;
use App\Repository\Inventory\DeliveryItemRepository;
use App\Repository\Inventory\DeliveryRepository;
use App\Repository\Inventory\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class CreatePurchaseReturnInvoiceDeliveryValidateController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(PurchaseReturnInvoiceItemRepository $purchaseReturnInvoiceItemRepository,
                             PurchaseReturnInvoiceItemStockRepository $purchaseReturnInvoiceItemStockRepository,
                             PurchaseReturnInvoiceRepository $purchaseReturnInvoiceRepository,
                             DeliveryRepository $deliveryRepository,
                             DeliveryItemRepository $deliveryItemRepository,
                             StockRepository $stockRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {
        $id = $request->get('id');

        $data = json_decode($request->getContent(), true);

        $purchaseReturnInvoice = $purchaseReturnInvoiceRepository->find($id);

        if(!($purchaseReturnInvoice instanceof PurchaseReturnInvoice))
        {
            return new JsonResponse(['hydra:title' => 'This data must be type of purchase return invoice.'], 404);
        }

        $existingDelivery = $deliveryRepository->findOneBy(['purchaseReturnInvoice' => $purchaseReturnInvoice]);
        if ($existingDelivery){
            return new JsonResponse(['hydra:title' => 'This purchase return invoice already has delivery on it.'], 500);
        }

        $generateDeliveryUniqNumber = $deliveryRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$generateDeliveryUniqNumber){
            $uniqueNumber = 'PUR/RET/DEL/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $generateDeliveryUniqNumber->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'PUR/RET/DEL/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
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

                // find purchase return invoice item stock
                $purchaseReturnInvoiceItemStocks = $purchaseReturnInvoiceItemStockRepository->findBy(['purchaseReturnInvoiceItem' => $purchaseReturnInvoiceItem]);
                if($purchaseReturnInvoiceItemStocks)
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
                    }
                }
                // find purchase invoice item stock end
            }
        }

        // other invoice status update
        $purchaseReturnInvoice->setOtherStatus('delivery');

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
