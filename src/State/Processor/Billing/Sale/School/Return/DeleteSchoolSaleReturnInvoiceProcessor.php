<?php
namespace App\State\Processor\Billing\Sale\School\Return;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Repository\Billing\Sale\SaleReturnInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class DeleteSchoolSaleReturnInvoiceProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $manager,
                                private readonly SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                                private readonly SaleReturnInvoiceFeeRepository $saleReturnInvoiceFeeRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        if(!$data instanceof SaleReturnInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This data must be type of invoice.'], 404);
        }

        $returnInvoice = $this->saleReturnInvoiceRepository->find($data->getId());

        if(!$returnInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }

        $saleInvoiceFees = $this->saleReturnInvoiceFeeRepository->findBy(['saleReturnInvoice'=> $returnInvoice]);
        if($saleInvoiceFees){
            foreach ($saleInvoiceFees as $saleInvoiceFee){
                $this->manager->remove($saleInvoiceFee);
            }
        }

        $this->manager->remove($returnInvoice);
        $this->manager->flush();

        //return $this->processor->process($saleInvoice, $operation, $uriVariables, $context);
    }
}
