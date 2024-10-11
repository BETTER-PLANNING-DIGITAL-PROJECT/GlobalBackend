<?php

namespace App\Controller\Inventory;

use App\Entity\Security\User;
use App\Repository\Inventory\WarehouseRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetWareHouseController extends AbstractController
{
    private WarehouseRepository $warehouseRepository;
    private SystemSettingsRepository $systemSettingsRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                WarehouseRepository                     $warehouseRepository,
                                SystemSettingsRepository                     $systemSettingsRepository,
    )
    {
        $this->warehouseRepository = $warehouseRepository;
        $this->systemSettingsRepository = $systemSettingsRepository;
    }

    public function __invoke(Request $request):JsonResponse
    {
        $warehouseData = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $warehouses = $this->warehouseRepository->findBy([], ['id' => 'DESC']);

            foreach ($warehouses as $warehouse)
            {
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
        else
        {
            $systemSettings = $this->systemSettingsRepository->findOneBy([]);
            if($systemSettings)
            {
                if($systemSettings->isIsBranches())
               {
                   $userBranches = $this->getUser()->getUserBranches();
                   foreach ($userBranches as $userBranch) {

                       // get cash desk
                       $warehouses = $this->warehouseRepository->findBy(['branch' => $userBranch], ['id' => 'DESC']);
                          foreach ($warehouses as $warehouse){
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
               }
               else {
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

               }
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
