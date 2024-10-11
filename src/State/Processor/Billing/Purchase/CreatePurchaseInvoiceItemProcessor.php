<?php
namespace App\State\Processor\Billing\Purchase;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Purchase\PurchaseInvoice;
use App\Entity\Billing\Purchase\PurchaseInvoiceItem;
use App\Entity\Billing\Purchase\PurchaseInvoiceItemDiscount;
use App\Entity\Billing\Purchase\PurchaseInvoiceItemTax;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemDiscountRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemTaxRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Product\ItemRepository;
use App\Repository\Setting\Finance\TaxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class CreatePurchaseInvoiceItemProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly TaxRepository $taxRepository,
                                private readonly ItemRepository $itemRepository,
                                private readonly PurchaseInvoiceRepository $purchaseInvoiceRepository,
                                private readonly PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                                private readonly PurchaseInvoiceItemDiscountRepository $purchaseInvoiceItemDiscountRepository,
                                private readonly PurchaseInvoiceItemTaxRepository $purchaseInvoiceItemTaxRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $purchaseInvoiceData = json_decode($this->request->getContent(), true);

        $purchaseInvoice = $this->purchaseInvoiceRepository->find($data->getId());
        if(!$data instanceof PurchaseInvoice)
        {
            return new JsonResponse(['hydra:description' => 'Purchase Invoice not found.'], 404);
        }

        $item = $this->itemRepository->find($this->getIdFromApiResourceId($purchaseInvoiceData['item']));
        if (!$item){
            return new JsonResponse(['hydra:description' => 'Item not found!'], 500);
        }

        if (!is_numeric($purchaseInvoiceData['quantity'])){
            return new JsonResponse(['hydra:description' => 'Quantity must be numeric value.'], 500);
        }
        elseif ($purchaseInvoiceData['quantity'] <= 0){
            return new JsonResponse(['hydra:description' => 'Quantity must be upper than 0.'], 500);
        }

        if (isset($purchaseInvoiceData['pu']))
        {
            if (!is_numeric($purchaseInvoiceData['pu'])){
                return new JsonResponse(['hydra:description' => 'Price must be numeric value.'], 500);
            }
            elseif ($purchaseInvoiceData['pu'] <= 0){
                return new JsonResponse(['hydra:description' => 'Price must be upper than 0.'], 500);
            }

            $amount = $purchaseInvoiceData['quantity'] * $purchaseInvoiceData['pu'];
            $pu = $purchaseInvoiceData['pu'];
        }
        else{
            if (!is_numeric($purchaseInvoiceData['totalAmount'])){
                return new JsonResponse(['hydra:description' => 'Total amount must be numeric value.'], 500);
            }
            elseif ($purchaseInvoiceData['totalAmount'] <= 0){
                return new JsonResponse(['hydra:description' => 'Total amount must be upper than 0.'], 500);
            }

            $amount = $purchaseInvoiceData['totalAmount'];
            $pu = $purchaseInvoiceData['totalAmount'] / $purchaseInvoiceData['quantity'];
        }

        /*if (is_numeric($purchaseInvoiceData['totalAmount'])){
            if ($purchaseInvoiceData['totalAmount'] > 0){
                // consider total amount
                $amount = $purchaseInvoiceData['totalAmount'];
                $pu = $purchaseInvoiceData['totalAmount'] / $purchaseInvoiceData['quantity'];
            }
            else{
                $amount = $purchaseInvoiceData['quantity'] * $purchaseInvoiceData['pu'];
                $pu = $purchaseInvoiceData['pu'];
            }
        }*/

        if ($purchaseInvoiceData['discount'] < 0){
            return new JsonResponse(['hydra:description' => 'Discount must be positive value.'], 500);
        }

        $purchaseInvoiceItem = new PurchaseInvoiceItem();

        $purchaseInvoiceItem->setItem($item);
        $purchaseInvoiceItem->setQuantity($purchaseInvoiceData['quantity']);
        $purchaseInvoiceItem->setReturnQuantity($purchaseInvoiceData['quantity']);
        $purchaseInvoiceItem->setPu($pu);
        $purchaseInvoiceItem->setDiscount($purchaseInvoiceData['discount']);
        $purchaseInvoiceItem->setPurchaseInvoice($purchaseInvoice);
        $purchaseInvoiceItem->setName($purchaseInvoiceData['name']);
        $purchaseInvoiceItem->setAmount($amount);

        $purchaseInvoiceItem->setUser($this->getUser());
        $purchaseInvoiceItem->setBranch($this->getUser()->getBranch());
        $purchaseInvoiceItem->setInstitution($this->getUser()->getInstitution());
        $purchaseInvoiceItem->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($purchaseInvoiceItem);

        // discount
        $discountAmount =  0;

        // purchase invoice item discount
        if($purchaseInvoiceData['discount'] > 0)
        {
            // discount
            $discountAmount =  ($amount * $purchaseInvoiceData['discount']) / 100;

            // persist discount
            $purchaseInvoiceItemDiscount = new PurchaseInvoiceItemDiscount();
            $purchaseInvoiceItemDiscount->setPurchaseInvoice($purchaseInvoice);
            $purchaseInvoiceItemDiscount->setPurchaseInvoiceItem($purchaseInvoiceItem);
            $purchaseInvoiceItemDiscount->setRate($purchaseInvoiceData['discount']);
            $purchaseInvoiceItemDiscount->setAmount($discountAmount);

            $purchaseInvoiceItemDiscount->setUser($this->getUser());
            $purchaseInvoiceItemDiscount->setBranch($this->getUser()->getBranch());
            $purchaseInvoiceItemDiscount->setInstitution($this->getUser()->getInstitution());
            $purchaseInvoiceItemDiscount->setYear($this->getUser()->getCurrentYear());
            $this->manager->persist($purchaseInvoiceItemDiscount);
        }

        $purchaseInvoiceItem->setDiscountAmount($discountAmount);

        $totalTaxAmount = 0;

        // set purchase invoice item tax
        if (isset($purchaseInvoiceData['taxes']))
        {
            foreach ($purchaseInvoiceData['taxes'] as $tax){
                // get tax object
                $taxObject = $this->taxRepository->find($this->getIdFromApiResourceId($tax));

                // set tax on purchase invoice item
                $purchaseInvoiceItem->addTax($taxObject);

                // tax amount
                $taxAmount = ($amount * $taxObject->getRate()) / 100;

                // persist purchase invoice tax
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
                $this->manager->persist($purchaseInvoiceItemTax);

                // total tax amount
                $totalTaxAmount += $taxAmount;
            }

        }

        $purchaseInvoiceItem->setTaxAmount($totalTaxAmount);

        $purchaseInvoiceItem->setAmountTtc($amount + $totalTaxAmount - $discountAmount);

        $this->manager->flush();

        // update purchase invoice
        $amount = $this->purchaseInvoiceItemRepository->purchaseInvoiceHtAmount($purchaseInvoice)[0][1];
        $purchaseInvoice->setAmount($amount);

        // get purchase invoice item discounts from purchase invoice
        $purchaseInvoiceItemDiscounts = $this->purchaseInvoiceItemDiscountRepository->findBy(['purchaseInvoice' => $purchaseInvoice]);
        $totalDiscountAmount = 0;
        if($purchaseInvoiceItemDiscounts)
        {
            foreach ($purchaseInvoiceItemDiscounts as $purchaseInvoiceItemDiscount)
            {
                $totalDiscountAmount += $purchaseInvoiceItemDiscount->getAmount();
            }
        }

        // get purchase invoice item taxes from purchase invoice
        $purchaseInvoiceItemTaxes = $this->purchaseInvoiceItemTaxRepository->findBy(['purchaseInvoice' => $purchaseInvoice]);
        $totalTaxAmount = 0;
        if($purchaseInvoiceItemTaxes)
        {
            foreach ($purchaseInvoiceItemTaxes as $purchaseInvoiceItemTax)
            {
                $totalTaxAmount += $purchaseInvoiceItemTax->getAmount();
            }
        }

        $amountTtc = $this->purchaseInvoiceItemRepository->purchaseInvoiceHtAmount($purchaseInvoice)[0][1] + $totalTaxAmount - $totalDiscountAmount;
        $purchaseInvoice->setTtc($amountTtc);
        $purchaseInvoice->setBalance($amountTtc);
        $purchaseInvoice->setVirtualBalance($amountTtc);

        $this->manager->flush();

        return $this->processor->process($purchaseInvoiceItem, $operation, $uriVariables, $context);
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