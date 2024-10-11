<?php

namespace App\Controller\Billing\Purchase\Invoice;

use App\Entity\Billing\Purchase\PurchaseInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemDiscountRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemTaxRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetPurchaseInvoiceTotalAmountController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                )
    {
    }

    public function __invoke(PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                             PurchaseInvoiceRepository $purchaseInvoiceRepository,
                             PurchaseInvoiceItemDiscountRepository $purchaseInvoiceItemDiscountRepository,
                             PurchaseInvoiceItemTaxRepository $purchaseInvoiceItemTaxRepository,
                             Request $request): JsonResponse
    {


        $id = $request->get('id');
        $purchaseInvoice = $purchaseInvoiceRepository->find($id);
        if (!$purchaseInvoice){

            return new JsonResponse(['hydra:description' => 'Purchase Invoice not found.'], 404);
        }

        // get purchase invoice item discounts from purchase invoice
        $purchaseInvoiceItemDiscounts = $purchaseInvoiceItemDiscountRepository->findBy(['purchaseInvoice' => $purchaseInvoice]);
        $totalDiscountAmount = 0;
        if($purchaseInvoiceItemDiscounts)
        {
            foreach ($purchaseInvoiceItemDiscounts as $purchaseInvoiceItemDiscount)
            {
                $totalDiscountAmount += $purchaseInvoiceItemDiscount->getAmount();
            }
        }

        // get purchase invoice item taxes from purchase invoice
        $purchaseInvoiceItemTaxes = $purchaseInvoiceItemTaxRepository->findBy(['purchaseInvoice' => $purchaseInvoice]);
        $totalTaxAmount = 0;
        $vatAmount = 0;
        $isAmount = 0;
        if($purchaseInvoiceItemTaxes)
        {
            foreach ($purchaseInvoiceItemTaxes as $purchaseInvoiceItemTax)
            {
                $totalTaxAmount += $purchaseInvoiceItemTax->getAmount();

                if($purchaseInvoiceItemTax->getTax()->getName() == 'V.A.T'){
                    $vatAmount += $purchaseInvoiceItemTax->getAmount();
                }
                elseif ($purchaseInvoiceItemTax->getTax()->getName() == 'IS'){
                    $isAmount += $purchaseInvoiceItemTax->getAmount();
                }
            }
        }

        $amountTtc = $purchaseInvoiceItemRepository->purchaseInvoiceHtAmount($purchaseInvoice)[0][1] + $totalTaxAmount - $totalDiscountAmount;

        $items = [
            'totalHt' => number_format($purchaseInvoiceItemRepository->purchaseInvoiceHtAmount($purchaseInvoice)[0][1], 2, ',',' '),
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
