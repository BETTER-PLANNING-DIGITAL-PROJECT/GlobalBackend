<?php

namespace App\Controller\Inventory;

use App\Entity\Inventory\Stock;
use App\Entity\Inventory\StockMovement;
use App\Entity\Security\User;
use App\Repository\Inventory\LocationRepository;
use App\Repository\Inventory\StockMovementRepository;
use App\Repository\Inventory\StockRepository;
use App\Repository\Inventory\WarehouseRepository;
use App\Repository\Product\ItemRepository;
use App\Repository\Product\UnitRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class CreateStockController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }
    public function jsondecode()
    {
        try {
            return file_get_contents('php://input') ?
                json_decode(file_get_contents('php://input'), false) :
                [];
        }catch (\Exception $ex)
        {
            return [];
        }

    }
    public function __invoke(StockRepository         $stockRepository,
                             ItemRepository          $itemRepository,
                             UnitRepository          $unitRepository,
                             StockMovementRepository $stockMovementRepository,
                             WarehouseRepository     $warehouseRepository,
                             LocationRepository      $locationRepository,
                             EntityManagerInterface  $entityManager,
                             SystemSettingsRepository $systemSettingsRepository,
                             BranchRepository $branchRepository,
                             Request                 $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $systemSettings = $systemSettingsRepository->findOneBy([]);

        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                if (!isset($requestData['branch'])) {
                    return new JsonResponse(['hydra:title' => 'Branch not found!.'], 400);
                }
                $branch = $branchRepository->find($this->getIdFromApiResourceId($requestData['branch']));
            }
            else{
                $branch = $this->getUser()->getBranch();
            }
        }
        else{
            $branch = $this->getUser()->getBranch();
        }

        $stock = new Stock();

        $reference = $requestData['reference'];

        if(!$reference)
        {
            $stockRef = $stockRepository->findOneBy(['branch' => $branch], ['id' => 'DESC']);
            if (!$stockRef){
                $reference = 'WH/ST/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
            }
            else{
                $filterNumber = preg_replace("/[^0-9]/", '', $stockRef->getReference());
                $number = intval($filterNumber);

                // Utilisation de number_format() pour ajouter des zéros à gauche
                $reference = 'WH/ST/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
            }
        }

        if (!$requestData['item']){
            return new JsonResponse(['hydra:title' => 'Item not found'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }
        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $requestData['item']);
        $filterId = intval($filter);
        $item = $itemRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        // warehouse
        if (!$requestData['warehouse']){
            return new JsonResponse(['hydra:title' => 'Warehouse not found'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }
        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $requestData['warehouse']);
        $filterId = intval($filter);
        $warehouse = $warehouseRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        if(!$requestData['quantity'])
        {
            return new JsonResponse(['hydra:title' => 'Quantity not found'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }
        else if($requestData['quantity'] < 0)
        {
            return new JsonResponse(['hydra:title' => 'Quantity can not be a negative number'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if(!$requestData['stockAt'])
        {
            return new JsonResponse(['hydra:title' => 'Stock entry date not found'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

//        if(!$requestData['unitCost'])
//        {
//            return new JsonResponse(['hydra:title' => 'Unit cost not found'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
//        }
//        elseif (!is_float($requestData['unitCost']))
//        {
//            return new JsonResponse(['hydra:title' => 'Unit cost should be float value'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
//        }
//        else if($requestData['unitCost'] < 0)
//        {
//            return new JsonResponse(['hydra:title' => 'Unit cost can not be a negative number'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
//        }

        $stock->setReference($reference);

        $stock->setItem($item);
        $stock->setQuantity($requestData['quantity']);
        $stock->setAvailableQte($requestData['quantity']);
        $stock->setReserveQte(0);

        $stock->setUnitCost($requestData['unitCost']);
        // $stock->setTotalValue($requestData['unitCost'] * $requestData['quantity']);

        //$stock->setUnit($unit);
        $stock->setWarehouse($warehouse);
        if (isset($requestData['location']) && $requestData['location'] != null){
            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $requestData['location']);
            $filterId = intval($filter);
            $location = $locationRepository->find($filterId);
            $stock->setLocation($location);
            // END: Filter the uri to just take the id and pass it to our object
        }

        $stock->setStockAt(new \DateTimeImmutable($requestData['stockAt']));

        if (isset($requestData['loseAt']) && $requestData['loseAt'] != null){
            $stock->setLoseAt(new \DateTimeImmutable($requestData['loseAt']));
        }

        $stock->setNote($requestData['note']);

        $stock->setBranch($branch);
        $stock->setUser($this->getUser());

        $stock->setInstitution($this->getUser()->getInstitution());
        $stock->setYear($this->getUser()->getCurrentYear());

        $entityManager->persist($stock);


        // Stock movement section

        // reference
        $stockMovementRef = $stockMovementRepository->findOneBy(['isOut' => false, 'branch' => $branch], ['id' => 'DESC']);
        if (!$stockMovementRef){
            $reference = 'WH/IN/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $stockMovementRef->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $reference = 'WH/IN/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $stockMovement = new StockMovement();

        $stockMovement->setReference($reference);
        $stockMovement->setItem($item);
        $stockMovement->setQuantity($requestData['quantity']);
        $stockMovement->setUnitCost($requestData['unitCost']);
        $stockMovement->setToWarehouse($warehouse);

        if (isset($requestData['location']) && $requestData['location'] != null){
            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $requestData['location']);
            $filterId = intval($filter);
            $location = $locationRepository->find($filterId);
            $stockMovement->setToLocation($location);
            // END: Filter the uri to just take the id and pass it to our object
        }

        $stockMovement->setStockAt(new \DateTimeImmutable($requestData['stockAt']));

        if (isset($requestData['loseAt']) && $requestData['loseAt'] != null){
            $stockMovement->setLoseAt(new \DateTimeImmutable($requestData['loseAt']));
        }

        $stockMovement->setNote($requestData['note']);
        $stockMovement->setType('stock entry');

        $stockMovement->setIsOut(false);

        $stockMovement->setStock($stock);

        $stockMovement->setBranch($branch);
        $stockMovement->setUser($this->getUser());
        $stockMovement->setInstitution($this->getUser()->getInstitution());
        $stockMovement->setYear($this->getUser()->getCurrentYear());

        $entityManager->persist($stockMovement);

        $entityManager->flush();

        return $this->json(['hydra:member' => $stockMovement]);
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

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
    }

}
