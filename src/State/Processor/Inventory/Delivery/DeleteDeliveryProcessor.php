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

final class DeleteDeliveryProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $manager,
                                private readonly DeliveryItemRepository $deliveryItemRepository,
                                private readonly DeliveryItemStockRepository $deliveryItemStockRepository,
                                Private readonly DeliveryRepository $deliveryRepository) {
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

        if($delivery->getStatus() == 'delivery')
        {
            return new JsonResponse(['hydra:description' => 'Can not delete validate delivery.'], 404);
        }

        // check if there is an invoice on it
        $delivery->getSaleInvoice()?->setOtherStatus('draft');

        $deliveryItems = $this->deliveryItemRepository->findBy(['delivery'=> $delivery]);
        if($deliveryItems)
        {
            foreach ($deliveryItems as $deliveryItem)
            {
                // find delivery item stock
                $deliveryItemStocks = $this->deliveryItemStockRepository->findBy(['deliveryItem' => $deliveryItem]);
                if($deliveryItemStocks)
                {
                    foreach ($deliveryItemStocks as $deliveryItemStock)
                    {
                        $this->manager->remove($deliveryItemStock);
                    }
                }

                $this->manager->remove($deliveryItem);
            }
        }

        $this->manager->remove($delivery);
        $this->manager->flush();

    }

}
