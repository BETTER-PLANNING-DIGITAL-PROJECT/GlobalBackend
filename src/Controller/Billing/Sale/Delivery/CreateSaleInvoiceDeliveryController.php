<?php

namespace App\Controller\Billing\Sale\Delivery;

use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Inventory\Delivery;
use App\Entity\Inventory\DeliveryItem;
use App\Entity\Inventory\DeliveryItemStock;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Inventory\DeliveryItemRepository;
use App\Repository\Inventory\DeliveryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class CreateSaleInvoiceDeliveryController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             SaleInvoiceItemStockRepository $saleInvoiceItemStockRepository,
                             DeliveryRepository $deliveryRepository,
                             DeliveryItemRepository $deliveryItemRepository,
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
        $delivery->setDescription('sale invoice draft delivery');
        $delivery->setIsValidate(false);
        $delivery->setStatus('draft');
        $delivery->setOtherStatus('draft');

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
                $deliveryItem->setInstitution($saleInvoiceItem->getInstitution());

                $this->manager->persist($deliveryItem);

                // find sale invoice item stock
                $saleInvoiceItemStocks = $saleInvoiceItemStockRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);
                if($saleInvoiceItemStocks)
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
                    }
                }
                // find sale invoice item stock end
            }
        }

        // other invoice status update
        $saleInvoice->setOtherStatus('delivery');

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