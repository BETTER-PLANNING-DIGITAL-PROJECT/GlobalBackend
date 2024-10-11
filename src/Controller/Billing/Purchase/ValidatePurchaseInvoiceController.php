<?php

namespace App\Controller\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseInvoice;
use App\Entity\Billing\Purchase\PurchaseInvoiceItemStock;
use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Billing\Sale\SaleInvoiceItemStock;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Inventory\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class ValidatePurchaseInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                             PurchaseInvoiceRepository $purchaseInvoiceRepository,
                             EntityManagerInterface $entityManager,
                             StockRepository $stockRepository,
                             Request $request): JsonResponse
    {


        $id = $request->get('id');

        $purchaseInvoice = $purchaseInvoiceRepository->find($id);

        if(!$purchaseInvoice instanceof PurchaseInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This data must be type of invoice.'], 404);
        }

        if(!$purchaseInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }

        $purchaseInvoiceItem = $purchaseInvoiceItemRepository->findOneBy(['purchaseInvoice' => $purchaseInvoice]);

        if(!$purchaseInvoiceItem)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'Cannot proceed : the cart is empty.'], 404);
        }

        $purchaseInvoice->setStatus('invoice');

        // Debut: Mise a jour des montants
        $amount = $purchaseInvoiceItemRepository->purchaseInvoiceTotalAmount($purchaseInvoice)[0][1];
        $purchaseInvoice->setAmount($amount);

        $taxResult = 0;
        $discountAmount = 0;
        $purchaseInvoiceItems = $purchaseInvoiceItemRepository->findBy(['purchaseInvoice' => $purchaseInvoice]);

        foreach ($purchaseInvoiceItems as $purchaseInvoiceItem){
            if ($purchaseInvoiceItem->getTaxes()){
                foreach ($purchaseInvoiceItem->getTaxes() as $tax){
                    $taxResult = $taxResult + $purchaseInvoiceItem->getAmount() * $tax->getRate() / 100;
                }
            }

            $discountAmount = $discountAmount + $purchaseInvoiceItem->getAmount() * $purchaseInvoiceItem->getDiscount() / 100;
        }

        $amountTtc = $purchaseInvoiceItemRepository->purchaseInvoiceTotalAmount($purchaseInvoice)[0][1] + $taxResult - $discountAmount;

        $purchaseInvoice->setTtc($amountTtc);
        $purchaseInvoice->setBalance($amountTtc);
        $purchaseInvoice->setVirtualBalance($amountTtc);

        // Fin: Mise a jour des montants

        // Validate purchase invoice item stock
        foreach ($purchaseInvoiceItems as $purchaseInvoiceItem)
        {
            // purchase Invoice Item Stock
            $stocks = $stockRepository->findByItemFIFO($purchaseInvoiceItem->getItem());

            $quantity = $purchaseInvoiceItem->getQuantity();

            foreach ($stocks as $stock){

                if($quantity <= $stock->getAvailableQte())
                {
                    $purchaseInvoiceItemStock = new PurchaseInvoiceItemStock();

                    $purchaseInvoiceItemStock->setPurchaseInvoiceItem($purchaseInvoiceItem);
                    $purchaseInvoiceItemStock->setStock($stock);
                    $purchaseInvoiceItemStock->setQuantity($quantity);
                    $purchaseInvoiceItemStock->setUser($this->getUser());
                    $purchaseInvoiceItemStock->setInstitution($this->getUser()->getInstitution());
                    $purchaseInvoiceItemStock->setYear($this->getUser()->getCurrentYear());

                    $entityManager->persist($purchaseInvoiceItemStock);

                    break;
                }
                else if($quantity > $stock->getAvailableQte())
                {
                    $purchaseInvoiceItemStock = new PurchaseInvoiceItemStock();

                    $purchaseInvoiceItemStock->setPurchaseInvoiceItem($purchaseInvoiceItem);
                    $purchaseInvoiceItemStock->setStock($stock);
                    $purchaseInvoiceItemStock->setQuantity($stock->getAvailableQte());

                    $purchaseInvoiceItemStock->setUser($this->getUser());
                    $purchaseInvoiceItemStock->setInstitution($this->getUser()->getInstitution());
                    $purchaseInvoiceItemStock->setYear($this->getUser()->getCurrentYear());

                    $entityManager->persist($purchaseInvoiceItemStock);

                    $quantity = $quantity - $stock->getAvailableQte();
                }

            }
        }

        $entityManager->flush();

        return $this->json(['hydra:member' => '200']);

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
