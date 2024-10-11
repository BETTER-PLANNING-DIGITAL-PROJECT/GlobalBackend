<?php
namespace App\State\Processor\Inventory\Delivery;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Inventory\DeliveryItem;
use App\Entity\Inventory\DeliveryItemStock;
use App\Entity\Security\User;
use App\Repository\Inventory\DeliveryItemRepository;
use App\Repository\Inventory\DeliveryItemStockRepository;
use App\Repository\Inventory\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PutDeliveryItemProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly StockRepository $stockRepository,
                                private readonly DeliveryItemStockRepository $deliveryItemStockRepository,
                                private readonly DeliveryItemRepository $deliveryItemRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if(!$data instanceof DeliveryItem)
        {
            return new JsonResponse(['hydra:description' => 'This delivery item is not found.'], 404);
        }

        $deliveryItem = $this->deliveryItemRepository->find($data->getId());
        if(!$deliveryItem)
        {
            return new JsonResponse(['hydra:description' => 'This delivery item is not found.'], 404);
        }

        $deliveryItemData = json_decode($this->request->getContent(), true);

        if (!is_numeric($deliveryItemData['quantity'])){
            return new JsonResponse(['hydra:description' => 'Quantity must be numeric value!'], 500);
        }
        elseif ($deliveryItemData['quantity'] <= 0){
            return new JsonResponse(['hydra:description' => 'Quantity must be upper than 0!'], 500);
        }

        $stock = $this->stockRepository->find($this->getIdFromApiResourceId($deliveryItemData['item']));
        if (!$stock){
            return new JsonResponse(['hydra:description' => 'Stock not found!'], 500);
        }

        // delete all the previous delivery item stock
        $deliveryItemStocks = $this->deliveryItemStockRepository->findBy(['deliveryItem' => $deliveryItem]);
        if ($deliveryItemStocks){
            foreach ($deliveryItemStocks as $deliveryItemStock){
                // $stock = $deliveryItemStock->getStock();
                $this->manager->remove($deliveryItemStock);
            }
            $this->manager->flush();
        }

        // only quantity can change
        $deliveryItem->setQuantity($deliveryItemData['quantity']);

        // create new delivery item stock
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
