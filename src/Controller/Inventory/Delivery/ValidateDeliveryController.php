<?php

namespace App\Controller\Inventory\Delivery;

use App\Entity\Inventory\Delivery;
use App\Entity\Security\User;
use App\Repository\Inventory\DeliveryItemRepository;
use App\Repository\Inventory\DeliveryItemStockRepository;
use App\Repository\Inventory\DeliveryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class ValidateDeliveryController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(DeliveryItemRepository $deliveryItemRepository,
                             DeliveryRepository $deliveryRepository,
                             DeliveryItemStockRepository $deliveryItemStockRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $delivery = $deliveryRepository->find($id);

        if(!$delivery instanceof Delivery)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of delivery.'], 404);
        }

        if($delivery->getStatus() == 'delivery')
        {
            return new JsonResponse(['hydra:description' => 'This delivery is already validate.'], 404);
        }

        $deliveryItems = $deliveryItemRepository->findBy(['delivery' => $delivery]);
        if(!$deliveryItems)
        {
            return new JsonResponse(['hydra:description' => 'Cannot proceed with empty cart.'], 404);
        }

        // this is only when it is a delivery not coming from sale
        foreach ($deliveryItems as $deliveryItem)
        {
            // find delivery item stock
            $deliveryItemStocks = $deliveryItemStockRepository->findBy(['deliveryItem' => $deliveryItem]);
            if($deliveryItemStocks)
            {
                foreach ($deliveryItemStocks as $deliveryItemStock)
                {
                    $stock = $deliveryItemStock->getStock();

                    // reserved quantity
                    $stock->setReserveQte($stock->getReserveQte() + $deliveryItemStock->getQuantity());
                    $stock->setAvailableQte($stock->getAvailableQte() - $deliveryItemStock->getQuantity());
                }
            }
        }

        $delivery->setIsValidate(true);
        $delivery->setValidateAt(new \DateTimeImmutable());
        $delivery->setValidateBy($this->getUser());
        $delivery->setStatus('delivery');

        $entityManager->flush();

        return $this->json(['hydra:member' => '200']);
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
