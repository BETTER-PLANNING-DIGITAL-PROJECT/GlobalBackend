<?php

namespace App\Controller\Product;

use App\Entity\Product\Item;
use App\Repository\Product\ItemCategoryRepository;
use App\Repository\Product\ItemRepository;
use App\Repository\Product\ItemTypeRepository;
use App\Repository\Product\UnitRepository;
use App\Repository\Security\Institution\BranchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class ImportItemController extends AbstractController
{
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

    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(Request $request,
                             ItemRepository $itemRepository,
                             ItemTypeRepository $itemTypeRepository,
                             ItemCategoryRepository $itemCategoryRepository,
                             UnitRepository $unitRepository,
                             BranchRepository $branchRepository): Response
    {
        $data = $this->jsondecode();

        if(!isset($data->data))
            return throw new BadRequestHttpException('file is compulsory');

        $items = $data->data;

        if (!$items) {
            throw new BadRequestHttpException('"file" is required');
        }

        foreach ($items as $item)
        {
            // branch
            if(isset($item->branch))
            {
                $itemBranch = $branchRepository->findOneBy(['code' => $item->branch]);
                if(!$itemBranch)
                {
                    return new JsonResponse(['hydra:title' => 'Branch not found in line '. $item->line],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                return new JsonResponse(['hydra:title' => 'Branch code empty in line '. $item->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            // reference
            if(!isset($item->reference))
            {
                return new JsonResponse(['hydra:title' => 'Item reference empty in line '. $item->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            else{
                // check if reference already exist
                $itemReference = $itemRepository->findOneBy(['reference' => $item->reference, 'branch' => $itemBranch, 'institution' => $this->getUser()->getInstitution()]);
                if($itemReference)
                {
                    return new JsonResponse(['hydra:title' => 'Item reference: '.$itemReference->getReference(). ' in line '. $item->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }

            // name
            if(!isset($item->name))
            {
                return new JsonResponse(['hydra:title' => 'Item name empty in line '. $item->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            else{
                // check if name already exist
                $itemName = $itemRepository->findOneBy(['name' => $item->name, 'branch' => $itemBranch, 'institution' => $this->getUser()->getInstitution()]);
                if($itemName)
                {
                    return new JsonResponse(['hydra:title' => 'Item name: '.$itemName->getName(). ' in line '. $item->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }

            // price
            if(isset($item->price))
            {
                $price = floatval($item->price);
            }
            else{
                $price = null;
            }

            // barcode
            if(isset($item->barcode))
            {
                $barcode = intval($item->barcode);

                // check if barcode already exist
                $itemBarcode = $itemRepository->findOneBy(['barcode' => $item->barcode, 'branch' => $itemBranch, 'institution' => $this->getUser()->getInstitution()]);
                if($itemBarcode)
                {
                    return new JsonResponse(['hydra:title' => 'Item barcode: '.$itemBarcode->getBarcode(). ' in line '. $item->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $barcode = null;
            }

            // item type
            if(isset($item->type))
            {
                $itemType = $itemTypeRepository->findOneBy(['name' => $item->type]);
                if(!$itemType)
                {
                    return new JsonResponse(['hydra:title' => 'Item type not found in line '. $item->line],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                return new JsonResponse(['hydra:title' => 'Item type name empty in line '. $item->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            // item category
            if(isset($item->category))
            {
                $itemCategory = $itemCategoryRepository->findOneBy(['name' => $item->category]);
                if(!$itemCategory)
                {
                    return new JsonResponse(['hydra:title' => 'Item category not found in line '. $item->line],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $itemCategory = null;
            }

            // unit
            if(isset($item->unit))
            {
                $itemUnit = $unitRepository->findOneBy(['name' => $item->unit]);
                if(!$itemUnit)
                {
                    return new JsonResponse(['hydra:title' => 'Item Unit not found in line '. $item->line],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $itemUnit = null;
            }

            $newItem = new Item();

            $newItem->setReference($item->reference);
            $newItem->setName($item->name);
            $newItem->setPrice($price);
            $newItem->setBarcode($barcode);
            $newItem->setItemType($itemType);
            $newItem->setItemCategory($itemCategory);
            $newItem->setUnit($itemUnit);

            $newItem->setIsPurchase(false);
            $newItem->setIsSale(true);
            $newItem->setIsRent(false);

            $newItem->setIsPos(true);

            $newItem->setBranch($itemBranch);
            $newItem->setInstitution($this->getUser()->getInstitution());
            $newItem->setYear($this->getUser()->getCurrentYear());
            $newItem->setUser($this->getUser());
            $newItem->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($newItem);
        }

        $this->entityManager->flush();

        return new Response(null, Response::HTTP_OK);

        //return $this->json(['hydra:member' => $this->roleResultName($profile->getName())]);
    }

}