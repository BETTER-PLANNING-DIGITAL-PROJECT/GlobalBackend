<?php

namespace App\Controller\Inventory\Delivery;

use App\Entity\Inventory\Delivery;
use App\Entity\Inventory\StockMovement;
use App\Entity\Security\User;
use App\Repository\Inventory\DeliveryItemRepository;
use App\Repository\Inventory\DeliveryItemStockRepository;
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
class StockOutDeliveryController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(DeliveryRepository $deliveryRepository,
                             DeliveryItemRepository $deliveryItemRepository,
                             DeliveryItemStockRepository $deliveryItemStockRepository,
                             StockRepository $stockRepository,
                             StockMovementRepository $stockMovementRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $data = json_decode($request->getContent(), true);

        $delivery = $deliveryRepository->find($id);

        if(!($delivery instanceof Delivery))
        {
            return new JsonResponse(['hydra:title' => 'This data must be type of delivery.'], 404);
        }

        if($delivery->getOtherStatus() == 'stock out')
        {
            return new JsonResponse(['hydra:title' => 'Delivery already stock out.'], 500);
        }

        $deliveryItems = $deliveryItemRepository->findBy(['delivery' => $delivery]);
        if ($deliveryItems)
        {
            foreach ($deliveryItems as $deliveryItem)
            {
                // find delivery item stock
                $deliveryItemStocks = $deliveryItemStockRepository->findBy(['deliveryItem' => $deliveryItem]);
                if ($deliveryItemStocks)
                {
                    foreach ($deliveryItemStocks as $deliveryItemStock)
                    {
                        // Faire la sortie de stock
                        $stock = $deliveryItemStock->getStock();

                        $stock->setReserveQte($stock->getReserveQte() - $deliveryItemStock->getQuantity());
                        $stock->setQuantity($stock->getQuantity() - $deliveryItemStock->getQuantity());

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
                        $stockMovement->setQuantity($deliveryItemStock->getQuantity());
                        $stockMovement->setUnitCost($stock->getUnitCost());
                        $stockMovement->setFromWarehouse($stock->getWarehouse());
                        // from location
                        // to warehouse
                        // to location
                        $stockMovement->setStockAt(new \DateTimeImmutable());
                        $stockMovement->setLoseAt($stock->getLoseAt());
                        $stockMovement->setNote('delivery stock out');
                        $stockMovement->setType('delivery stock out');
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

        $delivery->setOtherStatus('stock out');

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
