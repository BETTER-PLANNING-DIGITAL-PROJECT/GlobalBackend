<?php

namespace App\Controller\Billing\Purchase\Return;

use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItemDiscount;
use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItemTax;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemDiscountRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemStockRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemTaxRepository;
use App\Repository\Setting\Finance\TaxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class EditPurchaseReturnInvoiceItemController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(PurchaseReturnInvoiceItemRepository $purchaseReturnInvoiceItemRepository,
                             PurchaseReturnInvoiceItemStockRepository $purchaseReturnInvoiceItemStockRepository,
                             PurchaseReturnInvoiceItemDiscountRepository $purchaseReturnInvoiceItemDiscountRepository,
                             PurchaseReturnInvoiceItemTaxRepository $purchaseReturnInvoiceItemTaxRepository,
                             TaxRepository $taxRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        //$request = Request::createFromGlobals();
        $purchaseReturnInvoiceItemData = json_decode($request->getContent(), true);

        $id = $request->get('id');

        $purchaseReturnInvoiceItem = $purchaseReturnInvoiceItemRepository->findOneBy(['id' => $id]);
        if (!$purchaseReturnInvoiceItem){
            return new JsonResponse(['hydra:description' => 'This purchase return invoice item '.$id.' is not found.'], 404);
        }

        // receive data from url
        if (!is_numeric($purchaseReturnInvoiceItemData['quantity'])){
            return new JsonResponse(['hydra:description' => 'Quantity must be numeric value.'], 500);
        }
        elseif ($purchaseReturnInvoiceItemData['quantity'] <= 0){
            return new JsonResponse(['hydra:description' => 'Quantity must be upper than 0.'], 500);
        }

        if($purchaseReturnInvoiceItem->getPurchaseReturnInvoice()->getPurchaseInvoice())
        {
            if($purchaseReturnInvoiceItem->getReturnQuantity() < $purchaseReturnInvoiceItemData['quantity'])
            {
                return new JsonResponse(['hydra:description' => 'Quantity can not be more than sold quantity.'], 500);
            }
        }

        if (!is_string($purchaseReturnInvoiceItemData['pu']) && ($purchaseReturnInvoiceItemData['pu'] > 0))
        {
            if (!is_numeric($purchaseReturnInvoiceItemData['pu'])){
                return new JsonResponse(['hydra:description' => 'Price must be numeric value.'], 500);
            }
            elseif ($purchaseReturnInvoiceItemData['pu'] <= 0){
                return new JsonResponse(['hydra:description' => 'Price must be upper than 0.'], 500);
            }

            $amount = $purchaseReturnInvoiceItemData['quantity'] * $purchaseReturnInvoiceItemData['pu'];
            $pu = $purchaseReturnInvoiceItemData['pu'];
        }
        else{
            if (!is_numeric($purchaseReturnInvoiceItemData['totalAmount'])){
                return new JsonResponse(['hydra:description' => 'Total amount must be numeric value.'], 500);
            }
            elseif ($purchaseReturnInvoiceItemData['totalAmount'] <= 0){
                return new JsonResponse(['hydra:description' => 'Total amount must be upper than 0.'], 500);
            }

            $amount = $purchaseReturnInvoiceItemData['totalAmount'];
            $pu = $purchaseReturnInvoiceItemData['totalAmount'] / $purchaseReturnInvoiceItemData['quantity'];
        }

        /*if (isset($purchaseReturnInvoiceItemData['pu']) && !is_numeric($purchaseReturnInvoiceItemData['pu'])){
            return new JsonResponse(['hydra:description' => 'Price must be numeric value.'], 500);
        }
        elseif (isset($purchaseReturnInvoiceItemData['pu']) && $purchaseReturnInvoiceItemData['pu'] <= 0){
            return new JsonResponse(['hydra:description' => 'Price must be upper than 0.'], 500);
        }*/

        $purchaseReturnInvoiceItemStock = $purchaseReturnInvoiceItemStockRepository->findOneBy(['purchaseReturnInvoiceItem' => $purchaseReturnInvoiceItem]);
        if (!$purchaseReturnInvoiceItemStock){
            return new JsonResponse(['hydra:description' => 'Purchase return invoice item stock not found.'], 404);
        }

        // $amount = $purchaseReturnInvoiceItemData['quantity'] * $purchaseReturnInvoiceItemData['pu'];

        // remove all the previous discount
        $purchaseReturnInvoiceItemDiscounts = $purchaseReturnInvoiceItemDiscountRepository->findBy(['purchaseReturnInvoiceItem' => $purchaseReturnInvoiceItem]);
        if($purchaseReturnInvoiceItemDiscounts)
        {
            foreach ($purchaseReturnInvoiceItemDiscounts as $purchaseReturnInvoiceItemDiscount)
            {
                $entityManager->remove($purchaseReturnInvoiceItemDiscount);
            }
            $purchaseReturnInvoiceItem->setDiscount(0);
            $entityManager->flush();
        }
        // remove all the previous discount end

        // remove all the previous taxes
        $purchaseReturnInvoiceItemTaxes = $purchaseReturnInvoiceItemTaxRepository->findBy(['purchaseReturnInvoiceItem' => $purchaseReturnInvoiceItem]);
        if($purchaseReturnInvoiceItemTaxes)
        {
            foreach ($purchaseReturnInvoiceItemTaxes as $purchaseReturnInvoiceItemTax)
            {
                $purchaseReturnInvoiceItem->removeTax($purchaseReturnInvoiceItemTax->getTax());
                $entityManager->remove($purchaseReturnInvoiceItemTax);
            }
            $entityManager->flush();
        }
        // remove all the previous taxes end


        // update purchase return invoice item
        $purchaseReturnInvoiceItem->setQuantity($purchaseReturnInvoiceItemData['quantity']);
        if(!$purchaseReturnInvoiceItem->getPurchaseReturnInvoice()->getPurchaseInvoice())
        {
            $purchaseReturnInvoiceItem->setReturnQuantity($purchaseReturnInvoiceItemData['quantity']);
        }
        $purchaseReturnInvoiceItem->setPu($pu);
        $purchaseReturnInvoiceItem->setDiscount($purchaseReturnInvoiceItemData['discount']);
        $purchaseReturnInvoiceItem->setName($purchaseReturnInvoiceItemData['name']);
        $purchaseReturnInvoiceItem->setAmount($amount);


        // discount
        $discountAmount =  0;

        // purchase invoice item discount
        if($purchaseReturnInvoiceItemData['discount'] > 0)
        {
            // discount
            $discountAmount =  ($amount * $purchaseReturnInvoiceItemData['discount']) / 100;

            // persist discount
            $purchaseReturnInvoiceItemDiscount = new PurchaseReturnInvoiceItemDiscount();
            $purchaseReturnInvoiceItemDiscount->setPurchaseReturnInvoice($purchaseReturnInvoiceItem->getPurchaseReturnInvoice());
            $purchaseReturnInvoiceItemDiscount->setPurchaseReturnInvoiceItem($purchaseReturnInvoiceItem);
            $purchaseReturnInvoiceItemDiscount->setRate($purchaseReturnInvoiceItemData['discount']);
            $purchaseReturnInvoiceItemDiscount->setAmount($discountAmount);

            $purchaseReturnInvoiceItemDiscount->setUser($this->getUser());
            $purchaseReturnInvoiceItemDiscount->setBranch($this->getUser()->getBranch());
            $purchaseReturnInvoiceItemDiscount->setInstitution($this->getUser()->getInstitution());
            $purchaseReturnInvoiceItemDiscount->setYear($this->getUser()->getCurrentYear());
            $entityManager->persist($purchaseReturnInvoiceItemDiscount);
        }

        $purchaseReturnInvoiceItem->setDiscountAmount($discountAmount);

        $totalTaxAmount = 0;

        // set purchase return invoice item tax
        if (isset($purchaseReturnInvoiceItemData['taxes']))
        {
            foreach ($purchaseReturnInvoiceItemData['taxes'] as $tax){
                // get tax object
                $taxObject = $taxRepository->find($this->getIdFromApiResourceId($tax));

                // set tax on purchase return invoice item
                $purchaseReturnInvoiceItem->addTax($taxObject);

                // tax amount
                $taxAmount = ($amount * $taxObject->getRate()) / 100;

                // persist purchase return invoice tax
                $purchaseReturnInvoiceItemTax = new PurchaseReturnInvoiceItemTax();
                $purchaseReturnInvoiceItemTax->setPurchaseReturnInvoice($purchaseReturnInvoiceItem->getPurchaseReturnInvoice());
                $purchaseReturnInvoiceItemTax->setPurchaseReturnInvoiceItem($purchaseReturnInvoiceItem);
                $purchaseReturnInvoiceItemTax->setTax($taxObject);
                $purchaseReturnInvoiceItemTax->setRate($taxObject->getRate());
                $purchaseReturnInvoiceItemTax->setAmount($taxAmount);

                $purchaseReturnInvoiceItemTax->setUser($this->getUser());
                $purchaseReturnInvoiceItemTax->setBranch($this->getUser()->getBranch());
                $purchaseReturnInvoiceItemTax->setInstitution($this->getUser()->getInstitution());
                $purchaseReturnInvoiceItemTax->setYear($this->getUser()->getCurrentYear());
                $entityManager->persist($purchaseReturnInvoiceItemTax);

                // total tax amount
                $totalTaxAmount += $taxAmount;
            }

        }

        $purchaseReturnInvoiceItem->setAmountWithTaxes($totalTaxAmount);
        $purchaseReturnInvoiceItem->setAmountTtc($amount + $totalTaxAmount - $discountAmount);


        // update purchase return invoice item stock
        $purchaseReturnInvoiceItemStock->setQuantity($purchaseReturnInvoiceItemData['quantity']);

        $entityManager->flush();



        // update purchase return invoice
        $purchaseReturnInvoice = $purchaseReturnInvoiceItem->getPurchaseReturnInvoice();

        $amount = $purchaseReturnInvoiceItemRepository->purchaseReturnInvoiceHtAmount($purchaseReturnInvoice)[0][1];
        $purchaseReturnInvoice->setAmount($amount);

        // get purchase return invoice item discounts from purchase invoice
        $purchaseReturnInvoiceItemDiscounts = $purchaseReturnInvoiceItemDiscountRepository->findBy(['purchaseReturnInvoice' => $purchaseReturnInvoice]);
        $totalDiscountAmount = 0;
        if($purchaseReturnInvoiceItemDiscounts)
        {
            foreach ($purchaseReturnInvoiceItemDiscounts as $purchaseReturnInvoiceItemDiscount)
            {
                $totalDiscountAmount += $purchaseReturnInvoiceItemDiscount->getAmount();
            }
        }

        // get purchase return invoice item taxes from purchase invoice
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

        return $this->json(['hydra:member' => $purchaseReturnInvoiceItem]);
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
