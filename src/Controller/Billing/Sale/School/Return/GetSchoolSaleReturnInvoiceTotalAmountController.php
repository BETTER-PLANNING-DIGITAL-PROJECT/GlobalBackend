<?php

namespace App\Controller\Billing\Sale\School\Return;

use App\Entity\Billing\Sale\SaleReturnInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSchoolSaleReturnInvoiceTotalAmountController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                )
    {
    }

    public function __invoke(SaleReturnInvoiceFeeRepository $saleReturnInvoiceFeeRepository,
                             SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $returnInvoice = $saleReturnInvoiceRepository->find($id);
        if (!$returnInvoice){

            return new JsonResponse(['hydra:description' => 'This return invoice is not found.'], 404);
        }

        $taxResult = 0;
        $discountAmount = 0;
        $saleReturnInvoiceFees = $saleReturnInvoiceFeeRepository->findBy(['saleReturnInvoice' => $returnInvoice]);

        foreach ($saleReturnInvoiceFees as $saleReturnInvoiceFee){
            if ($saleReturnInvoiceFee->getTaxes()){
                foreach ($saleReturnInvoiceFee->getTaxes() as $tax){
                    $taxResult = $taxResult + $saleReturnInvoiceFee->getAmount() * $tax->getRate() / 100;
                }
            }

            $discountAmount = $discountAmount + $saleReturnInvoiceFee->getAmount() * $saleReturnInvoiceFee->getDiscount() / 100;
        }

        $amountTtc = $saleReturnInvoiceFeeRepository->saleReturnInvoiceHtAmount($returnInvoice)[0][1] + $taxResult - $discountAmount;

        $items = [
            'totalHt' => number_format($saleReturnInvoiceFeeRepository->saleReturnInvoiceHtAmount($returnInvoice)[0][1], 2, ',',' '),
            'taxes'   => number_format($taxResult, 2, ',',' '),
            'discountAmount' => number_format($discountAmount, 2, ',',' '),
            'totalTtc' => number_format($amountTtc, 2, ',',' '),
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

    public function taxes(SaleReturnInvoiceItem $saleReturnInvoiceItem){
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
