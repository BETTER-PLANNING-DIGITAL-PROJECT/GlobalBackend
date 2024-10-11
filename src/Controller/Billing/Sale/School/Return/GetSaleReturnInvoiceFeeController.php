<?php

namespace App\Controller\Billing\Sale\School\Return;

use App\Entity\Billing\Sale\SaleInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSaleReturnInvoiceFeeController extends AbstractController
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

        $saleReturnInvoiceFees = $saleReturnInvoiceFeeRepository->findBy(['saleReturnInvoice' => $returnInvoice]);

        $items = [];

        foreach ($saleReturnInvoiceFees as $saleReturnInvoiceFee){
            $items[] = [
                'id' => $saleReturnInvoiceFee->getId(),
                'saleReturnInvoice' => [
                    'id' => $saleReturnInvoiceFee->getSaleReturnInvoice() ? $saleReturnInvoiceFee->getSaleReturnInvoice()->getId() : '',
                    '@id' => '/api/get/sale-invoice/'. $saleReturnInvoiceFee->getSaleReturnInvoice()->getId(),
                    'invoiceNumber' => $saleReturnInvoiceFee->getSaleReturnInvoice() ? $saleReturnInvoiceFee->getSaleReturnInvoice()->getInvoiceNumber() : '',
                ],
                'name' => $saleReturnInvoiceFee->getName(),
                'quantity' => $saleReturnInvoiceFee->getQuantity(),
                'pu' => $saleReturnInvoiceFee->getPu(),
                'discount' => $saleReturnInvoiceFee->getDiscount(),
                'amount' => $saleReturnInvoiceFee->getAmount(),
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
