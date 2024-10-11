<?php

namespace App\Controller\Billing\Sale\School\Invoice;

use App\Entity\Billing\Sale\SaleInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSaleInvoiceFeeTotalAmountController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                )
    {
    }

    public function __invoke(SaleInvoiceFeeRepository $saleInvoiceFeeRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $invoice = $saleInvoiceRepository->find($id);
        if (!$invoice){

            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }

        $taxResult = 0;
        $discountAmount = 0;
        $vatAmount = 0;
        $isAmount = 0;
        $saleInvoiceFees = $saleInvoiceFeeRepository->findBy(['saleInvoice' => $invoice]);

        foreach ($saleInvoiceFees as $saleInvoiceFee){
            if ($saleInvoiceFee->getTaxes()){
                foreach ($saleInvoiceFee->getTaxes() as $tax){
                    $taxResult = $taxResult + $saleInvoiceFee->getAmount() * $tax->getRate() / 100;

                    if($tax->getName() == 'V.A.T'){
                        $vatAmount = $saleInvoiceFee->getAmount() * $tax->getRate() / 100;
                    }
                    elseif ($tax->getName() == 'IS'){
                        $isAmount = $saleInvoiceFee->getAmount() * $tax->getRate() / 100;
                    }
                }
            }

            $discountAmount = $discountAmount + $saleInvoiceFee->getAmount() * $saleInvoiceFee->getDiscount() / 100;
        }

        $amountTtc = $saleInvoiceFeeRepository->saleInvoiceHtAmount($invoice)[0][1] + $taxResult - $discountAmount;

        $items = [
            'totalHt' => number_format($saleInvoiceFeeRepository->saleInvoiceHtAmount($invoice)[0][1], 2, ',',' '),
            'taxes'   => number_format($taxResult, 2, ',',' '),
            'discountAmount' => number_format($discountAmount, 2, ',',' '),
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

    public function taxes(SaleInvoiceItem $saleInvoiceItem){
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
