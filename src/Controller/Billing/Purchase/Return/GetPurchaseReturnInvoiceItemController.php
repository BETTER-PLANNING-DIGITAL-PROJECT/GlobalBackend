<?php

namespace App\Controller\Billing\Purchase\Return;

use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetPurchaseReturnInvoiceItemController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(PurchaseReturnInvoiceItemRepository $purchaseReturnInvoiceItemRepository, PurchaseReturnInvoiceRepository $purchaseReturnInvoiceRepository, Request $request): JsonResponse
    {
        $id = $request->get('id');
        $purchaseReturnInvoice = $purchaseReturnInvoiceRepository->find($id);
        if (!$purchaseReturnInvoice){
            return new JsonResponse(['hydra:description' => 'Purchase Return Invoice not found.'], 404);
        }

        $purchaseReturnInvoiceItems = $purchaseReturnInvoiceItemRepository->findBy(['purchaseReturnInvoice' => $purchaseReturnInvoice]);

        $items = [];

        foreach ($purchaseReturnInvoiceItems as $purchaseReturnInvoiceItem){
            $items[] = [
                'id' => $purchaseReturnInvoiceItem->getId(),
                'purchaseInvoice' => [
                    'id' => $purchaseReturnInvoiceItem->getPurchaseReturnInvoice() ? $purchaseReturnInvoiceItem->getPurchaseReturnInvoice()->getId() : '',
                    '@id' => '/api/get/purchase-invoice/'. $purchaseReturnInvoiceItem->getPurchaseReturnInvoice()->getId(),
                    'invoiceNumber' => $purchaseReturnInvoiceItem->getPurchaseReturnInvoice() ? $purchaseReturnInvoiceItem->getPurchaseReturnInvoice()->getInvoiceNumber() : '',
                ],
                'item' => [
                    'id' => $purchaseReturnInvoiceItem->getItem()->getId(),
                    '@id' => '/api/get/items/'. $purchaseReturnInvoiceItem->getItem()->getId(),
                    'name' => $purchaseReturnInvoiceItem->getItem() ? $purchaseReturnInvoiceItem->getItem()->getName() : '',
                    'reference' => $purchaseReturnInvoiceItem->getItem() ? $purchaseReturnInvoiceItem->getItem()->getReference() : '',
                    'barcode' => $purchaseReturnInvoiceItem->getItem() ? $purchaseReturnInvoiceItem->getItem()->getBarcode() : '',
                    'price' => $purchaseReturnInvoiceItem->getItem() ? $purchaseReturnInvoiceItem->getItem()->getPrice() : '',
                    'cost' => $purchaseReturnInvoiceItem->getItem() ? $purchaseReturnInvoiceItem->getItem()->getCost() : '',
                ],
                'name' => $purchaseReturnInvoiceItem->getName(),
                'quantity' => $purchaseReturnInvoiceItem->getQuantity(),
                'amount' => number_format($purchaseReturnInvoiceItem->getAmount(), 2, ',',' '),
                'pu' => number_format($purchaseReturnInvoiceItem->getPu(), 2, ',',' '),
                'discount' => $purchaseReturnInvoiceItem->getDiscount(),
                'discountAmount' => $purchaseReturnInvoiceItem->getDiscountAmount(),
                'amountTtc' => $purchaseReturnInvoiceItem->getAmountTtc(),
                'amountWithTaxes' => $purchaseReturnInvoiceItem->getAmountWithTaxes(),

                'taxes' => $this->taxes($purchaseReturnInvoiceItem),

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

    public function taxes(PurchaseReturnInvoiceItem $purchaseReturnInvoiceItem): array
    {
        $taxes = [];

        foreach ($purchaseReturnInvoiceItem->getTaxes() as $tax){
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
