<?php

namespace App\Controller\Billing\Purchase\Return;

use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemDiscountRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemStockRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemTaxRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class ClearPurchaseReturnInvoiceItemController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(PurchaseReturnInvoiceRepository $purchaseReturnInvoiceRepository,
                             PurchaseReturnInvoiceItemRepository $purchaseReturnInvoiceItemRepository,
                             PurchaseReturnInvoiceItemStockRepository $purchaseReturnInvoiceItemStockRepository,
                             PurchaseReturnInvoiceItemTaxRepository $purchaseReturnInvoiceItemTaxRepository,
                             PurchaseReturnInvoiceItemDiscountRepository $purchaseReturnInvoiceItemDiscountRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {
        $id = $request->get('id');

        $purchaseReturnInvoice = $purchaseReturnInvoiceRepository->findOneBy(['id' => $id]);
        if (!$purchaseReturnInvoice){
            return new JsonResponse(['hydra:description' => 'Purchase Return Invoice '.$id.' not found.'], 404);
        }

        $purchaseReturnInvoiceItems = $purchaseReturnInvoiceItemRepository->findBy(['purchaseReturnInvoice' => $purchaseReturnInvoice]);
        foreach ($purchaseReturnInvoiceItems as $purchaseReturnInvoiceItem)
        {
            // clear purchase return invoice item discount
            $purchaseReturnInvoiceItemDiscounts = $purchaseReturnInvoiceItemDiscountRepository->findBy(['purchaseReturnInvoiceItem' => $purchaseReturnInvoiceItem]);
            if ($purchaseReturnInvoiceItemDiscounts){
                foreach ($purchaseReturnInvoiceItemDiscounts as $purchaseReturnInvoiceItemDiscount){
                    $entityManager->remove($purchaseReturnInvoiceItemDiscount);
                }
            }

            // clear purchase return invoice item tax
            $purchaseReturnInvoiceItemTaxes = $purchaseReturnInvoiceItemTaxRepository->findBy(['purchaseReturnInvoiceItem' => $purchaseReturnInvoiceItem]);
            if ($purchaseReturnInvoiceItemTaxes){
                foreach ($purchaseReturnInvoiceItemTaxes as $purchaseReturnInvoiceItemTax){
                    $entityManager->remove($purchaseReturnInvoiceItemTax);
                }
            }

            // clear purchase invoice item stock
            $purchaseReturnInvoiceItemStocks = $purchaseReturnInvoiceItemStockRepository->findBy(['purchaseReturnInvoiceItem' => $purchaseReturnInvoiceItem]);
            if ($purchaseReturnInvoiceItemStocks){
                foreach ($purchaseReturnInvoiceItemStocks as $purchaseReturnInvoiceItemStock){
                    $entityManager->remove($purchaseReturnInvoiceItemStock);
                }
            }

            $entityManager->remove($purchaseReturnInvoiceItem);
        }

        // update purchase return invoice
        $purchaseReturnInvoice->setAmount(0);
        $purchaseReturnInvoice->setTtc(0);
        $purchaseReturnInvoice->setBalance(0);
        $purchaseReturnInvoice->setVirtualBalance(0);

        $entityManager->flush();

        return $this->json(['hydra:member' => $purchaseReturnInvoice]);
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
