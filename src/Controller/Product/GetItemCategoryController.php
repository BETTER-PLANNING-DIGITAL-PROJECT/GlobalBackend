<?php

namespace App\Controller\Product;

use App\Entity\Security\User;
use App\Repository\Product\ItemCategoryRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetItemCategoryController extends AbstractController
{
    private ItemCategoryRepository $itemCategoryRepository;
    private SystemSettingsRepository $systemSettingsRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                ItemCategoryRepository                     $itemCategoryRepository,
                                SystemSettingsRepository                     $systemSettingsRepository,
    )
    {
        $this->itemCategoryRepository = $itemCategoryRepository;
        $this->systemSettingsRepository = $systemSettingsRepository;
    }

    public function __invoke(Request $request):JsonResponse
    {
        $itemCategoryData = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $itemCategories = $this->itemCategoryRepository->findBy([], ['id' => 'DESC']);

            foreach ($itemCategories as $itemCategory)
            {
                $itemCategoryData[] = [
                    '@id' => "/api/get/item-category/" . $itemCategory->getId(),
                    '@type' => "ItemCategory",
                    'id'=> $itemCategory ->getId(),
                    'name'=> $itemCategory->getName(),
                    'stockStrategy' => [
                        '@id' => "/api/get/stock-strategy/" . $itemCategory->getId(),
                        '@type' => "StockStrategy",
                        'id' => $itemCategory->getStockStrategy() ? $itemCategory->getStockStrategy()->getId() : '',
                        'name' => $itemCategory->getStockStrategy() ? $itemCategory->getStockStrategy()->getName() : '',
                    ],
                    'priceStrategy' => [
                        '@id' => "/api/get/price-strategy/" . $itemCategory->getId(),
                        '@type' => "PriceStrategy",
                        'id' => $itemCategory->getPriceStrategy() ? $itemCategory->getPriceStrategy()->getId() : '',
                        'name' => $itemCategory->getPriceStrategy() ? $itemCategory->getPriceStrategy()->getName() : '',
                    ],
                    'branch' => [
                        '@id' => "/api/get/branch/" . $itemCategory->getId(),
                        '@type' => "Branch",
                        'id' => $itemCategory->getBranch() ? $itemCategory->getBranch()->getId() : '',
                        'code' => $itemCategory->getBranch() ? $itemCategory->getBranch()->getCode() : '',
                        'name' => $itemCategory->getBranch() ? $itemCategory->getBranch()->getName() : '',
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

                       $itemCategories = $this->itemCategoryRepository->findBy(['branch' => $userBranch], ['id' => 'DESC']);
                          foreach ($itemCategories as $itemCategory){
                              $itemCategoryData[] = [
                                  '@id' => "/api/get/item-category/" . $itemCategory->getId(),
                                  '@type' => "ItemCategory",
                                  'id'=> $itemCategory ->getId(),
                                  'name'=> $itemCategory->getName(),
                                  'stockStrategy' => [
                                      '@id' => "/api/get/stock-strategy/" . $itemCategory->getId(),
                                      '@type' => "StockStrategy",
                                      'id' => $itemCategory->getStockStrategy() ? $itemCategory->getStockStrategy()->getId() : '',
                                      'name' => $itemCategory->getStockStrategy() ? $itemCategory->getStockStrategy()->getName() : '',
                                  ],
                                  'priceStrategy' => [
                                      '@id' => "/api/get/price-strategy/" . $itemCategory->getId(),
                                      '@type' => "PriceStrategy",
                                      'id' => $itemCategory->getPriceStrategy() ? $itemCategory->getPriceStrategy()->getId() : '',
                                      'name' => $itemCategory->getPriceStrategy() ? $itemCategory->getPriceStrategy()->getName() : '',
                                  ],
                                  'branch' => [
                                      '@id' => "/api/get/branch/" . $itemCategory->getId(),
                                      '@type' => "Branch",
                                      'id' => $itemCategory->getBranch() ? $itemCategory->getBranch()->getId() : '',
                                      'code' => $itemCategory->getBranch() ? $itemCategory->getBranch()->getCode() : '',
                                      'name' => $itemCategory->getBranch() ? $itemCategory->getBranch()->getName() : '',
                                  ],
                              ];
                          }
                       }
               }
               else {
                   $itemCategories = $this->itemCategoryRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

                   foreach ($itemCategories as $itemCategory) {
                       if ($itemCategory) {
                           $itemCategoryData[] = [
                               '@id' => "/api/get/item-category/" . $itemCategory->getId(),
                               '@type' => "ItemCategory",
                               'id'=> $itemCategory ->getId(),
                               'name'=> $itemCategory->getName(),
                               'stockStrategy' => [
                                   '@id' => "/api/get/stock-strategy/" . $itemCategory->getId(),
                                   '@type' => "StockStrategy",
                                   'id' => $itemCategory->getStockStrategy() ? $itemCategory->getStockStrategy()->getId() : '',
                                   'name' => $itemCategory->getStockStrategy() ? $itemCategory->getStockStrategy()->getName() : '',
                               ],
                               'priceStrategy' => [
                                   '@id' => "/api/get/price-strategy/" . $itemCategory->getId(),
                                   '@type' => "PriceStrategy",
                                   'id' => $itemCategory->getPriceStrategy() ? $itemCategory->getPriceStrategy()->getId() : '',
                                   'name' => $itemCategory->getPriceStrategy() ? $itemCategory->getPriceStrategy()->getName() : '',
                               ],
                               'branch' => [
                                   '@id' => "/api/get/branch/" . $itemCategory->getId(),
                                   '@type' => "Branch",
                                   'id' => $itemCategory->getBranch() ? $itemCategory->getBranch()->getId() : '',
                                   'code' => $itemCategory->getBranch() ? $itemCategory->getBranch()->getCode() : '',
                                   'name' => $itemCategory->getBranch() ? $itemCategory->getBranch()->getName() : '',
                               ],
                           ];
                       }
                   }

               }
            }
        }


        return $this->json(['hydra:member' => $itemCategoryData]);
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
