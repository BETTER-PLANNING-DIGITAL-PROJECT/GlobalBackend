<?php

namespace App\Controller\Billing\Purchase\Invoice;

use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemDiscountRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemTaxRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class DeletePurchaseInvoiceItemController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                             PurchaseInvoiceItemTaxRepository $purchaseInvoiceItemTaxRepository,
                             PurchaseInvoiceItemDiscountRepository $purchaseInvoiceItemDiscountRepository,
                             PurchaseInvoiceRepository $purchaseInvoiceRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $purchaseInvoiceItem = $purchaseInvoiceItemRepository->findOneBy(['id' => $id]);
        if (!$purchaseInvoiceItem){
            return new JsonResponse(['hydra:description' => 'This purchase invoice item '.$id.' is not found.'], 404);
        }

        $purchaseInvoice = $purchaseInvoiceItem->getPurchaseInvoice();

        // clear purchase invoice item discount
        $purchaseInvoiceItemDiscounts = $purchaseInvoiceItemDiscountRepository->findBy(['purchaseInvoiceItem' => $purchaseInvoiceItem]);
        if ($purchaseInvoiceItemDiscounts){
            foreach ($purchaseInvoiceItemDiscounts as $purchaseInvoiceItemDiscount){
                $entityManager->remove($purchaseInvoiceItemDiscount);
            }
        }

        // clear purchase invoice item tax
        $purchaseInvoiceItemTaxes = $purchaseInvoiceItemTaxRepository->findBy(['purchaseInvoiceItem' => $purchaseInvoiceItem]);
        if ($purchaseInvoiceItemTaxes){
            foreach ($purchaseInvoiceItemTaxes as $purchaseInvoiceItemTax){
                $entityManager->remove($purchaseInvoiceItemTax);
            }
        }

        $entityManager->remove($purchaseInvoiceItem);

        $entityManager->flush();


        // update purchase invoice
        $amount = $purchaseInvoiceItemRepository->purchaseInvoiceHtAmount($purchaseInvoice)[0][1];
        $purchaseInvoice->setAmount($amount);

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
        if($purchaseInvoiceItemTaxes)
        {
            foreach ($purchaseInvoiceItemTaxes as $purchaseInvoiceItemTax)
            {
                $totalTaxAmount += $purchaseInvoiceItemTax->getAmount();
            }
        }

        $amountTtc = $purchaseInvoiceItemRepository->purchaseInvoiceHtAmount($purchaseInvoice)[0][1] + $totalTaxAmount - $totalDiscountAmount;
        $purchaseInvoice->setTtc($amountTtc);
        $purchaseInvoice->setBalance($amountTtc);
        $purchaseInvoice->setVirtualBalance($amountTtc);

        $entityManager->flush();

        return $this->json(['hydra:member' => 200]);
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
