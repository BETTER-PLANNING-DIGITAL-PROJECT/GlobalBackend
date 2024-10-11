<?php
namespace App\State\Processor\Inventory\Delivery;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Inventory\Delivery;
use App\Entity\Inventory\DeliveryItem;
use App\Entity\Inventory\DeliveryItemStock;
use App\Entity\Security\User;
use App\Repository\Inventory\DeliveryRepository;
use App\Repository\Inventory\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class CreateDeliveryItemProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly StockRepository $stockRepository,
                                private readonly DeliveryRepository $deliveryRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if(!$data instanceof Delivery)
        {
            return new JsonResponse(['hydra:description' => 'This delivery is not found.'], 404);
        }

        $delivery = $this->deliveryRepository->find($data->getId());
        if(!$delivery)
        {
            return new JsonResponse(['hydra:description' => 'This delivery is not found.'], 404);
        }

        $deliveryItemData = json_decode($this->request->getContent(), true);

        if (!is_numeric($deliveryItemData['quantity'])){
            return new JsonResponse(['hydra:description' => 'Quantity must be numeric value!'], 500);
        }

        if ($deliveryItemData['quantity'] <= 0){
            return new JsonResponse(['hydra:description' => 'Quantity must be upper than 0!'], 500);
        }

        $stock = $this->stockRepository->find($this->getIdFromApiResourceId($deliveryItemData['item']));
        if (!$stock){
            return new JsonResponse(['hydra:description' => 'Stock not found!'], 500);
        }

        if (!$stock->getItem()){
            return new JsonResponse(['hydra:description' => 'Item not found!'], 500);
        }

        $deliveryItem = new DeliveryItem();

        $deliveryItem->setDelivery($delivery);
        $deliveryItem->setItem($stock->getItem());
        $deliveryItem->setQuantity($deliveryItemData['quantity']);

        $deliveryItem->setUser($this->getUser());
        $deliveryItem->setInstitution($this->getUser()->getInstitution());
        $deliveryItem->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($deliveryItem);

        // create delivery item stock
        $deliveryItemStock = new DeliveryItemStock();

        $deliveryItemStock->setDeliveryItem($deliveryItem);
        $deliveryItemStock->setStock($stock);
        $deliveryItemStock->setQuantity($deliveryItemData['quantity']);

        $deliveryItemStock->setCreatedAt(new \DateTimeImmutable());
        $deliveryItemStock->setUser($this->getUser());
        $deliveryItemStock->setYear($this->getUser()->getCurrentYear());
        $deliveryItemStock->setBranch($this->getUser()->getBranch());
        $deliveryItemStock->setInstitution($this->getUser()->getInstitution());

        $this->manager->persist($deliveryItemStock);
        // create delivery item stock end

        $this->manager->flush();

        return $this->processor->process($deliveryItem, $operation, $uriVariables, $context);
    }

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
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
