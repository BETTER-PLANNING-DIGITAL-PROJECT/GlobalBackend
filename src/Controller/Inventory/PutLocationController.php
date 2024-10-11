<?php

namespace App\Controller\Inventory;

use App\Entity\Security\User;
use App\Repository\Inventory\LocationRepository;
use App\Repository\Inventory\WarehouseRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PutLocationController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data, Request $request, WarehouseRepository $warehouseRepository, BranchRepository $branchRepository,
                             SystemSettingsRepository $systemSettingsRepository, LocationRepository $locationRepository)
    {
        $locationData = json_decode($request->getContent(), true);
        $systemSettings = $systemSettingsRepository->findOneBy([]);

        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                if (!isset($locationData['branch'])) {
                    return new JsonResponse(['hydra:description' => 'Branch not found!.'], 400);
                }
                $branch = $branchRepository->find($this->getIdFromApiResourceId($locationData['branch']));
            }
            else{
                $branch = $this->getUser()->getBranch();
            }
        }
        else{
            $branch = $this->getUser()->getBranch();
        }

        $warehouse = !isset($locationData['warehouse']) ? null : $warehouseRepository->find($this->getIdFromApiResourceId($locationData['warehouse']));

        $name = $locationData['name'];

        // Check for duplicates based on name within the same branch
        $duplicateCheckName = $locationRepository->findOneBy(['name' => $name, 'branch' => $branch, 'warehouse' => $warehouse]);
        if ($duplicateCheckName && ($duplicateCheckName != $data)) {
            return new JsonResponse(['hydra:description' => 'This name already exists in this branch and warehouse.'], 400);
        }

        $data->setName($locationData['name']);
        $data->setWarehouse($warehouse);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $data->setBranch($branch);
            }
        }

        return $data;
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
