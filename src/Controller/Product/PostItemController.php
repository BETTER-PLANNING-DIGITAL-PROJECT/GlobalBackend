<?php

namespace App\Controller\Product;

use App\Entity\Product\Item;
use App\Entity\Security\User;
use App\Repository\Product\ItemCategoryRepository;
use App\Repository\Product\ItemRepository;
use App\Repository\Product\ItemTypeRepository;
use App\Repository\Product\UnitRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PostItemController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data, Request $request, ItemRepository $itemRepository, ItemTypeRepository $itemTypeRepository, ItemCategoryRepository $itemCategoryRepository, UnitRepository $unitRepository, BranchRepository $branchRepository,
    SystemSettingsRepository $systemSettingsRepository)
    {
        $itemData = json_decode($request->getContent(), true);

        $systemSettings = $systemSettingsRepository->findOneBy([]);
        $reference = $itemData['reference'];
        $name = $itemData['name'];
        $barcode = $itemData['barcode'];
        // $branch = !isset($itemData['branch']) ? null : $branchRepository->find($this->getIdFromApiResourceId($itemData['branch']));

        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                if (!isset($itemData['branch'])) {
                    return new JsonResponse(['hydra:description' => 'Branch not found!.'], 400);
                }
                $branch = $branchRepository->find($this->getIdFromApiResourceId($itemData['branch']));
            }
            else{
                $branch = $this->getUser()->getBranch();
            }
        }
        else{
            $branch = $this->getUser()->getBranch();
        }

        // Check for duplicates based on reference within the same branch
        $duplicateCheckReference = $itemRepository->findOneBy(['reference' => $reference, 'branch' => $branch]);
        if ($duplicateCheckReference) {
            return new JsonResponse(['hydra:description' => 'This reference already exists in this branch.'], 400);
        }

        // Check for duplicates based on name within the same branch
        $duplicateCheckName = $itemRepository->findOneBy(['name' => $name, 'branch' => $branch]);
        if ($duplicateCheckName) {
            return new JsonResponse(['hydra:description' => 'This item already exists in this branch.'], 400);
        }

        if(isset($barcode)) {
            // Check for duplicates based on barcode within the same branch
            $duplicateCheckBarcode = $itemRepository->findOneBy(['barcode' => $barcode, 'branch' => $branch]);
            if ($duplicateCheckBarcode) {
                return new JsonResponse(['hydra:description' => 'This barcode already exists in this branch.'], 400);
            }
        }

        // Create a new item
        $newItem = new Item();
        $newItem->setReference($itemData['reference']);
        $newItem->setName($itemData['name']);
        $newItem->setPrice($itemData['price']);
        $newItem->setBarcode($itemData['barcode']);
        $itemType = !isset($itemData['itemType']) ? null : $itemTypeRepository->find($this->getIdFromApiResourceId($itemData['itemType']));
        $newItem->setItemType($itemType);
        $itemCategory = !isset($itemData['itemCategory']) ? null : $itemCategoryRepository->find($this->getIdFromApiResourceId($itemData['itemCategory']));
        $newItem->setItemCategory($itemCategory);
        $unit = !isset($itemData['unit']) ? null : $unitRepository->find($this->getIdFromApiResourceId($itemData['unit']));
        $newItem->setUnit($unit);
        /*if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $newItem->setBranch($branch);
            } else {
                $newItem->setBranch($this->getUser()->getBranch());
            }
        }*/
        $newItem->setIsPos($itemData['is_pos']);
        $newItem->setIsPurchase($itemData['isPurchase']);
        $newItem->setIsSale($itemData['isSale']);

        $newItem->setBranch($branch);
        $newItem->setInstitution($this->getUser()->getInstitution());
        $newItem->setUser($this->getUser());
        $newItem->setYear($this->getUser()->getCurrentYear());

        $itemRepository->save($newItem);

        return $newItem;
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
