<?php
namespace App\State\Processor\Billing\Sale\School\Return;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CancelSchoolSaleReturnInvoiceProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $manager,
                                Private readonly SaleReturnInvoiceRepository $saleReturnInvoiceRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        if(!$data instanceof SaleReturnInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This data must be type of return invoice.'], 404);
        }

        $saleReturnInvoice = $this->saleReturnInvoiceRepository->find($data->getId());

        if(!$saleReturnInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This return invoice is not found.'], 404);
        }

        $saleReturnInvoice->setStatus('draft');
        $this->manager->flush();

        return $this->processor->process($saleReturnInvoice, $operation, $uriVariables, $context);
    }
}
