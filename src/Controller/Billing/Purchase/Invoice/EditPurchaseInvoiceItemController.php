<?php

namespace App\Controller\Billing\Purchase\Invoice;

use App\Entity\Billing\Purchase\PurchaseInvoiceItemDiscount;
use App\Entity\Billing\Purchase\PurchaseInvoiceItemTax;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemDiscountRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemTaxRepository;
use App\Repository\Setting\Finance\TaxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class EditPurchaseInvoiceItemController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('api/edit/purchase/invoice/item/{id}', name: 'edit_purchase_invoice_item')]
    public function editItem($id, PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                             PurchaseInvoiceItemTaxRepository $purchaseInvoiceItemTaxRepository,
                             PurchaseInvoiceItemDiscountRepository $purchaseInvoiceItemDiscountRepository,
                             TaxRepository $taxRepository,
                             Request $request): JsonResponse
    {
        $request = Request::createFromGlobals();

        $purchaseInvoiceItemData = json_decode($request->getContent(), true);

        $purchaseInvoiceItem = $purchaseInvoiceItemRepository->findOneBy(['id' => $id]);
        if (!$purchaseInvoiceItem){
            return new JsonResponse(['hydra:description' => 'This purchase invoice item '.$id.' is not found.'], 404);
        }

        $purchaseInvoice = $purchaseInvoiceItem->getPurchaseInvoice();

        // receive data from url
        if (!is_numeric($purchaseInvoiceItemData['quantity'])){
            return new JsonResponse(['hydra:description' => 'Quantity must be numeric value.'], 500);
        }
        if ($purchaseInvoiceItemData['quantity'] <= 0){
            return new JsonResponse(['hydra:description' => 'Quantity must be upper than 0.'], 500);
        }

        if (isset($purchaseInvoiceItemData['pu']))
        {
            if (!is_numeric($purchaseInvoiceItemData['pu'])){
                return new JsonResponse(['hydra:description' => 'Price must be numeric value.'], 500);
            }
            elseif ($purchaseInvoiceItemData['pu'] <= 0){
                return new JsonResponse(['hydra:description' => 'Price must be upper than 0.'], 500);
            }

            $amount = $purchaseInvoiceItemData['quantity'] * $purchaseInvoiceItemData['pu'];
            $pu = $purchaseInvoiceItemData['pu'];
        }
        else{
            if (!is_numeric($purchaseInvoiceItemData['totalAmount'])){
                return new JsonResponse(['hydra:description' => 'Total amount must be numeric value.'], 500);
            }
            elseif ($purchaseInvoiceItemData['totalAmount'] <= 0){
                return new JsonResponse(['hydra:description' => 'Total amount must be upper than 0.'], 500);
            }

            $amount = $purchaseInvoiceItemData['totalAmount'];
            $pu = $purchaseInvoiceItemData['totalAmount'] / $purchaseInvoiceItemData['quantity'];
        }

        /*if (isset($purchaseInvoiceItemData['pu']) && !is_numeric($purchaseInvoiceItemData['pu'])){
            return new JsonResponse(['hydra:description' => 'Price must be numeric value.'], 500);
        }
        if (isset($purchaseInvoiceItemData['pu']) && $purchaseInvoiceItemData['pu'] <= 0){
            return new JsonResponse(['hydra:description' => 'Price must be upper than 0.'], 500);
        }*/

        // remove all the previous discount
        // get sale invoice item discounts from sale invoice item
        $purchaseInvoiceItemDiscounts = $purchaseInvoiceItemDiscountRepository->findBy(['purchaseInvoiceItem' => $purchaseInvoiceItem]);
        if($purchaseInvoiceItemDiscounts)
        {
            foreach ($purchaseInvoiceItemDiscounts as $purchaseInvoiceItemDiscount)
            {
                $this->entityManager->remove($purchaseInvoiceItemDiscount);
            }
            $purchaseInvoiceItem->setDiscount(0);
            $this->entityManager->flush();
        }
        // remove all the previous discount end

        // remove all the previous taxes
        // get sale invoice item taxes from sale invoice item
        $purchaseInvoiceItemTaxes = $purchaseInvoiceItemTaxRepository->findBy(['purchaseInvoiceItem' => $purchaseInvoiceItem]);
        if($purchaseInvoiceItemTaxes)
        {
            foreach ($purchaseInvoiceItemTaxes as $purchaseInvoiceItemTax)
            {
                $purchaseInvoiceItem->removeTax($purchaseInvoiceItemTax->getTax());
                $this->entityManager->remove($purchaseInvoiceItemTax);
            }
            $this->entityManager->flush();
        }
        // remove all the previous taxes end

        // start modification
        // $amount = $purchaseInvoiceItemData['quantity'] * $purchaseInvoiceItemData['pu'];

        // update sale invoice item
        $purchaseInvoiceItem->setQuantity($purchaseInvoiceItemData['quantity']);
        $purchaseInvoiceItem->setPu($pu);
        $purchaseInvoiceItem->setDiscount($purchaseInvoiceItemData['discount']);
        $purchaseInvoiceItem->setName($purchaseInvoiceItemData['name']);
        $purchaseInvoiceItem->setAmount($amount);

        // update sale invoice item discount
        $discountAmount =  0;

        // sale invoice item discount
        if($purchaseInvoiceItemData['discount'] > 0)
        {
            // discount
            $discountAmount =  ($amount * $purchaseInvoiceItemData['discount']) / 100;

            // persist discount
            $purchaseInvoiceItemDiscount = new PurchaseInvoiceItemDiscount();
            $purchaseInvoiceItemDiscount->setPurchaseInvoice($purchaseInvoice);
            $purchaseInvoiceItemDiscount->setPurchaseInvoiceItem($purchaseInvoiceItem);
            $purchaseInvoiceItemDiscount->setRate($purchaseInvoiceItemData['discount']);
            $purchaseInvoiceItemDiscount->setAmount($discountAmount);

            $purchaseInvoiceItemDiscount->setUser($this->getUser());
            $purchaseInvoiceItemDiscount->setBranch($this->getUser()->getBranch());
            $purchaseInvoiceItemDiscount->setInstitution($this->getUser()->getInstitution());
            $purchaseInvoiceItemDiscount->setYear($this->getUser()->getCurrentYear());
            $this->entityManager->persist($purchaseInvoiceItemDiscount);
        }

        $purchaseInvoiceItem->setDiscountAmount($discountAmount);


        // update sale invoice item tax
        $totalTaxAmount = 0;

        // set sale invoice item tax
        if (isset($purchaseInvoiceItemData['taxes']))
        {
            foreach ($purchaseInvoiceItemData['taxes'] as $tax){
                // get tax object
                $taxObject = $taxRepository->find($this->getIdFromApiResourceId($tax));

                // set tax on sale invoice item
                $purchaseInvoiceItem->addTax($taxObject);

                // tax amount
                $taxAmount = ($amount * $taxObject->getRate()) / 100;

                // persist sale invoice tax
                $purchaseInvoiceItemTax = new PurchaseInvoiceItemTax();
                $purchaseInvoiceItemTax->setPurchaseInvoice($purchaseInvoice);
                $purchaseInvoiceItemTax->setPurchaseInvoiceItem($purchaseInvoiceItem);
                $purchaseInvoiceItemTax->setTax($taxObject);
                $purchaseInvoiceItemTax->setRate($taxObject->getRate());
                $purchaseInvoiceItemTax->setAmount($taxAmount);

                $purchaseInvoiceItemTax->setUser($this->getUser());
                $purchaseInvoiceItemTax->setBranch($this->getUser()->getBranch());
                $purchaseInvoiceItemTax->setInstitution($this->getUser()->getInstitution());
                $purchaseInvoiceItemTax->setYear($this->getUser()->getCurrentYear());
                $this->entityManager->persist($purchaseInvoiceItemTax);

                // total tax amount
                $totalTaxAmount += $taxAmount;
            }

        }

        $purchaseInvoiceItem->setTaxAmount($totalTaxAmount);

        $purchaseInvoiceItem->setAmountTtc($amount + $totalTaxAmount - $discountAmount);

        $this->entityManager->flush();


        // update purchase invoice
        $amount = $purchaseInvoiceItemRepository->purchaseInvoiceHtAmount($purchaseInvoice)[0][1];
        $purchaseInvoice->setAmount($amount);

        // get purchase invoice item discounts from purchase invoice
        $purchaseInvoiceItemDiscounts = $purchaseInvoiceItemDiscountRepository->findBy(['purchaseInvoice' => $purchaseInvoice]);
        $totalDiscountAmount = 0;
        if($purchaseInvoiceItemDiscounts)
        {
            foreach ($purchaseInvoiceItemDiscounts as $purchaseInvoiceItemDiscount)
            {
                $totalDiscountAmount += $purchaseInvoiceItemDiscount->getAmount();
            }
        }

        // get purchase invoice item taxes from purchase invoice
        $purchaseInvoiceItemTaxes = $purchaseInvoiceItemTaxRepository->findBy(['purchaseInvoice' => $purchaseInvoice]);
        $totalTaxAmount = 0;
        if($purchaseInvoiceItemTaxes)
        {
            foreach ($purchaseInvoiceItemTaxes as $purchaseInvoiceItemTax)
            {
                $totalTaxAmount += $purchaseInvoiceItemTax->getAmount();
            }
        }

        $amountTtc = $purchaseInvoiceItemRepository->purchaseInvoiceHtAmount($purchaseInvoice)[0][1] + $totalTaxAmount - $totalDiscountAmount;
        $purchaseInvoice->setTtc($amountTtc);
        $purchaseInvoice->setBalance($amountTtc);
        $purchaseInvoice->setVirtualBalance($amountTtc);

        $this->entityManager->flush();

        return $this->json(['hydra:member' => $purchaseInvoiceItem]);

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
