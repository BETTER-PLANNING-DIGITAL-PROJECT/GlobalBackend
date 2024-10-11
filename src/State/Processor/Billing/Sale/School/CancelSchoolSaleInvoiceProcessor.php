<?php
namespace App\State\Processor\Billing\Sale\School;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleInvoice;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CancelSchoolSaleInvoiceProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $manager,
                                Private readonly SaleInvoiceRepository $saleInvoiceRepository,
                                Private readonly SaleInvoiceItemRepository $saleInvoiceItemRepository,
                                Private readonly SaleInvoiceItemStockRepository $saleInvoiceItemStockRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if(!$data instanceof SaleInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This data must be type of invoice.'], 404);
        }

        $saleInvoice = $this->saleInvoiceRepository->find($data->getId());
        if(!$saleInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'Invoice not found.'], 404);
        }

        $saleInvoice->setStatus('draft');

        // Validate sale invoice item stock : reserved quantity
        $saleInvoiceItems = $this->saleInvoiceItemRepository->findBy(['saleInvoice' => $saleInvoice]);
        foreach ($saleInvoiceItems as $saleInvoiceItem)
        {
            // Get sale invoice item stock
            $saleInvoiceItemStocks = $this->saleInvoiceItemStockRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);

            if($saleInvoiceItemStocks)
            {
                foreach ($saleInvoiceItemStocks as $saleInvoiceItemStock){

                    $stock = $saleInvoiceItemStock->getStock();

                    $stock->setReserveQte($stock->getReserveQte() - $saleInvoiceItemStock->getQuantity());
                    $stock->setAvailableQte($stock->getAvailableQte() + $saleInvoiceItemStock->getQuantity());
                    $stock->setQuantity(($stock->getAvailableQte() + $saleInvoiceItemStock->getQuantity()) + ($stock->getReserveQte() - $saleInvoiceItemStock->getQuantity()));

                }

            }

        }

        $this->manager->flush();

        return $this->processor->process($saleInvoice, $operation, $uriVariables, $context);
    }
}
