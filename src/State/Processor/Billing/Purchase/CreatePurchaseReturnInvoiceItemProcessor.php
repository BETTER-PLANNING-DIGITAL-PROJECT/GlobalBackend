<?php
namespace App\State\Processor\Billing\Purchase;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Purchase\PurchaseReturnInvoice;
use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItem;
use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItemDiscount;
use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItemStock;
use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItemTax;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemDiscountRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemTaxRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceRepository;
use App\Repository\Inventory\StockRepository;
use App\Repository\Setting\Finance\TaxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class CreatePurchaseReturnInvoiceItemProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly StockRepository $stockRepository,
                                private readonly TaxRepository $taxRepository,
                                private readonly PurchaseReturnInvoiceRepository $purchaseReturnInvoiceRepository,
                                private readonly PurchaseReturnInvoiceItemRepository $purchaseReturnInvoiceItemRepository,
                                private readonly PurchaseReturnInvoiceItemDiscountRepository $purchaseReturnInvoiceItemDiscountRepository,
                                private readonly PurchaseReturnInvoiceItemTaxRepository $purchaseReturnInvoiceItemTaxRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $purchaseReturnInvoiceData = json_decode($this->request->getContent(), true);

        $purchaseReturnInvoice = $this->purchaseReturnInvoiceRepository->find($data->getId());

        if(!$data instanceof PurchaseReturnInvoice)
        {
            return new JsonResponse(['hydra:description' => 'Purchase Return Invoice not found.'], 404);
        }

        if (!is_numeric($purchaseReturnInvoiceData['quantity'])){
            return new JsonResponse(['hydra:description' => 'Quantity must be numeric value.'], 500);
        }
        elseif ($purchaseReturnInvoiceData['quantity'] <= 0){
            return new JsonResponse(['hydra:description' => 'Quantity must be upper than 0.'], 500);
        }

        if (!is_numeric($purchaseReturnInvoiceData['pu'])){
            return new JsonResponse(['hydra:description' => 'Price must be numeric value.'], 500);
        }
        elseif ($purchaseReturnInvoiceData['pu'] <= 0){
            return new JsonResponse(['hydra:description' => 'Price must be upper than 0.'], 500);
        }

        if ($purchaseReturnInvoiceData['discount'] < 0){
            return new JsonResponse(['hydra:description' => 'Discount must be positive value.'], 500);
        }

        $stock = $this->stockRepository->find($this->getIdFromApiResourceId($purchaseReturnInvoiceData['item']));
        if (!$stock){
            return new JsonResponse(['hydra:description' => 'Stock not found!'], 500);
        }

        $amount = $purchaseReturnInvoiceData['quantity'] * $purchaseReturnInvoiceData['pu'];

        $purchaseReturnInvoiceItem = new PurchaseReturnInvoiceItem();

        $purchaseReturnInvoiceItem->setPurchaseReturnInvoice($purchaseReturnInvoice);
        $purchaseReturnInvoiceItem->setItem($stock->getItem());
        $purchaseReturnInvoiceItem->setQuantity($purchaseReturnInvoiceData['quantity']);
        $purchaseReturnInvoiceItem->setReturnQuantity($purchaseReturnInvoiceData['quantity']);
        $purchaseReturnInvoiceItem->setPu($purchaseReturnInvoiceData['pu']);
        $purchaseReturnInvoiceItem->setDiscount($purchaseReturnInvoiceData['discount']);
        $purchaseReturnInvoiceItem->setName($purchaseReturnInvoiceData['name']);
        $purchaseReturnInvoiceItem->setAmount($amount);

        $purchaseReturnInvoiceItem->setUser($this->getUser());
        $purchaseReturnInvoiceItem->setBranch($this->getUser()->getBranch());
        $purchaseReturnInvoiceItem->setInstitution($this->getUser()->getInstitution());
        $purchaseReturnInvoiceItem->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($purchaseReturnInvoiceItem);


        // discount
        $discountAmount =  0;

        // purchase invoice item discount
        if($purchaseReturnInvoiceData['discount'] > 0)
        {
            // discount
            $discountAmount =  ($amount * $purchaseReturnInvoiceData['discount']) / 100;

            // persist discount
            $purchaseReturnInvoiceItemDiscount = new PurchaseReturnInvoiceItemDiscount();
            $purchaseReturnInvoiceItemDiscount->setPurchaseReturnInvoice($purchaseReturnInvoice);
            $purchaseReturnInvoiceItemDiscount->setPurchaseReturnInvoiceItem($purchaseReturnInvoiceItem);
            $purchaseReturnInvoiceItemDiscount->setRate($purchaseReturnInvoiceData['discount']);
            $purchaseReturnInvoiceItemDiscount->setAmount($discountAmount);

            $purchaseReturnInvoiceItemDiscount->setUser($this->getUser());
            $purchaseReturnInvoiceItemDiscount->setBranch($this->getUser()->getBranch());
            $purchaseReturnInvoiceItemDiscount->setInstitution($this->getUser()->getInstitution());
            $purchaseReturnInvoiceItemDiscount->setYear($this->getUser()->getCurrentYear());
            $this->manager->persist($purchaseReturnInvoiceItemDiscount);
        }

        $purchaseReturnInvoiceItem->setDiscountAmount($discountAmount);

        $totalTaxAmount = 0;

        // set purchase return invoice item tax
        if (isset($purchaseReturnInvoiceData['taxes']))
        {
            foreach ($purchaseReturnInvoiceData['taxes'] as $tax){
                // get tax object
                $taxObject = $this->taxRepository->find($this->getIdFromApiResourceId($tax));

                // set tax on purchase return invoice item
                $purchaseReturnInvoiceItem->addTax($taxObject);

                // tax amount
                $taxAmount = ($amount * $taxObject->getRate()) / 100;

                // persist purchase return invoice tax
                $purchaseReturnInvoiceItemTax = new PurchaseReturnInvoiceItemTax();
                $purchaseReturnInvoiceItemTax->setPurchaseReturnInvoice($purchaseReturnInvoice);
                $purchaseReturnInvoiceItemTax->setPurchaseReturnInvoiceItem($purchaseReturnInvoiceItem);
                $purchaseReturnInvoiceItemTax->setTax($taxObject);
                $purchaseReturnInvoiceItemTax->setRate($taxObject->getRate());
                $purchaseReturnInvoiceItemTax->setAmount($taxAmount);

                $purchaseReturnInvoiceItemTax->setUser($this->getUser());
                $purchaseReturnInvoiceItemTax->setBranch($this->getUser()->getBranch());
                $purchaseReturnInvoiceItemTax->setInstitution($this->getUser()->getInstitution());
                $purchaseReturnInvoiceItemTax->setYear($this->getUser()->getCurrentYear());
                $this->manager->persist($purchaseReturnInvoiceItemTax);

                // total tax amount
                $totalTaxAmount += $taxAmount;
            }

        }

        $purchaseReturnInvoiceItem->setAmountWithTaxes($totalTaxAmount);

        $purchaseReturnInvoiceItem->setAmountTtc($amount + $totalTaxAmount - $discountAmount);

        // CHECK IF THAT STOCK IS ALREADY IN CURRENT SALE INVOICE

        $purchaseReturnInvoiceItemStock = new PurchaseReturnInvoiceItemStock();

        $purchaseReturnInvoiceItemStock->setPurchaseReturnInvoiceItem($purchaseReturnInvoiceItem);
        $purchaseReturnInvoiceItemStock->setStock($stock);
        $purchaseReturnInvoiceItemStock->setQuantity($purchaseReturnInvoiceData['quantity']);
        $purchaseReturnInvoiceItemStock->setCreatedAt(new \DateTimeImmutable());
        $purchaseReturnInvoiceItemStock->setUser($this->getUser());
        $purchaseReturnInvoiceItemStock->setYear($this->getUser()->getCurrentYear());
        $purchaseReturnInvoiceItemStock->setBranch($this->getUser()->getBranch());
        $purchaseReturnInvoiceItemStock->setInstitution($this->getUser()->getInstitution());

        $this->manager->persist($purchaseReturnInvoiceItemStock);

        $this->manager->flush();


        // update purchase return invoice
        $amount = $this->purchaseReturnInvoiceItemRepository->purchaseReturnInvoiceHtAmount($purchaseReturnInvoice)[0][1];
        $purchaseReturnInvoice->setAmount($amount);

        // get purchase return invoice item discounts from purchase invoice
        $purchaseReturnInvoiceItemDiscounts = $this->purchaseReturnInvoiceItemDiscountRepository->findBy(['purchaseReturnInvoice' => $purchaseReturnInvoice]);
        $totalDiscountAmount = 0;
        if($purchaseReturnInvoiceItemDiscounts)
        {
            foreach ($purchaseReturnInvoiceItemDiscounts as $purchaseReturnInvoiceItemDiscount)
            {
                $totalDiscountAmount += $purchaseReturnInvoiceItemDiscount->getAmount();
            }
        }

        // get purchase return invoice item taxes from purchase invoice
        $purchaseReturnInvoiceItemTaxes = $this->purchaseReturnInvoiceItemTaxRepository->findBy(['purchaseReturnInvoice' => $purchaseReturnInvoice]);
        $totalTaxAmount = 0;
        if($purchaseReturnInvoiceItemTaxes)
        {
            foreach ($purchaseReturnInvoiceItemTaxes as $purchaseReturnInvoiceItemTax)
            {
                $totalTaxAmount += $purchaseReturnInvoiceItemTax->getAmount();
            }
        }

        $amountTtc = $this->purchaseReturnInvoiceItemRepository->purchaseReturnInvoiceHtAmount($purchaseReturnInvoice)[0][1] + $totalTaxAmount - $totalDiscountAmount;
        $purchaseReturnInvoice->setTtc($amountTtc);
        $purchaseReturnInvoice->setBalance($amountTtc);
        $purchaseReturnInvoice->setVirtualBalance($amountTtc);

        $this->manager->flush();

        return $this->processor->process($purchaseReturnInvoiceItem, $operation, $uriVariables, $context);
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
