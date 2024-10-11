<?php

namespace App\Controller\Billing\Purchase;

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
class GetPurchaseInvoiceTotalAmountController extends AbstractController
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
        $invoice = $purchaseInvoiceRepository->find($id);
        if (!$invoice){

            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }

        $taxResult = 0;
        $discountAmount = 0;
        $purchaseInvoiceItems = $purchaseInvoiceItemRepository->findBy(['purchaseInvoice' => $invoice]);

        foreach ($purchaseInvoiceItems as $purchaseInvoiceItem){
            if ($purchaseInvoiceItem->getTaxes()){
                foreach ($purchaseInvoiceItem->getTaxes() as $tax){
                    $taxResult = $taxResult + $purchaseInvoiceItem->getAmount() * $tax->getRate() / 100;
                }
            }

            $discountAmount = $discountAmount + $purchaseInvoiceItem->getAmount() * $purchaseInvoiceItem->getDiscount() / 100;
        }

        $amountTtc = $purchaseInvoiceItemRepository->purchaseInvoiceTotalAmount($invoice)[0][1] + $taxResult - $discountAmount;

        $items = [
            'totalHt' => number_format($purchaseInvoiceItemRepository->purchaseInvoiceTotalAmount($invoice)[0][1], 2, ',',' '),
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

    public function taxes(PurchaseInvoiceItem $purchaseInvoiceItem){
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
