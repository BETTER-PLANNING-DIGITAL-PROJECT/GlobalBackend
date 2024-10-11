<?php

namespace App\Controller\Billing\Sale\Invoice;

use App\Entity\Billing\Sale\SaleInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemDiscountRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemTaxRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSaleInvoiceTotalAmountController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             SaleInvoiceItemDiscountRepository $saleInvoiceItemDiscountRepository,
                             SaleInvoiceItemTaxRepository $saleInvoiceItemTaxRepository,
                             Request $request): JsonResponse
    {
        $id = $request->get('id');
        $saleInvoice = $saleInvoiceRepository->find($id);
        if (!$saleInvoice){
            return new JsonResponse(['hydra:description' => 'Sale Invoice not found.'], 404);
        }

        /*$taxResult = 0;
        $discountAmount = 0;
        $vatAmount = 0;
        $isAmount = 0;
        $saleInvoiceItems = $saleInvoiceItemRepository->findBy(['saleInvoice' => $invoice]);

        foreach ($saleInvoiceItems as $saleInvoiceItem){
            if ($saleInvoiceItem->getTaxes()){
                foreach ($saleInvoiceItem->getTaxes() as $tax){
                    $taxResult = $taxResult + $saleInvoiceItem->getAmount() * $tax->getRate() / 100;

                    if($tax->getName() == 'V.A.T'){
                        $vatAmount = $saleInvoiceItem->getAmount() * $tax->getRate() / 100;
                    }
                    elseif ($tax->getName() == 'IS'){
                        $isAmount = $saleInvoiceItem->getAmount() * $tax->getRate() / 100;
                    }
                }
            }

            $discountAmount = $discountAmount + $saleInvoiceItem->getAmount() * $saleInvoiceItem->getDiscount() / 100;
        }

        $amountTtc = $saleInvoiceItemRepository->saleInvoiceHtAmount($invoice)[0][1] + $taxResult - $discountAmount;*/

        // get sale invoice item discounts from sale invoice
        $saleInvoiceItemDiscounts = $saleInvoiceItemDiscountRepository->findBy(['saleInvoice' => $saleInvoice]);
        $totalDiscountAmount = 0;
        if($saleInvoiceItemDiscounts)
        {
            foreach ($saleInvoiceItemDiscounts as $saleInvoiceItemDiscount)
            {
                $totalDiscountAmount += $saleInvoiceItemDiscount->getAmount();
            }
        }

        // get sale invoice item taxes from sale invoice
        $saleInvoiceItemTaxes = $saleInvoiceItemTaxRepository->findBy(['saleInvoice' => $saleInvoice]);
        $totalTaxAmount = 0;
        $vatAmount = 0;
        $isAmount = 0;
        if($saleInvoiceItemTaxes)
        {
            foreach ($saleInvoiceItemTaxes as $saleInvoiceItemTax)
            {
                $totalTaxAmount += $saleInvoiceItemTax->getAmount();

                if($saleInvoiceItemTax->getTax()->getName() == 'V.A.T'){
                    $vatAmount += $saleInvoiceItemTax->getAmount();
                }
                elseif ($saleInvoiceItemTax->getTax()->getName() == 'IS'){
                    $isAmount += $saleInvoiceItemTax->getAmount();
                }
            }
        }

        $amountTtc = $saleInvoiceItemRepository->saleInvoiceHtAmount($saleInvoice)[0][1] + $totalTaxAmount - $totalDiscountAmount;

        $items = [
            'totalHt' => number_format($saleInvoiceItemRepository->saleInvoiceHtAmount($saleInvoice)[0][1], 2, ',',' '),
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

    public function taxes(SaleInvoiceItem $saleInvoiceItem): array
    {
        $taxes = [];

        foreach ($saleInvoiceItem->getTaxes() as $tax){
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
