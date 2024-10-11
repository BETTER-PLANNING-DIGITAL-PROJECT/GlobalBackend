<?php

namespace App\Controller\Product;

use App\Entity\Security\User;
use App\Repository\Product\ItemRepository;
use App\Repository\Security\SystemSettingsRepository;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\CashDeskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetItemController extends AbstractController
{
    private ItemRepository $itemRepository;
    private SystemSettingsRepository $systemSettingsRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                ItemRepository                     $itemRepository,
                                SystemSettingsRepository                     $systemSettingsRepository,
    )
    {
        $this->itemRepository = $itemRepository;
        $this->systemSettingsRepository = $systemSettingsRepository;
    }

    public function __invoke(Request $request):JsonResponse
    {
        $itemData = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $items = $this->itemRepository->findBy([], ['id' => 'DESC']);

            foreach ($items as $item)
            {
                $itemData[] = [
                    '@id' => "/api/get/item/" . $item->getId(),
                    '@type' => "Item",
                    'id'=> $item ->getId(),
                    'name'=> $item->getName(),
                    'reference'=> $item->getReference(),
                    'price'=> $item->getPrice(),
                    'barcode'=> $item->getBarcode(),
                    'salePrice'=> $item->getSalePrice(),
                    'unit' => [
                        '@type' => "Unit",
                        'id' => $item->getUnit() ? $item->getUnit()->getId() : '',
                        'name' => $item->getUnit() ? $item->getUnit()->getName() : '',
                    ],
                    'itemType' => [
                        '@type' => "ItemType",
                        'id' => $item->getItemType() ? $item->getItemType()->getId() : '',
                        'name' => $item->getItemType() ? $item->getItemType()->getName() : '',
                    ],
                    'itemCategory' => [
                        '@type' => "ItemCategory",
                        'id' => $item->getItemCategory() ? $item->getItemCategory()->getId() : '',
                        'name' => $item->getItemCategory() ? $item->getItemCategory()->getName() : '',
                    ],
                    'branch' => [
                        '@id' => "/api/get/branch/" . $item->getId(),
                        '@type' => "Branch",
                        'id' => $item->getBranch() ? $item->getBranch()->getId() : '',
                        'code' => $item->getBranch() ? $item->getBranch()->getCode() : '',
                        'name' => $item->getBranch() ? $item->getBranch()->getName() : '',
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

                       $items = $this->itemRepository->findBy(['branch' => $userBranch], ['id' => 'DESC']);
                          foreach ($items as $item){
                              $itemData[] = [
                                  '@id' => "/api/get/item/" . $item->getId(),
                                  '@type' => "Item",
                                  'id'=> $item ->getId(),
                                  'name'=> $item->getName(),
                                  'reference'=> $item->getReference(),
                                  'price'=> $item->getPrice(),
                                  'barcode'=> $item->getBarcode(),
                                  'salePrice'=> $item->getSalePrice(),
                                  'unit' => [
                                      '@type' => "Unit",
                                      'id' => $item->getUnit() ? $item->getUnit()->getId() : '',
                                      'name' => $item->getUnit() ? $item->getUnit()->getName() : '',
                                  ],
                                  'itemType' => [
                                      '@type' => "ItemType",
                                      'id' => $item->getItemType() ? $item->getItemType()->getId() : '',
                                      'name' => $item->getItemType() ? $item->getItemType()->getName() : '',
                                  ],
                                  'itemCategory' => [
                                      '@type' => "ItemCategory",
                                      'id' => $item->getItemCategory() ? $item->getItemCategory()->getId() : '',
                                      'name' => $item->getItemCategory() ? $item->getItemCategory()->getName() : '',
                                  ],
                                  'branch' => [
                                      '@id' => "/api/get/branch/" . $item->getId(),
                                      '@type' => "Branch",
                                      'id' => $item->getBranch() ? $item->getBranch()->getId() : '',
                                      'code' => $item->getBranch() ? $item->getBranch()->getCode() : '',
                                      'name' => $item->getBranch() ? $item->getBranch()->getName() : '',
                                  ],
                              ];
                          }
                       }
               }
               else {
                   $items = $this->itemRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

                   foreach ($items as $item) {
                       if ($item) {
                           $itemData[] = [
                               '@id' => "/api/get/item/" . $item->getId(),
                               '@type' => "Item",
                               'id'=> $item ->getId(),
                               'name'=> $item->getName(),
                               'reference'=> $item->getReference(),
                               'price'=> $item->getPrice(),
                               'barcode'=> $item->getBarcode(),
                               'salePrice'=> $item->getSalePrice(),
                               'unit' => [
                                   '@type' => "Unit",
                                   'id' => $item->getUnit() ? $item->getUnit()->getId() : '',
                                   'name' => $item->getUnit() ? $item->getUnit()->getName() : '',
                               ],
                               'itemType' => [
                                   '@type' => "ItemType",
                                   'id' => $item->getItemType() ? $item->getItemType()->getId() : '',
                                   'name' => $item->getItemType() ? $item->getItemType()->getName() : '',
                               ],
                               'itemCategory' => [
                                   '@type' => "ItemCategory",
                                   'id' => $item->getItemCategory() ? $item->getItemCategory()->getId() : '',
                                   'name' => $item->getItemCategory() ? $item->getItemCategory()->getName() : '',
                               ],
                               'branch' => [
                                   '@id' => "/api/get/branch/" . $item->getId(),
                                   '@type' => "Branch",
                                   'id' => $item->getBranch() ? $item->getBranch()->getId() : '',
                                   'code' => $item->getBranch() ? $item->getBranch()->getCode() : '',
                                   'name' => $item->getBranch() ? $item->getBranch()->getName() : '',
                               ],
                           ];
                       }
                   }

               }
            }
        }


        return $this->json(['hydra:member' => $itemData]);
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
