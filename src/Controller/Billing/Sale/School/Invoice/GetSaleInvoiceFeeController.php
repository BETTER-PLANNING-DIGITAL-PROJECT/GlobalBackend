<?php

namespace App\Controller\Billing\Sale\School\Invoice;

use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Partner\CustomerRepository;
use App\Repository\Product\ItemRepository;
use App\Repository\School\Schooling\Configuration\FeeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSaleInvoiceFeeController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                )
    {
    }

    public function __invoke(SaleInvoiceFeeRepository $saleInvoiceFeeRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             CustomerRepository $customerRepository,
                             ItemRepository $itemRepository,
                             FeeRepository $feeRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $saleInvoice = $saleInvoiceRepository->find($id);
        if (!$saleInvoice){
            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }

        $items = [];

        $saleInvoiceFees = $saleInvoiceFeeRepository->findBy(['saleInvoice' => $saleInvoice]);

        foreach ($saleInvoiceFees as $saleInvoiceFee){
            $items[] = [
                'id' => $saleInvoiceFee->getId(),
                'saleInvoice' => [
                    'id' => $saleInvoiceFee->getSaleInvoice() ? $saleInvoiceFee->getSaleInvoice()->getId() : '',
                    '@id' => '/api/get/sale-invoice/'. $saleInvoiceFee->getSaleInvoice()->getId(),
                    'invoiceNumber' => $saleInvoiceFee->getSaleInvoice() ? $saleInvoiceFee->getSaleInvoice()->getInvoiceNumber() : '',
                ],
                'item' => [
                    'id' => $saleInvoiceFee->getFee()->getId(),
                    '@id' => '/api/get/fee/'. $saleInvoiceFee->getFee()->getId(),
                    'code' => $saleInvoiceFee->getFee() ? $saleInvoiceFee->getFee()->getCode() : '',
                    'name' => $saleInvoiceFee->getFee() ? $saleInvoiceFee->getFee()->getName() : '',
                    'class' => $saleInvoiceFee->getClass() ? $saleInvoiceFee->getClass()->getCode() : '',
                    'amount' => $saleInvoiceFee->getFee() ? $saleInvoiceFee->getFee()->getAmount() : '',
                ],
                'name' => $saleInvoiceFee->getName(),
                'quantity' => $saleInvoiceFee->getQuantity(),
                'amount' => number_format($saleInvoiceFee->getAmount(), 2, ',',' '),
                'pu' => number_format($saleInvoiceFee->getPu(), 2, ',',' '),
                'discount' => $saleInvoiceFee->getDiscount(),
                'discountAmount' => $saleInvoiceFee->getDiscountAmount(),
                'amountTtc' => $saleInvoiceFee->getAmountTtc(),
                'amountWithTaxes' => $saleInvoiceFee->getAmountWithTaxes(),
                'returnQuantity' => $saleInvoiceFee->getReturnQuantity(),
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

}
