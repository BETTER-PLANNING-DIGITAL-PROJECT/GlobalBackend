<?php

namespace App\Controller\Inventory;

use App\Entity\Security\User;
use App\Repository\Inventory\StockRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetStockController extends AbstractController
{
    private StockRepository $stockRepository;
    private SystemSettingsRepository $systemSettingsRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                StockRepository                     $stockRepository,
                                SystemSettingsRepository                     $systemSettingsRepository,
    )
    {
        $this->stockRepository = $stockRepository;
        $this->systemSettingsRepository = $systemSettingsRepository;
    }

    public function __invoke(Request $request):JsonResponse
    {
        $stockData = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $stocks = $this->stockRepository->findBy([], ['id' => 'DESC']);

            foreach ($stocks as $stock)
            {
                $stockData[] = [
                    'id'=> $stock ->getId(),
                    'quantity'=> $stock->getQuantity(),
                    'availableQte'=> $stock->getAvailableQte(),
                    'reserveQte'=> $stock->getReserveQte(),
                    'unitCost'=> $stock->getUnitCost(),
                    'totalValue'=> ($stock->getQuantity() * $stock->getUnitCost()),
                    'stockDate'=> $stock->getStockAt() ? $stock->getStockAt()->format('Y-m-d') : '',
                    'loseDate'=> $stock->getLoseAt()? $stock->getLoseAt()->format('Y-m-d') : '',
                    'reference'=> $stock->getReference(),
                    'comment'=> $stock->getNote(),
                    'item' => [
                        '@id' => "/api/get/item/" . $stock->getItem()->getId(),
                        '@type' => "Item",
                        'id' => $stock->getItem() ? $stock->getItem()->getId() : '',
                        'name' => $stock->getItem() ? $stock->getItem()->getName() : '',
                    ],
                    'location' => $stock->getLocation() ? [
                        '@id' => "/api/get/location/" . $stock->getLocation()->getId(),
                        '@type' => "Location",
                        'id' => $stock->getLocation() ? $stock->getLocation()->getId() : '',
                        'name' => $stock->getLocation() ? $stock->getLocation()->getName() : '',
                    ] : '',
                    'warehouse' => [
                        '@id' => "/api/get/warehouse/" . $stock->getWarehouse()->getId(),
                        '@type' => "Warehouse",
                        'id' => $stock->getWarehouse() ? $stock->getWarehouse()->getId() : '',
                        'name' => $stock->getWarehouse() ? $stock->getWarehouse()->getName() : '',
                    ],
                    'branch' => [
                        '@id' => "/api/get/branch/" . $stock->getBranch()->getId(),
                        '@type' => "Branch",
                        'id' => $stock->getBranch() ? $stock->getBranch()->getId() : '',
                        'name' => $stock->getBranch() ? $stock->getBranch()->getName() : '',
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
                       $stocks = $this->stockRepository->findBy(['branch' => $userBranch], ['id' => 'DESC']);
                          foreach ($stocks as $stock){
                              $stockData[] = [
                                  'id'=> $stock ->getId(),
                                  'quantity'=> $stock->getQuantity(),
                                  'availableQte'=> $stock->getAvailableQte(),
                                  'reserveQte'=> $stock->getReserveQte(),
                                  'unitCost'=> $stock->getUnitCost(),
                                  'totalValue'=> $stock->getTotalValue(),
                                  'stockDate'=> $stock->getStockAt() ? $stock->getStockAt()->format('Y-m-d') : '',
                                  'loseDate'=> $stock->getLoseAt()? $stock->getLoseAt()->format('Y-m-d') : '',
                                  'reference'=> $stock->getReference(),
                                  'comment'=> $stock->getNote(),
                                  'item' => [
                                      '@id' => "/api/get/item/" . $stock->getItem()->getId(),
                                      '@type' => "Item",
                                      'id' => $stock->getItem() ? $stock->getItem()->getId() : '',
                                      'name' => $stock->getItem() ? $stock->getItem()->getName() : '',
                                  ],
                                  'location' => $stock->getLocation() ? [
                                      '@id' => "/api/get/location/" . $stock->getLocation()->getId(),
                                      '@type' => "Location",
                                      'id' => $stock->getLocation() ? $stock->getLocation()->getId() : '',
                                      'name' => $stock->getLocation() ? $stock->getLocation()->getName() : '',
                                  ] : '',
                                  'warehouse' => [
                                      '@id' => "/api/get/warehouse/" . $stock->getWarehouse()->getId(),
                                      '@type' => "Warehouse",
                                      'id' => $stock->getWarehouse() ? $stock->getWarehouse()->getId() : '',
                                      'name' => $stock->getWarehouse() ? $stock->getWarehouse()->getName() : '',
                                  ],
                                  'branch' => [
                                      '@id' => "/api/get/branch/" . $stock->getBranch()->getId(),
                                      '@type' => "Branch",
                                      'id' => $stock->getBranch() ? $stock->getBranch()->getId() : '',
                                      'name' => $stock->getBranch() ? $stock->getBranch()->getName() : '',
                                  ],
                              ];
                          }
                       }
               }
               else {
                   $stocks = $this->stockRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

                   foreach ($stocks as $stock) {
                       if ($stock) {
                           $stockData[] = [
                               'id'=> $stock ->getId(),
                               'quantity'=> $stock->getQuantity(),
                               'availableQte'=> $stock->getAvailableQte(),
                               'reserveQte'=> $stock->getReserveQte(),
                               'unitCost'=> $stock->getUnitCost(),
                               'totalValue'=> $stock->getTotalValue(),
                               'stockDate'=> $stock->getStockAt() ? $stock->getStockAt()->format('Y-m-d') : '',
                               'loseDate'=> $stock->getLoseAt()? $stock->getLoseAt()->format('Y-m-d') : '',
                               'reference'=> $stock->getReference(),
                               'comment'=> $stock->getNote(),
                               'item' => [
                                   '@id' => "/api/get/item/" . $stock->getItem()->getId(),
                                   '@type' => "Item",
                                   'id' => $stock->getItem() ? $stock->getItem()->getId() : '',
                                   'name' => $stock->getItem() ? $stock->getItem()->getName() : '',
                               ],
                               'location' => $stock->getLocation() ? [
                                   '@id' => "/api/get/location/" . $stock->getLocation()->getId(),
                                   '@type' => "Location",
                                   'id' => $stock->getLocation() ? $stock->getLocation()->getId() : '',
                                   'name' => $stock->getLocation() ? $stock->getLocation()->getName() : '',
                               ] : '',
                               'warehouse' => [
                                   '@id' => "/api/get/warehouse/" . $stock->getWarehouse()->getId(),
                                   '@type' => "Warehouse",
                                   'id' => $stock->getWarehouse() ? $stock->getWarehouse()->getId() : '',
                                   'name' => $stock->getWarehouse() ? $stock->getWarehouse()->getName() : '',
                               ],
                               'branch' => [
                                   '@id' => "/api/get/branch/" . $stock->getBranch()->getId(),
                                   '@type' => "Branch",
                                   'id' => $stock->getBranch() ? $stock->getBranch()->getId() : '',
                                   'name' => $stock->getBranch() ? $stock->getBranch()->getName() : '',
                               ],
                           ];
                       }
                   }

               }
            }
        }


        return $this->json(['hydra:member' => $stockData]);
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
