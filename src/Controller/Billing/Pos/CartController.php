<?php

namespace App\Controller\Billing\Pos;

use App\Entity\Billing\Sale\SaleInvoiceItemDiscount;
use App\Entity\Billing\Sale\SaleInvoiceItemTax;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemDiscountRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemTaxRepository;
use App\Repository\Setting\Finance\TaxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class CartController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('api/edit/sale/invoice/item/{id}', name: 'edit_sale_invoice_item')]
    public function edit($id,
                         SaleInvoiceItemRepository $saleInvoiceItemRepository,
                         SaleInvoiceItemStockRepository $saleInvoiceItemStockRepository,
                         SaleInvoiceItemTaxRepository $saleInvoiceItemTaxRepository,
                         SaleInvoiceItemDiscountRepository $saleInvoiceItemDiscountRepository,
                         TaxRepository $taxRepository,
                         Request $request): JsonResponse
    {
        $request = Request::createFromGlobals();

        $saleInvoiceItemData = json_decode($request->getContent(), true);

        $saleInvoiceItem = $saleInvoiceItemRepository->findOneBy(['id' => $id]);
        if (!$saleInvoiceItem){
            return new JsonResponse(['hydra:description' => 'This sale invoice item '.$id.' is not found.'], 404);
        }

        $saleInvoice = $saleInvoiceItem->getSaleInvoice();

        // receive data from url
        if (!is_numeric($saleInvoiceItemData['quantity'])){
            return new JsonResponse(['hydra:description' => 'Quantity must be numeric value.'], 500);
        }
        if ($saleInvoiceItemData['quantity'] <= 0){
            return new JsonResponse(['hydra:description' => 'Quantity must be upper than 0.'], 500);
        }
        if (isset($saleInvoiceItemData['pu']) && !is_numeric($saleInvoiceItemData['pu'])){
            return new JsonResponse(['hydra:description' => 'Price must be numeric value.'], 500);
        }
        if (isset($saleInvoiceItemData['pu']) && $saleInvoiceItemData['pu'] <= 0){
            return new JsonResponse(['hydra:description' => 'Price must be upper than 0.'], 500);
        }

        $saleInvoiceItemStock = $saleInvoiceItemStockRepository->findOneBy(['saleInvoiceItem' => $saleInvoiceItem]);
        if (!$saleInvoiceItemStock){
            return new JsonResponse(['hydra:description' => 'Sale invoice item stock not found.'], 404);
        }

        $stock = $saleInvoiceItemStock->getStock();
        if ($saleInvoiceItemData['quantity'] > $stock->getAvailableQte()){
            return new JsonResponse(['hydra:description' => 'Request quantity must be less than available quantity.'], 500);
        }

        // remove all the previous discount
        // get sale invoice item discounts from sale invoice item
        $saleInvoiceItemDiscounts = $saleInvoiceItemDiscountRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);
        if($saleInvoiceItemDiscounts)
        {
            foreach ($saleInvoiceItemDiscounts as $saleInvoiceItemDiscount)
            {
                $this->entityManager->remove($saleInvoiceItemDiscount);
            }
            $saleInvoiceItem->setDiscount(0);
            $this->entityManager->flush();
        }
        // remove all the previous discount end

        // remove all the previous taxes
        // get sale invoice item taxes from sale invoice item
        $saleInvoiceItemTaxes = $saleInvoiceItemTaxRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);
        if($saleInvoiceItemTaxes)
        {
            foreach ($saleInvoiceItemTaxes as $saleInvoiceItemTax)
            {
                $saleInvoiceItem->removeTax($saleInvoiceItemTax->getTax());
                $this->entityManager->remove($saleInvoiceItemTax);
            }
            $this->entityManager->flush();
        }
        // remove all the previous taxes end


        // start modification
        $amount = $saleInvoiceItemData['quantity'] * $saleInvoiceItemData['pu'];

        // update sale invoice item
        $saleInvoiceItem->setQuantity($saleInvoiceItemData['quantity']);
        $saleInvoiceItem->setPu($saleInvoiceItemData['pu']);
        $saleInvoiceItem->setDiscount($saleInvoiceItemData['discount']);
        $saleInvoiceItem->setName($saleInvoiceItemData['name']);
        $saleInvoiceItem->setAmount($amount);

        // update sale invoice item discount
        $discountAmount =  0;

        // sale invoice item discount
        if($saleInvoiceItemData['discount'] > 0)
        {
            // discount
            $discountAmount =  ($amount * $saleInvoiceItemData['discount']) / 100;

            // persist discount
            $saleInvoiceItemDiscount = new SaleInvoiceItemDiscount();
            $saleInvoiceItemDiscount->setSaleInvoice($saleInvoice);
            $saleInvoiceItemDiscount->setSaleInvoiceItem($saleInvoiceItem);
            $saleInvoiceItemDiscount->setRate($saleInvoiceItemData['discount']);
            $saleInvoiceItemDiscount->setAmount($discountAmount);

            $saleInvoiceItemDiscount->setUser($this->getUser());
            $saleInvoiceItemDiscount->setBranch($this->getUser()->getBranch());
            $saleInvoiceItemDiscount->setInstitution($this->getUser()->getInstitution());
            $saleInvoiceItemDiscount->setYear($this->getUser()->getCurrentYear());
            $this->entityManager->persist($saleInvoiceItemDiscount);
        }

        $saleInvoiceItem->setDiscountAmount($discountAmount);


        // update sale invoice item tax
        $totalTaxAmount = 0;

        // set sale invoice item tax
        if (isset($saleInvoiceItemData['taxes']))
        {
            foreach ($saleInvoiceItemData['taxes'] as $tax){
                // get tax object
                $taxObject = $taxRepository->find($this->getIdFromApiResourceId($tax));

                // set tax on sale invoice item
                $saleInvoiceItem->addTax($taxObject);

                // tax amount
                $taxAmount = ($amount * $taxObject->getRate()) / 100;

                // persist sale invoice tax
                $saleInvoiceItemTax = new SaleInvoiceItemTax();
                $saleInvoiceItemTax->setSaleInvoice($saleInvoice);
                $saleInvoiceItemTax->setSaleInvoiceItem($saleInvoiceItem);
                $saleInvoiceItemTax->setTax($taxObject);
                $saleInvoiceItemTax->setRate($taxObject->getRate());
                $saleInvoiceItemTax->setAmount($taxAmount);

                $saleInvoiceItemTax->setUser($this->getUser());
                $saleInvoiceItemTax->setBranch($this->getUser()->getBranch());
                $saleInvoiceItemTax->setInstitution($this->getUser()->getInstitution());
                $saleInvoiceItemTax->setYear($this->getUser()->getCurrentYear());
                $this->entityManager->persist($saleInvoiceItemTax);

                // total tax amount
                $totalTaxAmount += $taxAmount;
            }

        }

        $saleInvoiceItem->setAmountWithTaxes($totalTaxAmount);

        $saleInvoiceItem->setAmountTtc($amount + $totalTaxAmount - $discountAmount);


        // update sale invoice item stock
        $saleInvoiceItemStock->setQuantity($saleInvoiceItemData['quantity']);

        $this->entityManager->flush();


        // update sale invoice
        $saleInvoice = $saleInvoiceItem->getSaleInvoice();

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

        $this->entityManager->flush();

        return $this->json(['hydra:member' => $saleInvoiceItem]);
    }

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
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
