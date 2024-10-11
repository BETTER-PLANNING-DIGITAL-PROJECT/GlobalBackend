<?php
namespace App\State\Processor\Billing\Sale;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleInvoice;
use App\Repository\Billing\Sale\SaleInvoiceItemDiscountRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemTaxRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class DeleteSaleInvoiceProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $manager,
                                private readonly SaleInvoiceItemRepository $saleInvoiceItemRepository,
                                private readonly SaleInvoiceItemDiscountRepository $saleInvoiceItemDiscountRepository,
                                private readonly SaleInvoiceItemTaxRepository $saleInvoiceItemTaxRepository,
                                private readonly SaleInvoiceItemStockRepository $saleInvoiceItemStockRepository,
                                Private readonly SaleInvoiceRepository $saleInvoiceRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if(!$data instanceof SaleInvoice)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of sale invoice.'], 404);
        }

        $saleInvoice = $this->saleInvoiceRepository->find($data->getId());
        if(!$saleInvoice)
        {
            return new JsonResponse(['hydra:description' => 'Sale Invoice not found.'], 404);
        }

        $saleInvoiceItems = $this->saleInvoiceItemRepository->findBy(['saleInvoice' => $saleInvoice]);
        if($saleInvoiceItems)
        {
            foreach ($saleInvoiceItems as $saleInvoiceItem)
            {
                // clear sale invoice item discount
                $saleInvoiceItemDiscounts = $this->saleInvoiceItemDiscountRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);
                if ($saleInvoiceItemDiscounts){
                    foreach ($saleInvoiceItemDiscounts as $saleInvoiceItemDiscount){
                        $this->manager->remove($saleInvoiceItemDiscount);
                    }
                }

                // clear sale invoice item tax
                $saleInvoiceItemTaxes = $this->saleInvoiceItemTaxRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);
                if ($saleInvoiceItemTaxes){
                    foreach ($saleInvoiceItemTaxes as $saleInvoiceItemTax){
                        $this->manager->remove($saleInvoiceItemTax);
                    }
                }

                // clear sale invoice item stock
                $saleInvoiceItemStocks = $this->saleInvoiceItemStockRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);
                if ($saleInvoiceItemStocks){
                    foreach ($saleInvoiceItemStocks as $saleInvoiceItemStock){
                        $this->manager->remove($saleInvoiceItemStock);
                    }
                }

                $this->manager->remove($saleInvoiceItem);
            }
        }

        $this->manager->remove($saleInvoice);
        $this->manager->flush();

        //return $this->processor->process($saleInvoice, $operation, $uriVariables, $context);
    }
}
