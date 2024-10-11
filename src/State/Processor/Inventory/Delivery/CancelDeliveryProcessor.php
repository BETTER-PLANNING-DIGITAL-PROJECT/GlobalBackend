<?php
namespace App\State\Processor\Inventory\Delivery;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Inventory\Delivery;
use App\Repository\Inventory\DeliveryItemRepository;
use App\Repository\Inventory\DeliveryItemStockRepository;
use App\Repository\Inventory\DeliveryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CancelDeliveryProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $manager,
                                Private readonly DeliveryRepository $deliveryRepository,
                                Private readonly DeliveryItemRepository $deliveryItemRepository,
                                Private readonly DeliveryItemStockRepository $deliveryItemStockRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if(!$data instanceof Delivery)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of delivery.'], 404);
        }

        $delivery = $this->deliveryRepository->find($data->getId());
        if(!$delivery)
        {
            return new JsonResponse(['hydra:description' => 'This delivery is not found.'], 404);
        }

        $deliveryItems = $this->deliveryItemRepository->findBy(['delivery' => $delivery]);
        foreach ($deliveryItems as $deliveryItem)
        {
            // find delivery item stock
            $deliveryItemStocks = $this->deliveryItemStockRepository->findBy(['deliveryItem' => $deliveryItem]);
            if($deliveryItemStocks)
            {
                foreach ($deliveryItemStocks as $deliveryItemStock)
                {
                    $stock = $deliveryItemStock->getStock();

                    // rollback reserved quantity
                    $stock->setReserveQte($stock->getReserveQte() - $deliveryItemStock->getQuantity());
                    $stock->setAvailableQte($stock->getAvailableQte() + $deliveryItemStock->getQuantity());
                }
            }
        }

        $delivery->setStatus('draft');
        $this->manager->flush();

        return $this->processor->process($delivery, $operation, $uriVariables, $context);
    }
}
