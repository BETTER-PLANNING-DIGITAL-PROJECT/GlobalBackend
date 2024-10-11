<?php
namespace App\State\Processor\Billing\Purchase;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Purchase\PurchaseReturnInvoice;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CancelPurchaseReturnInvoiceProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $manager,
                                Private readonly PurchaseReturnInvoiceRepository $purchaseReturnInvoiceRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if(!$data instanceof PurchaseReturnInvoice)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of return invoice.'], 404);
        }

        $purchaseReturnInvoice = $this->purchaseReturnInvoiceRepository->find($data->getId());
        if(!$purchaseReturnInvoice)
        {
            return new JsonResponse(['hydra:description' => 'Purchase Return Invoice not found.'], 404);
        }

        $purchaseReturnInvoice->setStatus('draft');
        $this->manager->flush();

        return $this->processor->process($purchaseReturnInvoice, $operation, $uriVariables, $context);
    }
}
