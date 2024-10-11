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
class ClearPurchaseInvoiceItemController extends AbstractController
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

        $purchaseInvoice = $purchaseInvoiceRepository->findOneBy(['id' => $id]);
        if (!$purchaseInvoice){
            return new JsonResponse(['hydra:description' => 'Purchase Invoice '.$id.' not found.'], 404);
        }

        $purchaseInvoiceItems = $purchaseInvoiceItemRepository->findBy(['purchaseInvoice' => $purchaseInvoice]);
        foreach ($purchaseInvoiceItems as $purchaseInvoiceItem)
        {
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
        }

        // update purchase invoice
        $purchaseInvoice->setAmount(0);
        $purchaseInvoice->setTtc(0);
        $purchaseInvoice->setBalance(0);
        $purchaseInvoice->setVirtualBalance(0);

        $entityManager->flush();

        return $this->json(['hydra:member' => $purchaseInvoice]);
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