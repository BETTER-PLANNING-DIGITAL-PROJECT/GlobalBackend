<?php

namespace App\Controller\Billing\Pos;

use App\Entity\Billing\Sale\SaleInvoiceItem;
use App\Entity\Billing\Sale\SaleInvoiceItemStock;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
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
class SearchItemBarcodeController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('api/create/sale/invoice/{id}/item/barcode', name: 'create_sale_invoice_item_barcode')]
    public function searchItem($id, ItemRepository $itemRepository,
                               SaleInvoiceRepository $saleInvoiceRepository,
                               SaleInvoiceItemRepository $saleInvoiceItemRepository,
                               SaleInvoiceItemStockRepository $saleInvoiceItemStockRepository, StockRepository $stockRepository, Request $request, $type = null, $category = null): JsonResponse
    {
        $request = Request::createFromGlobals();

        $invoiceData = json_decode($request->getContent(), true);

        //$id = $request->get('id');

        $saleInvoice = $saleInvoiceRepository->findOneBy(['id' => $id]);
        if (!$saleInvoice){
            return new JsonResponse(['hydra:description' => 'This sale invoice '.$id.' is not found.'], 404);
        }

        $barcode = $invoiceData['barcode'];

        if (!is_numeric($barcode)){
            return new JsonResponse(['hydra:description' => 'Invalid barcode.'], 404);
        }

        // find if item exist
        $item = $itemRepository->findOneBy(['barcode' => $barcode], ['id' => 'ASC']);
        if (!$item){
            return new JsonResponse(['hydra:description' => 'Item not found.'], 404);
        }

        $pu = $item->getPrice();
        $branch = $this->getUser()->getBranch();

        if(!$item->getItemCategory())
        {
            // FIFO
            $stock = $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreAsc($item,$branch) ? $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreAsc($item,$branch)[0] : null;
        }
        else{
            if(!$item->getItemCategory()->getStockStrategy())
            {
                // FIFO
                $stock = $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreAsc($item,$branch) ? $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreAsc($item,$branch)[0] : null;
            }
            else{
                // get the outStrategy
                $outStrategy = $item->getItemCategory()->getStockStrategy()->getCode();
                if($outStrategy == 'FIFO')
                {
                    // FIFO
                    $stock = $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreAsc($item,$branch) ? $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreAsc($item,$branch)[0] : null;
                }
                else{
                    // LIFO
                    $stock = $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreDesc($item,$branch) ? $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreDesc($item,$branch)[0] : null;
                }
            }
        }

        // FIFO
        // $stock = $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreAsc($item,$branch) ? $stockRepository->findOneAvailableStockGreaterThanZeroByItemStoreAsc($item,$branch)[0] : '';

        if (!$stock){
            return new JsonResponse(['hydra:description' => 'Stock not found.'], 404);
        }

        // find if this stock is already in the sale
        // $stockExist = $saleInvoiceItemStockRepository->findOneBy(['item' => $item, 'saleInvoice' => $saleInvoice]);

        $saleInvoiceItem = new SaleInvoiceItem();

        $saleInvoiceItem->setItem($stock->getItem());
        $saleInvoiceItem->setQuantity(1);
        $saleInvoiceItem->setPu($pu);
        // discount
        $saleInvoiceItem->setSaleInvoice($saleInvoice);
        // name
        $saleInvoiceItem->setAmount($pu);

        $saleInvoiceItem->setUser($this->getUser());
        $saleInvoiceItem->setBranch($this->getUser()->getBranch());
        $saleInvoiceItem->setInstitution($this->getUser()->getInstitution());
        $saleInvoiceItem->setYear($this->getUser()->getCurrentYear());

        $this->entityManager->persist($saleInvoiceItem);

        // sale invoice item stock
        $saleInvoiceItemStock = new SaleInvoiceItemStock();

        $saleInvoiceItemStock->setSaleInvoiceItem($saleInvoiceItem);
        $saleInvoiceItemStock->setStock($stock);
        $saleInvoiceItemStock->setQuantity(1);
        $saleInvoiceItemStock->setCreatedAt(new \DateTimeImmutable());
        $saleInvoiceItemStock->setUser($this->getUser());
        $saleInvoiceItemStock->setYear($this->getUser()->getCurrentYear());
        $saleInvoiceItemStock->setBranch($this->getUser()->getBranch());
        $saleInvoiceItemStock->setInstitution($this->getUser()->getInstitution());

        $this->entityManager->persist($saleInvoiceItemStock);

        $this->entityManager->flush();

        return $this->json(['hydra:description' => '200']);
    }

    #[Route('api/search/sale/invoice/{id}/item/name/reference', name: 'search_sale_invoice_item_name_reference')]
    public function searchByNameReference($id, SaleInvoiceRepository $saleInvoiceRepository, SaleInvoiceItemRepository $saleInvoiceItemRepository, SaleInvoiceItemStockRepository $saleInvoiceItemStockRepository, StockRepository $stockRepository, Request $request, $type = null, $category = null): JsonResponse
    {
        $request = Request::createFromGlobals();

        $invoiceData = json_decode($request->getContent(), true);

        //$id = $request->get('id');

        $saleInvoice = $saleInvoiceRepository->findOneBy(['id' => $id]);
        if (!$saleInvoice){
            return new JsonResponse(['hydra:description' => 'This invoice '.$id.' is not found.'], 404);
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
