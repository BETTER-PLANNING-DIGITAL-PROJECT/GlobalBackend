<?php

namespace App\Controller\Billing\Sale\Return;

use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemDiscountRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemTaxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class EditSaleReturnInvoiceItemController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleReturnInvoiceItemRepository $saleReturnInvoiceItemRepository,
                             SaleReturnInvoiceItemStockRepository $saleReturnInvoiceItemStockRepository,
                             SaleReturnInvoiceItemDiscountRepository $saleReturnInvoiceItemDiscountRepository,
                             SaleReturnInvoiceItemTaxRepository $saleReturnInvoiceItemTaxRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        //$request = Request::createFromGlobals();

        $saleReturnInvoiceItemData = json_decode($request->getContent(), true);

        $id = $request->get('id');

        $saleReturnInvoiceItem = $saleReturnInvoiceItemRepository->findOneBy(['id' => $id]);
        if (!$saleReturnInvoiceItem){
            return new JsonResponse(['hydra:description' => 'This sale return invoice item '.$id.' is not found.'], 404);
        }

        // receive data from url
        if (!is_numeric($saleReturnInvoiceItemData['quantity'])){
            return new JsonResponse(['hydra:description' => 'Quantity must be numeric value.'], 500);
        }
        elseif ($saleReturnInvoiceItemData['quantity'] <= 0){
            return new JsonResponse(['hydra:description' => 'Quantity must be upper than 0.'], 500);
        }

        if($saleReturnInvoiceItem->getSaleReturnInvoice()->getSaleInvoice())
        {
            if($saleReturnInvoiceItem->getReturnQuantity() < $saleReturnInvoiceItemData['quantity'])
            {
                return new JsonResponse(['hydra:description' => 'Quantity can not be more than sold quantity.'], 500);
            }
        }

        if (isset($saleReturnInvoiceItemData['pu']) && !is_numeric($saleReturnInvoiceItemData['pu'])){
            return new JsonResponse(['hydra:description' => 'Price must be numeric value.'], 500);
        }
        elseif (isset($saleReturnInvoiceItemData['pu']) && $saleReturnInvoiceItemData['pu'] <= 0){
            return new JsonResponse(['hydra:description' => 'Price must be upper than 0.'], 500);
        }

        $saleReturnInvoiceItemStock = $saleReturnInvoiceItemStockRepository->findOneBy(['saleReturnInvoiceItem' => $saleReturnInvoiceItem]);
        if (!$saleReturnInvoiceItemStock){
            return new JsonResponse(['hydra:description' => 'Sale return invoice item stock not found.'], 404);
        }

        $amount = $saleReturnInvoiceItemData['quantity'] * $saleReturnInvoiceItemData['pu'];

        // update sale return invoice item
        $saleReturnInvoiceItem->setQuantity($saleReturnInvoiceItemData['quantity']);
        if(!$saleReturnInvoiceItem->getSaleReturnInvoice()->getSaleInvoice())
        {
            $saleReturnInvoiceItem->setReturnQuantity($saleReturnInvoiceItemData['quantity']);
        }
        $saleReturnInvoiceItem->setPu($saleReturnInvoiceItemData['pu']);
        $saleReturnInvoiceItem->setName($saleReturnInvoiceItemData['name']);
        $saleReturnInvoiceItem->setAmount($amount);

        // update sale return invoice item discount

        // get sale return invoice item discounts from sale return invoice item
        $saleReturnInvoiceItemDiscounts = $saleReturnInvoiceItemDiscountRepository->findBy(['saleReturnInvoiceItem' => $saleReturnInvoiceItem]);
        $totalDiscountAmount = 0;
        if($saleReturnInvoiceItemDiscounts)
        {
            foreach ($saleReturnInvoiceItemDiscounts as $saleReturnInvoiceItemDiscount)
            {
                $discountAmount =  $amount * $saleReturnInvoiceItemDiscount->getRate() / 100;
                $saleReturnInvoiceItemDiscount->setAmount($discountAmount);

                $totalDiscountAmount += $discountAmount;
            }
        }

        $saleReturnInvoiceItem->setDiscountAmount($totalDiscountAmount);


        // update sale return invoice item tax

        // get sale return invoice item taxes from sale return invoice item
        $saleReturnInvoiceItemTaxes = $saleReturnInvoiceItemTaxRepository->findBy(['saleReturnInvoiceItem' => $saleReturnInvoiceItem]);
        $totalTaxAmount = 0;
        if($saleReturnInvoiceItemTaxes)
        {
            foreach ($saleReturnInvoiceItemTaxes as $saleReturnInvoiceItemTax)
            {
                $taxAmount =  $amount * $saleReturnInvoiceItemTax->getRate() / 100;
                $saleReturnInvoiceItemTax->setAmount($taxAmount);

                $totalTaxAmount += $taxAmount;
            }
        }

        $saleReturnInvoiceItem->setAmountWithTaxes($totalTaxAmount);

        $saleReturnInvoiceItem->setAmountTtc($amount + $totalTaxAmount - $totalDiscountAmount);


        // update sale return invoice item stock
        $saleReturnInvoiceItemStock->setQuantity($saleReturnInvoiceItemData['quantity']);

        $entityManager->flush();


        // update sale return invoice
        $saleReturnInvoice = $saleReturnInvoiceItem->getSaleReturnInvoice();

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

        return $this->json(['hydra:member' => $saleReturnInvoiceItem]);
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
