<?php

namespace App\Controller\Billing\Sale\Return;

use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemDiscountRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemTaxRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class DeleteSaleReturnInvoiceItemController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleReturnInvoiceItemRepository $saleReturnInvoiceItemRepository,
                             SaleReturnInvoiceItemStockRepository $saleReturnInvoiceItemStockRepository,
                             SaleReturnInvoiceItemDiscountRepository $saleReturnInvoiceItemDiscountRepository,
                             SaleReturnInvoiceItemTaxRepository $saleReturnInvoiceItemTaxRepository,
                             SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $saleReturnInvoiceItem = $saleReturnInvoiceItemRepository->findOneBy(['id' => $id]);
        if (!$saleReturnInvoiceItem){
            return new JsonResponse(['hydra:description' => 'This sale return invoice item '.$id.' is not found.'], 404);
        }

        $saleReturnInvoice = $saleReturnInvoiceItem->getSaleReturnInvoice();

        // clear sale return invoice item discount
        $saleReturnInvoiceItemDiscounts = $saleReturnInvoiceItemDiscountRepository->findBy(['saleReturnInvoiceItem' => $saleReturnInvoiceItem]);
        if ($saleReturnInvoiceItemDiscounts){
            foreach ($saleReturnInvoiceItemDiscounts as $saleReturnInvoiceItemDiscount){
                $entityManager->remove($saleReturnInvoiceItemDiscount);
            }
        }

        // clear sale return invoice item tax
        $saleReturnInvoiceItemTaxes = $saleReturnInvoiceItemTaxRepository->findBy(['saleReturnInvoiceItem' => $saleReturnInvoiceItem]);
        if ($saleReturnInvoiceItemTaxes){
            foreach ($saleReturnInvoiceItemTaxes as $saleReturnInvoiceItemTax){
                $entityManager->remove($saleReturnInvoiceItemTax);
            }
        }

        $saleReturnInvoiceItemStocks = $saleReturnInvoiceItemStockRepository->findBy(['saleReturnInvoiceItem' => $saleReturnInvoiceItem]);
        if ($saleReturnInvoiceItemStocks){
            foreach ($saleReturnInvoiceItemStocks as $saleReturnInvoiceItemStock){
                $entityManager->remove($saleReturnInvoiceItemStock);
            }
        }

        $entityManager->remove($saleReturnInvoiceItem);

        $entityManager->flush();


        // update sale return invoice
        $amount = $saleReturnInvoiceItemRepository->saleReturnInvoiceHtAmount($saleReturnInvoice)[0][1];
        $saleReturnInvoice->setAmount($amount);

        // get sale return invoice item discounts from sale return invoice
        $saleReturnInvoiceItemDiscounts = $saleReturnInvoiceItemDiscountRepository->findBy(['saleReturnInvoice' => $saleReturnInvoice]);
        $totalDiscountAmount = 0;
        if($saleReturnInvoiceItemDiscounts)
        {
            foreach ($saleReturnInvoiceItemDiscounts as $saleReturnInvoiceItemDiscount)
            {
                $totalDiscountAmount += $saleReturnInvoiceItemDiscount->getAmount();
            }
        }

        // get sale return invoice item taxes from sale return invoice
        $saleReturnInvoiceItemTaxes = $saleReturnInvoiceItemTaxRepository->findBy(['saleReturnInvoice' => $saleReturnInvoice]);
        $totalTaxAmount = 0;
        if($saleReturnInvoiceItemTaxes)
        {
            foreach ($saleReturnInvoiceItemTaxes as $saleReturnInvoiceItemTax)
            {
                $totalTaxAmount += $saleReturnInvoiceItemTax->getAmount();
            }
        }

        $amountTtc = $saleReturnInvoiceItemRepository->saleReturnInvoiceHtAmount($saleReturnInvoice)[0][1] + $totalTaxAmount - $totalDiscountAmount;
        $saleReturnInvoice->setTtc($amountTtc);
        $saleReturnInvoice->setBalance($amountTtc);
        $saleReturnInvoice->setVirtualBalance($amountTtc);

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
