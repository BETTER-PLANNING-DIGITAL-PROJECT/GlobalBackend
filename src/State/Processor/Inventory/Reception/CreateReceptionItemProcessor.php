<?php
namespace App\State\Processor\Inventory\Reception;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Inventory\Reception;
use App\Entity\Inventory\ReceptionItem;
use App\Entity\Security\User;
use App\Repository\Inventory\ReceptionRepository;
use App\Repository\Inventory\StockRepository;
use App\Repository\Product\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class CreateReceptionItemProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly ItemRepository $itemRepository,
                                private readonly ReceptionRepository $receptionRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if(!$data instanceof Reception)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This reception is not found.'], 404);
        }

        $reception = $this->receptionRepository->find($data->getId());

        $receptionData = json_decode($this->request->getContent(), true);

        if (!is_numeric($receptionData['quantity'])){
            return new JsonResponse(['hydra:description' => 'Quantity must be numeric value.'], 500);
        }

        if ($receptionData['quantity'] <= 0){
            return new JsonResponse(['hydra:description' => 'Quantity must be upper than 0.'], 500);
        }

        $item = $this->itemRepository->find($this->getIdFromApiResourceId($receptionData['item']));
        if (!$item){
            return new JsonResponse(['hydra:description' => 'Item not found!'], 500);
        }

        $receptionItem = new ReceptionItem();

        $receptionItem->setItem($item);
        $receptionItem->setQuantity($receptionData['quantity']);
        $receptionItem->setReception($reception);

        $receptionItem->setUser($this->getUser());
        $receptionItem->setInstitution($this->getUser()->getInstitution());
        $receptionItem->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($receptionItem);
        $this->manager->flush();

        return $this->processor->process($receptionItem, $operation, $uriVariables, $context);
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
