<?php
namespace App\State\Processor\Billing\Purchase;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Purchase\PurchaseReturnInvoice;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemDiscountRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemStockRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemTaxRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class DeletePurchaseReturnInvoiceProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $manager,
                                private readonly PurchaseReturnInvoiceRepository $purchaseReturnInvoiceRepository,
                                private readonly PurchaseReturnInvoiceItemDiscountRepository $purchaseReturnInvoiceItemDiscountRepository,
                                private readonly PurchaseReturnInvoiceItemTaxRepository $purchaseReturnInvoiceItemTaxRepository,
                                private readonly PurchaseReturnInvoiceItemStockRepository $purchaseReturnInvoiceItemStockRepository,
                                private readonly PurchaseReturnInvoiceItemRepository $purchaseReturnInvoiceItemRepository) {
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

        $purchaseReturnInvoiceItems = $this->purchaseReturnInvoiceItemRepository->findBy(['purchaseReturnInvoice'=> $purchaseReturnInvoice]);
        if($purchaseReturnInvoiceItems)
        {
            foreach ($purchaseReturnInvoiceItems as $purchaseReturnInvoiceItem)
            {
                // clear purchase return invoice item discount
                $purchaseReturnInvoiceItemDiscounts = $this->purchaseReturnInvoiceItemDiscountRepository->findBy(['purchaseReturnInvoiceItem' => $purchaseReturnInvoiceItem]);
                if ($purchaseReturnInvoiceItemDiscounts){
                    foreach ($purchaseReturnInvoiceItemDiscounts as $purchaseReturnInvoiceItemDiscount){
                        $this->manager->remove($purchaseReturnInvoiceItemDiscount);
                    }
                }

                // clear purchase return invoice item tax
                $purchaseReturnInvoiceItemTaxes = $this->purchaseReturnInvoiceItemTaxRepository->findBy(['purchaseReturnInvoiceItem' => $purchaseReturnInvoiceItem]);
                if ($purchaseReturnInvoiceItemTaxes){
                    foreach ($purchaseReturnInvoiceItemTaxes as $purchaseReturnInvoiceItemTax){
                        $this->manager->remove($purchaseReturnInvoiceItemTax);
                    }
                }

                // clear purchase return invoice item stock
                $purchaseReturnInvoiceItemStocks = $this->purchaseReturnInvoiceItemStockRepository->findBy(['purchaseReturnInvoiceItem' => $purchaseReturnInvoiceItem]);
                if ($purchaseReturnInvoiceItemStocks){
                    foreach ($purchaseReturnInvoiceItemStocks as $purchaseReturnInvoiceItemStock){
                        $this->manager->remove($purchaseReturnInvoiceItemStock);
                    }
                }

                $this->manager->remove($purchaseReturnInvoiceItem);
            }
        }

        $this->manager->remove($purchaseReturnInvoice);
        $this->manager->flush();

        //return $this->processor->process($purchaseInvoice, $operation, $uriVariables, $context);
    }
}
