<?php

namespace App\Controller\Inventory\Delivery;

use App\Entity\Security\User;
use App\Repository\Inventory\DeliveryItemRepository;
use App\Repository\Inventory\DeliveryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetDeliveryItemController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                )
    {
    }

    public function __invoke(DeliveryItemRepository $deliveryItemRepository,
                             DeliveryRepository $deliveryRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $delivery = $deliveryRepository->find($id);
        if (!$delivery){
            return new JsonResponse(['hydra:description' => 'This delivery is not found.'], 404);
        }

        $deliveryItems = $deliveryItemRepository->findBy(['delivery' => $delivery], ['id' => 'DESC']);

        $items = [];

        foreach ($deliveryItems as $deliveryItem){
            $items[] = [
                'id' => $deliveryItem->getId(),
                'delivery' => [
                    'id' => $deliveryItem->getDelivery() ? $deliveryItem->getDelivery()->getId() : '',
                    '@id' => '/api/get/delivery/'. $deliveryItem->getDelivery()->getId(),
                    'reference' => $deliveryItem->getDelivery() ? $deliveryItem->getDelivery()->getReference() : '',
                ],
                'item' => [
                    'id' => $deliveryItem->getItem()->getId(),
                    '@id' => '/api/get/item/'. $deliveryItem->getItem()->getId(),
                    'name' => $deliveryItem->getItem() ? $deliveryItem->getItem()->getName() : '',
                    'reference' => $deliveryItem->getItem() ? $deliveryItem->getItem()->getReference() : '',
                    'barcode' => $deliveryItem->getItem() ? $deliveryItem->getItem()->getBarcode() : '',
                    'price' => $deliveryItem->getItem() ? $deliveryItem->getItem()->getPrice() : '',
                    'salePrice' => $deliveryItem->getItem() ? $deliveryItem->getItem()->getSalePrice() : '',
                    'cost' => $deliveryItem->getItem() ? $deliveryItem->getItem()->getCost() : '',
                ],
                'quantity' => $deliveryItem->getQuantity(),

            ];
        }

        return $this->json(['hydra:member' => $items]);
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
