<?php

namespace App\Controller\Billing\Purchase\Return;

use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemDiscountRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemTaxRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetPurchaseReturnInvoiceTotalAmountController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(PurchaseReturnInvoiceItemRepository $purchaseReturnInvoiceItemRepository,
                             PurchaseReturnInvoiceRepository $purchaseReturnInvoiceRepository,
                             PurchaseReturnInvoiceItemDiscountRepository $purchaseReturnInvoiceItemDiscountRepository,
                             PurchaseReturnInvoiceItemTaxRepository $purchaseReturnInvoiceItemTaxRepository,
                             Request $request): JsonResponse
    {
        $id = $request->get('id');
        $purchaseReturnInvoice = $purchaseReturnInvoiceRepository->find($id);
        if (!$purchaseReturnInvoice){
            return new JsonResponse(['hydra:description' => 'Purchase Return Invoice not found.'], 404);
        }

        // get purchase return invoice item discounts from purchase invoice
        $purchaseReturnInvoiceItemDiscounts = $purchaseReturnInvoiceItemDiscountRepository->findBy(['purchaseReturnInvoice' => $purchaseReturnInvoice]);
        $totalDiscountAmount = 0;
        if($purchaseReturnInvoiceItemDiscounts)
        {
            foreach ($purchaseReturnInvoiceItemDiscounts as $purchaseReturnInvoiceItemDiscount)
            {
                $totalDiscountAmount += $purchaseReturnInvoiceItemDiscount->getAmount();
            }
        }

        // get purchase return invoice item taxes from purchase invoice
        $purchaseReturnInvoiceItemTaxes = $purchaseReturnInvoiceItemTaxRepository->findBy(['purchaseReturnInvoice' => $purchaseReturnInvoice]);
        $totalTaxAmount = 0;
        $vatAmount = 0;
        $isAmount = 0;
        if($purchaseReturnInvoiceItemTaxes)
        {
            foreach ($purchaseReturnInvoiceItemTaxes as $purchaseReturnInvoiceItemTax)
            {
                $totalTaxAmount += $purchaseReturnInvoiceItemTax->getAmount();

                if($purchaseReturnInvoiceItemTax->getTax()->getName() == 'V.A.T'){
                    $vatAmount += $purchaseReturnInvoiceItemTax->getAmount();
                }
                elseif ($purchaseReturnInvoiceItemTax->getTax()->getName() == 'IS'){
                    $isAmount += $purchaseReturnInvoiceItemTax->getAmount();
                }
            }
        }

        $amountTtc = $purchaseReturnInvoiceItemRepository->purchaseReturnInvoiceHtAmount($purchaseReturnInvoice)[0][1] + $totalTaxAmount - $totalDiscountAmount;

        $items = [
            'totalHt' => number_format($purchaseReturnInvoiceItemRepository->purchaseReturnInvoiceHtAmount($purchaseReturnInvoice)[0][1], 2, ',',' '),
            'taxes'   => number_format($totalTaxAmount, 2, ',',' '),
            'discountAmount' => number_format($totalDiscountAmount, 2, ',',' '),
            'totalTtc' => number_format($amountTtc, 2, ',',' '),
            'vatAmount' => number_format($vatAmount, 2, ',',' '),
            'isAmount' => number_format($isAmount, 2, ',',' '),
        ];

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
