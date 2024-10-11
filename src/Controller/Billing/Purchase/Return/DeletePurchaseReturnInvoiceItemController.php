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
class DeletePurchaseReturnInvoiceItemController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(PurchaseReturnInvoiceItemRepository $purchaseReturnInvoiceItemRepository,
                             PurchaseReturnInvoiceItemStockRepository $purchaseReturnInvoiceItemStockRepository,
                             PurchaseReturnInvoiceItemDiscountRepository $purchaseReturnInvoiceItemDiscountRepository,
                             PurchaseReturnInvoiceItemTaxRepository $purchaseReturnInvoiceItemTaxRepository,
                             PurchaseReturnInvoiceRepository $purchaseReturnInvoiceRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $purchaseReturnInvoiceItem = $purchaseReturnInvoiceItemRepository->findOneBy(['id' => $id]);
        if (!$purchaseReturnInvoiceItem){
            return new JsonResponse(['hydra:description' => 'This purchase return invoice item '.$id.' is not found.'], 404);
        }

        $purchaseReturnInvoice = $purchaseReturnInvoiceItem->getPurchaseReturnInvoice();

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

        $purchaseReturnInvoiceItemStocks = $purchaseReturnInvoiceItemStockRepository->findBy(['purchaseReturnInvoiceItem' => $purchaseReturnInvoiceItem]);
        if ($purchaseReturnInvoiceItemStocks){
            foreach ($purchaseReturnInvoiceItemStocks as $purchaseReturnInvoiceItemStock){
                $entityManager->remove($purchaseReturnInvoiceItemStock);
            }
        }

        $entityManager->remove($purchaseReturnInvoiceItem);

        $entityManager->flush();


        // update purchase return invoice
        $amount = $purchaseReturnInvoiceItemRepository->purchaseReturnInvoiceHtAmount($purchaseReturnInvoice)[0][1];
        $purchaseReturnInvoice->setAmount($amount);

        // get purchase return invoice item discounts from purchase return invoice
        $purchaseReturnInvoiceItemDiscounts = $purchaseReturnInvoiceItemDiscountRepository->findBy(['purchaseReturnInvoice' => $purchaseReturnInvoice]);
        $totalDiscountAmount = 0;
        if($purchaseReturnInvoiceItemDiscounts)
        {
            foreach ($purchaseReturnInvoiceItemDiscounts as $purchaseReturnInvoiceItemDiscount)
            {
                $totalDiscountAmount += $purchaseReturnInvoiceItemDiscount->getAmount();
            }
        }

        // get purchase return invoice item taxes from purchase return invoice
        $purchaseReturnInvoiceItemTaxes = $purchaseReturnInvoiceItemTaxRepository->findBy(['purchaseReturnInvoice' => $purchaseReturnInvoice]);
        $totalTaxAmount = 0;
        if($purchaseReturnInvoiceItemTaxes)
        {
            foreach ($purchaseReturnInvoiceItemTaxes as $purchaseReturnInvoiceItemTax)
            {
                $totalTaxAmount += $purchaseReturnInvoiceItemTax->getAmount();
            }
        }

        $amountTtc = $purchaseReturnInvoiceItemRepository->purchaseReturnInvoiceHtAmount($purchaseReturnInvoice)[0][1] + $totalTaxAmount - $totalDiscountAmount;
        $purchaseReturnInvoice->setTtc($amountTtc);
        $purchaseReturnInvoice->setBalance($amountTtc);
        $purchaseReturnInvoice->setVirtualBalance($amountTtc);

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
