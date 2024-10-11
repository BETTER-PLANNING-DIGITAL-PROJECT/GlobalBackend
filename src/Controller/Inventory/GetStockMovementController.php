<?php

namespace App\Controller\Inventory;

use App\Entity\Security\User;
use App\Repository\Inventory\LocationRepository;
use App\Repository\Inventory\StockMovementRepository;
use App\Repository\Inventory\StockRepository;
use App\Repository\Inventory\WarehouseRepository;
use App\Repository\Security\SystemSettingsRepository;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\CashDeskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetStockMovementController extends AbstractController
{
    private StockMovementRepository $stockMovementRepository;
    private SystemSettingsRepository $systemSettingsRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                StockMovementRepository                     $stockMovementRepository,
                                SystemSettingsRepository                     $systemSettingsRepository,
    )
    {
        $this->stockMovementRepository = $stockMovementRepository;
        $this->systemSettingsRepository = $systemSettingsRepository;
    }

    public function __invoke(Request $request):JsonResponse
    {
        $stockMovementData = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $stockMovements = $this->stockMovementRepository->findBy([], ['id' => 'DESC']);

            foreach ($stockMovements as $stockMovement)
            {
                $stockMovementData[] = [
                    'id'=> $stockMovement ->getId(),
                    'stockAt'=> $stockMovement->getStockAt()->format('Y-m-d'),
                    'reference'=> $stockMovement->getReference(),
                    'quantity'=> $stockMovement->getQuantity(),
                    'fromWarehouse' => [
                        '@id' => "/api/get/from-warehouse/" . $stockMovement->getFromWarehouse()->getId(),
                        '@type' => "FromWarehouse",
                        'id' => $stockMovement->getFromWarehouse() ? $stockMovement->getFromWarehouse()->getId() : '',
                        'name' => $stockMovement->getFromWarehouse() ? $stockMovement->getFromWarehouse()->getName() : '',
                    ],
                    'toWarehouse' => [
                        '@id' => "/api/get/to-warehouse/" . $stockMovement->getToWarehouse()->getId(),
                        '@type' => "ToWarehouse",
                        'id' => $stockMovement->getToWarehouse() ? $stockMovement->getToWarehouse()->getId() : '',
                        'name' => $stockMovement->getToWarehouse() ? $stockMovement->getToWarehouse()->getName() : '',
                    ],
                    'item' => [
                        '@id' => "/api/get/item/" . $stockMovement->getItem()->getId(),
                        '@type' => "Item",
                        'id' => $stockMovement->getItem() ? $stockMovement->getItem()->getId() : '',
                        'name' => $stockMovement->getItem() ? $stockMovement->getItem()->getName() : '',
                    ],
                    'branch' => [
                        '@id' => "/api/get/branch/" . $stockMovement->getBranch()->getId(),
                        '@type' => "Branch",
                        'id' => $stockMovement->getBranch() ? $stockMovement->getBranch()->getId() : '',
                        'name' => $stockMovement->getBranch() ? $stockMovement->getBranch()->getName() : '',
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
                       $stockMovements = $this->stockMovementRepository->findBy(['branch' => $userBranch], ['id' => 'DESC']);
                          foreach ($stockMovements as $stockMovement){
                              $stockMovementData[] = [
                                  'id'=> $stockMovement ->getId(),
                                  'stockAt'=> $stockMovement->getStockAt()->format('Y-m-d'),
                                  'reference'=> $stockMovement->getReference(),
                                  'quantity'=> $stockMovement->getQuantity(),
                                  'fromWarehouse' => [
                                      '@id' => "/api/get/from-warehouse/" . $stockMovement->getFromWarehouse()->getId(),
                                      '@type' => "FromWarehouse",
                                      'id' => $stockMovement->getFromWarehouse() ? $stockMovement->getFromWarehouse()->getId() : '',
                                      'name' => $stockMovement->getFromWarehouse() ? $stockMovement->getFromWarehouse()->getName() : '',
                                  ],
                                  'toWarehouse' => [
                                      '@id' => "/api/get/to-warehouse/" . $stockMovement->getToWarehouse()->getId(),
                                      '@type' => "ToWarehouse",
                                      'id' => $stockMovement->getToWarehouse() ? $stockMovement->getToWarehouse()->getId() : '',
                                      'name' => $stockMovement->getToWarehouse() ? $stockMovement->getToWarehouse()->getName() : '',
                                  ],
                                  'item' => [
                                      '@id' => "/api/get/item/" . $stockMovement->getItem()->getId(),
                                      '@type' => "Item",
                                      'id' => $stockMovement->getItem() ? $stockMovement->getItem()->getId() : '',
                                      'name' => $stockMovement->getItem() ? $stockMovement->getItem()->getName() : '',
                                  ],
                                  'branch' => [
                                      '@id' => "/api/get/branch/" . $stockMovement->getBranch()->getId(),
                                      '@type' => "Branch",
                                      'id' => $stockMovement->getBranch() ? $stockMovement->getBranch()->getId() : '',
                                      'name' => $stockMovement->getBranch() ? $stockMovement->getBranch()->getName() : '',
                                  ],
                              ];
                          }
                       }
               }
               else {
                   $stockMovements = $this->stockMovementRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

                   foreach ($stockMovements as $stockMovement) {
                       if ($stockMovement) {
                           $stockMovementData[] = [
                               'id'=> $stockMovement ->getId(),
                               'stockAt'=> $stockMovement->getStockAt()->format('Y-m-d'),
                               'reference'=> $stockMovement->getReference(),
                               'quantity'=> $stockMovement->getQuantity(),
                               'fromWarehouse' => [
                                   '@id' => "/api/get/from-warehouse/" . $stockMovement->getFromWarehouse()->getId(),
                                   '@type' => "FromWarehouse",
                                   'id' => $stockMovement->getFromWarehouse() ? $stockMovement->getFromWarehouse()->getId() : '',
                                   'name' => $stockMovement->getFromWarehouse() ? $stockMovement->getFromWarehouse()->getName() : '',
                               ],
                               'toWarehouse' => [
                                   '@id' => "/api/get/to-warehouse/" . $stockMovement->getToWarehouse()->getId(),
                                   '@type' => "ToWarehouse",
                                   'id' => $stockMovement->getToWarehouse() ? $stockMovement->getToWarehouse()->getId() : '',
                                   'name' => $stockMovement->getToWarehouse() ? $stockMovement->getToWarehouse()->getName() : '',
                               ],
                               'item' => [
                                   '@id' => "/api/get/item/" . $stockMovement->getItem()->getId(),
                                   '@type' => "Item",
                                   'id' => $stockMovement->getItem() ? $stockMovement->getItem()->getId() : '',
                                   'name' => $stockMovement->getItem() ? $stockMovement->getItem()->getName() : '',
                               ],
                               'branch' => [
                                   '@id' => "/api/get/branch/" . $stockMovement->getBranch()->getId(),
                                   '@type' => "Branch",
                                   'id' => $stockMovement->getBranch() ? $stockMovement->getBranch()->getId() : '',
                                   'name' => $stockMovement->getBranch() ? $stockMovement->getBranch()->getName() : '',
                               ],
                           ];
                       }
                   }

               }
            }
        }


        return $this->json(['hydra:member' => $stockMovementData]);
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
