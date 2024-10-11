<?php
namespace App\State\Processor\Billing\Sale;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemDiscountRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemTaxRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class DeleteSaleReturnInvoiceProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $manager,
                                private readonly SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                                private readonly SaleReturnInvoiceItemDiscountRepository $saleReturnInvoiceItemDiscountRepository,
                                private readonly SaleReturnInvoiceItemTaxRepository $saleReturnInvoiceItemTaxRepository,
                                private readonly SaleReturnInvoiceItemStockRepository $saleReturnInvoiceItemStockRepository,
                                private readonly SaleReturnInvoiceItemRepository $saleReturnInvoiceItemRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if(!$data instanceof SaleReturnInvoice)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of return invoice.'], 404);
        }

        $saleReturnInvoice = $this->saleReturnInvoiceRepository->find($data->getId());
        if(!$saleReturnInvoice)
        {
            return new JsonResponse(['hydra:description' => 'Sale Return Invoice not found.'], 404);
        }

        $saleReturnInvoiceItems = $this->saleReturnInvoiceItemRepository->findBy(['saleReturnInvoice'=> $saleReturnInvoice]);
        if($saleReturnInvoiceItems)
        {
            foreach ($saleReturnInvoiceItems as $saleReturnInvoiceItem)
            {
                // clear sale return invoice item discount
                $saleReturnInvoiceItemDiscounts = $this->saleReturnInvoiceItemDiscountRepository->findBy(['saleReturnInvoiceItem' => $saleReturnInvoiceItem]);
                if ($saleReturnInvoiceItemDiscounts){
                    foreach ($saleReturnInvoiceItemDiscounts as $saleReturnInvoiceItemDiscount){
                        $this->manager->remove($saleReturnInvoiceItemDiscount);
                    }
                }

                // clear sale return invoice item tax
                $saleReturnInvoiceItemTaxes = $this->saleReturnInvoiceItemTaxRepository->findBy(['saleReturnInvoiceItem' => $saleReturnInvoiceItem]);
                if ($saleReturnInvoiceItemTaxes){
                    foreach ($saleReturnInvoiceItemTaxes as $saleReturnInvoiceItemTax){
                        $this->manager->remove($saleReturnInvoiceItemTax);
                    }
                }

                // clear sale return invoice item stock
                $saleReturnInvoiceItemStocks = $this->saleReturnInvoiceItemStockRepository->findBy(['saleReturnInvoiceItem' => $saleReturnInvoiceItem]);
                if ($saleReturnInvoiceItemStocks){
                    foreach ($saleReturnInvoiceItemStocks as $saleReturnInvoiceItemStock){
                        $this->manager->remove($saleReturnInvoiceItemStock);
                    }
                }

                $this->manager->remove($saleReturnInvoiceItem);
            }
        }

        $this->manager->remove($saleReturnInvoice);
        $this->manager->flush();

        //return $this->processor->process($saleInvoice, $operation, $uriVariables, $context);
    }
}
