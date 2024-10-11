<?php
namespace App\State\Processor\Billing\Sale;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleInvoice;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CancelSaleInvoiceProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $manager,
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

        $saleInvoice->setStatus('draft');

        $this->manager->flush();

        return $this->processor->process($saleInvoice, $operation, $uriVariables, $context);
    }
}
