<?php
namespace App\State\Processor\Billing\Sale\School;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleInvoice;
use App\Repository\Billing\Sale\SaleInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class DeleteSchoolSaleInvoiceProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $manager,
                                private readonly SaleInvoiceFeeRepository $saleInvoiceFeeRepository,
                                Private readonly SaleInvoiceRepository $saleInvoiceRepository) {
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

        $saleInvoiceFees = $this->saleInvoiceFeeRepository->findBy(['saleInvoice'=> $saleInvoice]);
        if($saleInvoiceFees)
        {
            foreach ($saleInvoiceFees as $saleInvoiceFee)
            {
                $this->manager->remove($saleInvoiceFee);
            }
        }

        $this->manager->remove($saleInvoice);
        $this->manager->flush();

        //return $this->processor->process($saleInvoice, $operation, $uriVariables, $context);
    }
}
