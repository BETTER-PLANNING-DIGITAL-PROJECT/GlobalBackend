<?php

namespace App\Controller\Billing\Sale\Invoice;

use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemDiscountRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemTaxRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class DeleteSaleInvoiceItemController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceItemStockRepository $saleInvoiceItemStockRepository,
                             SaleInvoiceItemTaxRepository $saleInvoiceItemTaxRepository,
                             SaleInvoiceItemDiscountRepository $saleInvoiceItemDiscountRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $saleInvoiceItem = $saleInvoiceItemRepository->findOneBy(['id' => $id]);
        if (!$saleInvoiceItem){
            return new JsonResponse(['hydra:description' => 'This sale invoice item '.$id.' is not found.'], 404);
        }

        $saleInvoice = $saleInvoiceItem->getSaleInvoice();

        // clear sale invoice item discount
        $saleInvoiceItemDiscounts = $saleInvoiceItemDiscountRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);
        if ($saleInvoiceItemDiscounts){
            foreach ($saleInvoiceItemDiscounts as $saleInvoiceItemDiscount){
                $entityManager->remove($saleInvoiceItemDiscount);
            }
        }

        // clear sale invoice item tax
        $saleInvoiceItemTaxes = $saleInvoiceItemTaxRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);
        if ($saleInvoiceItemTaxes){
            foreach ($saleInvoiceItemTaxes as $saleInvoiceItemTax){
                $entityManager->remove($saleInvoiceItemTax);
            }
        }

        $saleInvoiceItemStocks = $saleInvoiceItemStockRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);
        if ($saleInvoiceItemStocks){
            foreach ($saleInvoiceItemStocks as $saleInvoiceItemStock){
                $entityManager->remove($saleInvoiceItemStock);
            }
        }

        $entityManager->remove($saleInvoiceItem);

        $entityManager->flush();


        // update sale invoice
        $amount = $saleInvoiceItemRepository->saleInvoiceHtAmount($saleInvoice)[0][1];
        $saleInvoice->setAmount($amount);

        // get sale invoice item discounts from sale invoice
        $saleInvoiceItemDiscounts = $saleInvoiceItemDiscountRepository->findBy(['saleInvoice' => $saleInvoice]);
        $totalDiscountAmount = 0;
        if($saleInvoiceItemDiscounts)
        {
            foreach ($saleInvoiceItemDiscounts as $saleInvoiceItemDiscount)
            {
                $totalDiscountAmount += $saleInvoiceItemDiscount->getAmount();
            }
        }

        // get sale invoice item taxes from sale invoice
        $saleInvoiceItemTaxes = $saleInvoiceItemTaxRepository->findBy(['saleInvoice' => $saleInvoice]);
        $totalTaxAmount = 0;
        if($saleInvoiceItemTaxes)
        {
            foreach ($saleInvoiceItemTaxes as $saleInvoiceItemTax)
            {
                $totalTaxAmount += $saleInvoiceItemTax->getAmount();
            }
        }

        $amountTtc = $saleInvoiceItemRepository->saleInvoiceHtAmount($saleInvoice)[0][1] + $totalTaxAmount - $totalDiscountAmount;
        $saleInvoice->setTtc($amountTtc);
        $saleInvoice->setBalance($amountTtc);
        $saleInvoice->setVirtualBalance($amountTtc);

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
