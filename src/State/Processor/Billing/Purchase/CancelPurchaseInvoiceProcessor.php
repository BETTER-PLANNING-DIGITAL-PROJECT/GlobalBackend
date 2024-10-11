<?php
namespace App\State\Processor\Billing\Purchase;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Purchase\PurchaseInvoice;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CancelPurchaseInvoiceProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $manager,
                                Private readonly PurchaseInvoiceRepository $purchaseInvoiceRepository
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if(!$data instanceof PurchaseInvoice)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of purchase invoice.'], 404);
        }

        $purchaseInvoice = $this->purchaseInvoiceRepository->find($data->getId());
        if(!$purchaseInvoice)
        {
            return new JsonResponse(['hydra:description' => 'Purchase Invoice not found.'], 404);
        }

        $purchaseInvoice->setStatus('draft');

        $this->manager->flush();

        return $this->processor->process($purchaseInvoice, $operation, $uriVariables, $context);
    }
}
