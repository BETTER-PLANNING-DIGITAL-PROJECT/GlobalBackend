<?php

namespace App\Controller\Inventory\Delivery;

use App\Entity\Security\User;
use App\Repository\Inventory\DeliveryItemRepository;
use App\Repository\Inventory\DeliveryItemStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class DeleteDeliveryItemController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(DeliveryItemRepository $deliveryItemRepository,
                             DeliveryItemStockRepository $deliveryItemStockRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $deliveryItem = $deliveryItemRepository->findOneBy(['id' => $id]);
        if (!$deliveryItem){
            return new JsonResponse(['hydra:description' => 'This delivery item '.$id.' is not found.'], 404);
        }

        $deliveryItemStocks = $deliveryItemStockRepository->findBy(['deliveryItem' => $deliveryItem]);
        if ($deliveryItemStocks){
            foreach ($deliveryItemStocks as $deliveryItemStock){
                $entityManager->remove($deliveryItemStock);
            }
        }

        $entityManager->remove($deliveryItem);

        $entityManager->flush();

        return $this->json(['hydra:member' => 200]);
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
