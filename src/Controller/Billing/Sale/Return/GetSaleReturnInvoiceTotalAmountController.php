<?php

namespace App\Controller\Billing\Sale\Return;

use App\Entity\Billing\Sale\SaleReturnInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemDiscountRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemTaxRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSaleReturnInvoiceTotalAmountController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(SaleReturnInvoiceItemRepository $saleReturnInvoiceItemRepository,
                             SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                             SaleReturnInvoiceItemDiscountRepository $saleReturnInvoiceItemDiscountRepository,
                             SaleReturnInvoiceItemTaxRepository $saleReturnInvoiceItemTaxRepository,
                             Request $request): JsonResponse
    {
        $id = $request->get('id');
        $saleReturnInvoice = $saleReturnInvoiceRepository->find($id);
        if (!$saleReturnInvoice){
            return new JsonResponse(['hydra:description' => 'Sale Return Invoice not found.'], 404);
        }

        // get sale return invoice item discounts from sale invoice
        $saleReturnInvoiceItemDiscounts = $saleReturnInvoiceItemDiscountRepository->findBy(['saleReturnInvoice' => $saleReturnInvoice]);
        $totalDiscountAmount = 0;
        if($saleReturnInvoiceItemDiscounts)
        {
            foreach ($saleReturnInvoiceItemDiscounts as $saleReturnInvoiceItemDiscount)
            {
                $totalDiscountAmount += $saleReturnInvoiceItemDiscount->getAmount();
            }
        }

        // get sale return invoice item taxes from sale invoice
        $saleReturnInvoiceItemTaxes = $saleReturnInvoiceItemTaxRepository->findBy(['saleReturnInvoice' => $saleReturnInvoice]);
        $totalTaxAmount = 0;
        $vatAmount = 0;
        $isAmount = 0;
        if($saleReturnInvoiceItemTaxes)
        {
            foreach ($saleReturnInvoiceItemTaxes as $saleReturnInvoiceItemTax)
            {
                $totalTaxAmount += $saleReturnInvoiceItemTax->getAmount();

                if($saleReturnInvoiceItemTax->getTax()->getName() == 'V.A.T'){
                    $vatAmount += $saleReturnInvoiceItemTax->getAmount();
                }
                elseif ($saleReturnInvoiceItemTax->getTax()->getName() == 'IS'){
                    $isAmount += $saleReturnInvoiceItemTax->getAmount();
                }
            }
        }

        $amountTtc = $saleReturnInvoiceItemRepository->saleReturnInvoiceHtAmount($saleReturnInvoice)[0][1] + $totalTaxAmount - $totalDiscountAmount;

        $items = [
            'totalHt' => number_format($saleReturnInvoiceItemRepository->saleReturnInvoiceHtAmount($saleReturnInvoice)[0][1], 2, ',',' '),
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

    public function taxes(SaleReturnInvoiceItem $saleReturnInvoiceItem): array
    {
        $taxes = [];

        foreach ($saleReturnInvoiceItem->getTaxes() as $tax){
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
