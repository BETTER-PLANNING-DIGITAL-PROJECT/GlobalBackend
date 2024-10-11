<?php

namespace App\Controller\Billing\Pos;

use App\Entity\Billing\Sale\SaleReturnInvoiceItem;
use App\Entity\Billing\Sale\SaleReturnInvoiceItemStock;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use App\Repository\Inventory\StockRepository;
use App\Repository\Product\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class SearchItemReturnBarcodeController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $entityManager)
    {
    }


    #[Route('api/create/sale/return/invoice/{id}/item/barcode', name: 'create_sale_return_invoice_item_barcode')]
    public function searchItem($id, ItemRepository $itemRepository, SaleReturnInvoiceRepository $saleReturnInvoiceRepository, SaleReturnInvoiceItemRepository $saleReturnInvoiceItemRepository, SaleReturnInvoiceItemStockRepository $saleReturnInvoiceItemStockRepository, StockRepository $stockRepository): JsonResponse
    {
        $request = Request::createFromGlobals();

        $invoiceData = json_decode($request->getContent(), true);

        //$id = $request->get('id');

        $saleReturnInvoice = $saleReturnInvoiceRepository->findOneBy(['id' => $id]);
        if (!$saleReturnInvoice){
            return new JsonResponse(['hydra:description' => 'This return invoice '.$id.' is not found.'], 404);
        }

        //$barcode = $request->get('barcode');
        $barcode = $invoiceData['barcode'];

        if(is_numeric($barcode))
        {
            // find if item exist
            $item = $itemRepository->findOneBy(['barcode' => $barcode], ['id' => 'ASC']);
            if (!$item){
                return new JsonResponse(['hydra:description' => 'Item not found.'], 404);
            }

            $amount = $item->getPrice();

            if(!$item->getItemCategory())
            {
                // FIFO
                //$stock = $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreAsc($item) ? $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreAsc($item)[0] : '';
                $stock = $stockRepository->findOneBy(['item' => $item], ['id' => 'ASC']);
            }
            else{
                if(!$item->getItemCategory()->getStockStrategy())
                {
                    // FIFO
                    //$stock = $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreAsc($item) ? $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreAsc($item)[0] : '';
                    $stock = $stockRepository->findOneBy(['item' => $item], ['id' => 'ASC']);
                }
                else{
                    // get the outStrategy
                    $outStrategy = $item->getItemCategory()->getStockStrategy()->getCode();
                    if($outStrategy == 'FIFO')
                    {
                        // FIFO
                        //$stock = $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreAsc($item) ? $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreAsc($item)[0] : '';
                        $stock = $stockRepository->findOneBy(['item' => $item], ['id' => 'ASC']);
                    }
                    else{
                        // LIFO
                        //$stock = $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreDesc($item) ? $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreDesc($item)[0] : '';
                        $stock = $stockRepository->findOneBy(['item' => $item], ['id' => 'DESC']);
                    }
                }
            }


            // CHECK IF THAT ITEM IS ALREADY IN CURRENT SALE INVOICE
//            $saleReturnInvoiceItem = $saleReturnInvoiceItemRepository->findOneBy(['item' => $item, 'saleReturnInvoice' => $saleReturnInvoice]);
//            if($saleReturnInvoiceItem)
//            {
//                // sale invoice item
//                $saleReturnInvoiceItem->setQuantity($saleReturnInvoiceItem->getQuantity() + 1);
//                $saleReturnInvoiceItem->setAmount($saleReturnInvoiceItem->getAmount() + $amount);
//                $saleReturnInvoiceItem->setAmountTtc($saleReturnInvoiceItem->getAmountTtc() + $amount);
//                $saleReturnInvoiceItem->setAmountWithTaxes($saleReturnInvoiceItem->getAmountWithTaxes() + $amount);
//
//                // get sale invoice item stock
//                $previoussaleReturnInvoiceItemStock = $saleReturnInvoiceItemStockRepository->findOneBy(['saleReturnInvoiceItem' => $saleReturnInvoiceItem]);
//                if(!$previoussaleReturnInvoiceItemStock)
//                {
//                    return new JsonResponse(['hydra:description' => 'Please delete that item then add again.'], 404);
//                }
//
//                $previousStock = $previoussaleReturnInvoiceItemStock->getStock();
//
//                if($stock == $previousStock)
//                {
//                    // sale invoice item stock
//                    $previoussaleReturnInvoiceItemStock->setQuantity($previoussaleReturnInvoiceItemStock->getQuantity() + 1);
//                }
//                else{
//                    // sale invoice item stock
//                    $saleReturnInvoiceItemStock = new saleReturnInvoiceItemStock();
//                    $saleReturnInvoiceItemStock->setsaleReturnInvoiceItem($saleReturnInvoiceItem);
//                    $saleReturnInvoiceItemStock->setStock($stock);
//                    $saleReturnInvoiceItemStock->setQuantity(1);
//                    $saleReturnInvoiceItemStock->setCreatedAt(new \DateTimeImmutable());
//                    $saleReturnInvoiceItemStock->setUser($this->getUser());
//                    $saleReturnInvoiceItemStock->setYear($this->getUser()->getCurrentYear());
//                    $saleReturnInvoiceItemStock->setBranch($this->getUser()->getBranch());
//                    $saleReturnInvoiceItemStock->setInstitution($this->getUser()->getInstitution());
//
//                    $this->entityManager->persist($saleReturnInvoiceItemStock);
//                }
//            }
//            else
//            {
                if (!$stock){
                    //return new JsonResponse(['hydra:description' => 'Stock not found.'], 404);
                    return new JsonResponse(['hydra:description' => 'No already stock exist for this item.'], 404);
                }

                // sale invoice item
                $saleReturnInvoiceItem = new SaleReturnInvoiceItem();

                $saleReturnInvoiceItem->setItem($stock->getItem());
                $saleReturnInvoiceItem->setQuantity(1);
                $saleReturnInvoiceItem->setPu($amount);
                // $saleReturnInvoiceItem->setDiscount($invoiceData['discount']);
                $saleReturnInvoiceItem->setsaleReturnInvoice($saleReturnInvoice);
                // $saleReturnInvoiceItem->setName($invoiceData['name']);
                $saleReturnInvoiceItem->setAmount($amount);


                // taxes
                // discount

                // $saleReturnInvoiceItem->setDiscountAmount($discountAmount);
                // $saleReturnInvoiceItem->setAmountTtc($saleReturnInvoiceItem->getAmount() + $taxResult - $discountAmount);
                $saleReturnInvoiceItem->setAmountTtc($amount);
                $saleReturnInvoiceItem->setAmountWithTaxes($amount);

                $saleReturnInvoiceItem->setUser($this->getUser());
                $saleReturnInvoiceItem->setBranch($this->getUser()->getBranch());
                $saleReturnInvoiceItem->setInstitution($this->getUser()->getInstitution());
                $saleReturnInvoiceItem->setYear($this->getUser()->getCurrentYear());

                $this->entityManager->persist($saleReturnInvoiceItem);


                // sale invoice item stock
                $saleReturnInvoiceItemStock = new saleReturnInvoiceItemStock();
                $saleReturnInvoiceItemStock->setSaleReturnInvoiceItem($saleReturnInvoiceItem);
                $saleReturnInvoiceItemStock->setStock($stock);
                $saleReturnInvoiceItemStock->setQuantity(1);
                $saleReturnInvoiceItemStock->setCreatedAt(new \DateTimeImmutable());
                $saleReturnInvoiceItemStock->setUser($this->getUser());
                $saleReturnInvoiceItemStock->setYear($this->getUser()->getCurrentYear());
                $saleReturnInvoiceItemStock->setBranch($this->getUser()->getBranch());
                $saleReturnInvoiceItemStock->setInstitution($this->getUser()->getInstitution());

                $this->entityManager->persist($saleReturnInvoiceItemStock);
//            }

            $this->entityManager->flush();
        }

        return $this->json(['hydra:description' => '200']);
    }

    #[Route('api/search/sale/return/invoice/{id}/item/name/reference', name: 'search_sale_return_invoice_item_name_reference')]
    public function searchByNameReference($id, SaleReturnInvoiceRepository $saleReturnInvoiceRepository, SaleReturnInvoiceItemRepository $saleReturnInvoiceItemRepository, SaleReturnInvoiceItemStockRepository $saleReturnInvoiceItemStockRepository, StockRepository $stockRepository, Request $request, $type = null, $category = null): JsonResponse
    {
        $request = Request::createFromGlobals();

        $invoiceData = json_decode($request->getContent(), true);

        //$id = $request->get('id');

        $saleReturnInvoice = $saleReturnInvoiceRepository->findOneBy(['id' => $id]);
        if (!$saleReturnInvoice){
            return new JsonResponse(['hydra:description' => 'This return invoice '.$id.' is not found.'], 404);
        }

        //$barcode = $request->get('barcode');
        $criteria = $invoiceData['barcode'];

        $all = [];

        // Find Item In stock in current POS Location With BarCode LIKE CRITERIA
        $foundItems = $stockRepository->searchItemInStockWithBarcodeLikeNameReference($criteria, 10);

        if($foundItems)
        {
            foreach ($foundItems as $item){
                $all[] = [
                    'id' => $item->getItem()->getId(),
                    '@id' => '/api/get/item/'. $item->getItem()->getId(),
                    'name' => $item->getItem()->getName(),
                    'barcode' => $item->getItem()->getBarcode(),
                    'position' => $item->getItem()->getPosition(),
                    'price' => $item->getItem()->getPrice(),
                    'itemType' => $item->getItem()->getItemType(),
                    'itemCategory' => $item->getItem()->getItemCategory(),
                    'reference' => $item->getItem()->getReference(),
                    'batchNumber' => $item->getItem()->getBatchNumber(),

                ];

            }

            //return $this->json(['hydra:member' => $all]);
        }
        else
        {
            return new JsonResponse(['hydra:description' => 'No item found for this criteria.'], 404);

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
