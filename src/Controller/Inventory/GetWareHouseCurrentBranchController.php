<?php

namespace App\Controller\Inventory;

use App\Entity\Security\User;
use App\Repository\Inventory\WarehouseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetWareHouseCurrentBranchController extends AbstractController
{
    private WarehouseRepository $warehouseRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                WarehouseRepository                     $warehouseRepository,
    )
    {
        $this->warehouseRepository = $warehouseRepository;
    }

    public function __invoke(Request $request):JsonResponse
    {
        $warehouseData = [];

        $warehouses = $this->warehouseRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

        foreach ($warehouses as $warehouse) {
            if ($warehouse) {
                $warehouseData[] = [
                    '@id' => "/api/get/warehouse/" . $warehouse->getId(),
                    '@type' => "Warehouse",
                    'id'=> $warehouse ->getId(),
                    'code'=> $warehouse->getCode(),
                    'name'=> $warehouse->getName(),
                    'address'=> $warehouse->getAddress(),
                    'branch' => [
                        '@id' => "/api/get/branch/" . $warehouse->getBranch()->getId(),
                        '@type' => "Branch",
                        'id' => $warehouse->getBranch() ? $warehouse->getBranch()->getId() : '',
                        'name' => $warehouse->getBranch() ? $warehouse->getBranch()->getName() : '',
                    ],
                ];
            }
        }

        return $this->json(['hydra:member' => $warehouseData]);
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
