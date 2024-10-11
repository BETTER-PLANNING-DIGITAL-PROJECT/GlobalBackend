<?php

namespace App\Controller\Billing\Purchase\Invoice;

use App\Entity\Billing\Purchase\PurchaseInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetPurchaseInvoiceItemController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                )
    {
    }

    public function __invoke(PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                             PurchaseInvoiceRepository $purchaseInvoiceRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $purchaseInvoice = $purchaseInvoiceRepository->find($id);
        if (!$purchaseInvoice){

            return new JsonResponse(['hydra:description' => 'Purchase Invoice not found.'], 404);
        }

        $purchaseInvoiceItems = $purchaseInvoiceItemRepository->findBy(['purchaseInvoice' => $purchaseInvoice]);

        $items = [];

        foreach ($purchaseInvoiceItems as $purchaseInvoiceItem){
            $items[] = [
                'id' => $purchaseInvoiceItem->getId(),
                'saleInvoice' => [
                    'id' => $purchaseInvoiceItem->getPurchaseInvoice() ? $purchaseInvoiceItem->getPurchaseInvoice()->getId() : '',
                    '@id' => '/api/get/purchase-invoice/'. $purchaseInvoiceItem->getPurchaseInvoice()->getId(),
                    'invoiceNumber' => $purchaseInvoiceItem->getPurchaseInvoice() ? $purchaseInvoiceItem->getPurchaseInvoice()->getInvoiceNumber() : '',
                ],
                'item' => [
                    'id' => $purchaseInvoiceItem->getItem()->getId(),
                    '@id' => '/api/get/items/'. $purchaseInvoiceItem->getItem()->getId(),
                    'name' => $purchaseInvoiceItem->getItem() ? $purchaseInvoiceItem->getItem()->getName() : '',
                    'reference' => $purchaseInvoiceItem->getItem() ? $purchaseInvoiceItem->getItem()->getReference() : '',
                    'barcode' => $purchaseInvoiceItem->getItem() ? $purchaseInvoiceItem->getItem()->getBarcode() : '',
                    'price' => $purchaseInvoiceItem->getItem() ? $purchaseInvoiceItem->getItem()->getPrice() : '',
                    'salePrice' => $purchaseInvoiceItem->getItem() ? $purchaseInvoiceItem->getItem()->getSalePrice() : '',
                    'cost' => $purchaseInvoiceItem->getItem() ? $purchaseInvoiceItem->getItem()->getCost() : '',
                ],
                'name' => $purchaseInvoiceItem->getName(),
                'quantity' => $purchaseInvoiceItem->getQuantity(),
                'amount' => number_format($purchaseInvoiceItem->getAmount(), 2, ',',' '),
                'pu' => number_format($purchaseInvoiceItem->getPu(), 2, ',',' '),
                'discount' => $purchaseInvoiceItem->getDiscount(),
                'discountAmount' => $purchaseInvoiceItem->getDiscountAmount(),
                'amountTtc' => $purchaseInvoiceItem->getAmountTtc(),
                'taxAmount' => $purchaseInvoiceItem->getTaxAmount(),

                'taxes' => $this->taxes($purchaseInvoiceItem),

            ];
        }

        return $this->json(['hydra:member' => $items]);
    }


    public function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }

    public function taxes(PurchaseInvoiceItem $purchaseInvoiceItem): array
    {
        $taxes = [];

        foreach ($purchaseInvoiceItem->getTaxes() as $tax){
            $taxes[] = [
                'id' => $tax->getId(),
                'name' => $tax->getName(),
                'rate' => $tax->getRate(),
                'label' => $tax->getLabel(),
            ];
        }
        return $taxes;
    }
}
