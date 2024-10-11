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
class ClearSaleReturnInvoiceItemController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                             SaleReturnInvoiceItemRepository $saleReturnInvoiceItemRepository,
                             SaleReturnInvoiceItemStockRepository $saleReturnInvoiceItemStockRepository,
                             SaleReturnInvoiceItemTaxRepository $saleReturnInvoiceItemTaxRepository,
                             SaleReturnInvoiceItemDiscountRepository $saleReturnInvoiceItemDiscountRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {
        $id = $request->get('id');

        $saleReturnInvoice = $saleReturnInvoiceRepository->findOneBy(['id' => $id]);
        if (!$saleReturnInvoice){
            return new JsonResponse(['hydra:description' => 'Sale Return Invoice '.$id.' not found.'], 404);
        }

        $saleReturnInvoiceItems = $saleReturnInvoiceItemRepository->findBy(['saleReturnInvoice' => $saleReturnInvoice]);
        foreach ($saleReturnInvoiceItems as $saleReturnInvoiceItem)
        {
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

            // clear sale invoice item stock
            $saleReturnInvoiceItemStocks = $saleReturnInvoiceItemStockRepository->findBy(['saleReturnInvoiceItem' => $saleReturnInvoiceItem]);
            if ($saleReturnInvoiceItemStocks){
                foreach ($saleReturnInvoiceItemStocks as $saleReturnInvoiceItemStock){
                    $entityManager->remove($saleReturnInvoiceItemStock);
                }
            }

            $entityManager->remove($saleReturnInvoiceItem);
        }

        // update sale return invoice
        $saleReturnInvoice->setAmount(0);
        $saleReturnInvoice->setTtc(0);
        $saleReturnInvoice->setBalance(0);
        $saleReturnInvoice->setVirtualBalance(0);

        $entityManager->flush();

        return $this->json(['hydra:member' => $saleReturnInvoice]);
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
