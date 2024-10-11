<?php

namespace App\Controller\Billing\Sale;

use App\Entity\Security\User;
use App\Repository\Inventory\StockRepository;
use App\Repository\Product\ItemCategoryRepository;
use App\Repository\Product\ItemRepository;
use App\Repository\Product\ItemTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetItemByItemTypeCategoryController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    #[Route('api/get/item/by/item/type/{type}', name: 'get_item_by_item_type')]
    #[Route('api/get/item/by/item/category/{category}', name: 'get_item_by_item_category')]
    #[Route('api/get/item/by/item/type/{type}/category/{category}', name: 'get_item_by_item_type_category')]
    public function __invoke(ItemRepository $itemRepository, ItemTypeRepository $itemTypeRepository, ItemCategoryRepository $itemCategoryRepository, StockRepository $stockRepository, Request $request, $type = null, $category = null): JsonResponse
    {
        $routeName = $request->attributes->get('_route');

        if ($routeName == 'get_item_by_item_type'){
            $typeObject = $itemTypeRepository->find($type);
            $items = $itemRepository->findBy(['itemType' => $typeObject]);
        }
        elseif ($routeName == 'get_item_by_item_category'){
            $categoryObject = $itemCategoryRepository->find($category);
            $items = $itemRepository->findBy(['itemCategory' => $categoryObject]);
        }
        elseif ($routeName == 'get_item_by_item_type_category')
        {
            $typeObject = $itemTypeRepository->find($type);
            $categoryObject = $itemCategoryRepository->find($category);
            $items = $itemRepository->findBy(['itemType' => $typeObject, 'itemCategory' => $categoryObject]);
        }
        else {
            return new JsonResponse(['hydra:description' => 'Query cannot continue parameters are mandatory.'], 404);
        }

        $all = [];

        foreach ($items as $item){

            $all[] = [
                'id' => $item->getId(),
                '@id' => '/api/get/item/'. $item->getId(),

                'reference' => $item->getReference(),
                'name' => $item->getName(),
                'barcode' => $item->getBarcode(),
                'price' => $item->getPrice(),
                'itemType' => $item->getItemType(),
                'itemCategory' => $item->getItemCategory() ? $item->getItemCategory() : '',

            ];

        }

        return $this->json(['hydra:member' => $all]);
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
