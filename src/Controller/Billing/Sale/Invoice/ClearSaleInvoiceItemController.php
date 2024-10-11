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
class ClearSaleInvoiceItemController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
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

        $saleInvoice = $saleInvoiceRepository->findOneBy(['id' => $id]);
        if (!$saleInvoice){
            return new JsonResponse(['hydra:description' => 'Sale Invoice '.$id.' not found.'], 404);
        }

        $saleInvoiceItems = $saleInvoiceItemRepository->findBy(['saleInvoice' => $saleInvoice]);
        foreach ($saleInvoiceItems as $saleInvoiceItem)
        {
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

            // clear sale invoice item stock
            $saleInvoiceItemStocks = $saleInvoiceItemStockRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);
            if ($saleInvoiceItemStocks){
                foreach ($saleInvoiceItemStocks as $saleInvoiceItemStock){
                    $entityManager->remove($saleInvoiceItemStock);
                }
            }

            $entityManager->remove($saleInvoiceItem);
        }

        // update sale invoice
        $saleInvoice->setAmount(0);
        $saleInvoice->setTtc(0);
        $saleInvoice->setBalance(0);
        $saleInvoice->setVirtualBalance(0);

        $entityManager->flush();

        return $this->json(['hydra:member' => $saleInvoice]);
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
