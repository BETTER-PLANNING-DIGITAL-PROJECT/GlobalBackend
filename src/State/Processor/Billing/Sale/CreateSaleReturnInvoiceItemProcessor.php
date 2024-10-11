<?php
namespace App\State\Processor\Billing\Sale;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Billing\Sale\SaleReturnInvoiceItem;
use App\Entity\Billing\Sale\SaleReturnInvoiceItemDiscount;
use App\Entity\Billing\Sale\SaleReturnInvoiceItemStock;
use App\Entity\Billing\Sale\SaleReturnInvoiceItemTax;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemDiscountRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemTaxRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use App\Repository\Inventory\StockRepository;
use App\Repository\Setting\Finance\TaxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class CreateSaleReturnInvoiceItemProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly StockRepository $stockRepository,
                                private readonly TaxRepository $taxRepository,
                                private readonly SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                                private readonly SaleReturnInvoiceItemRepository $saleReturnInvoiceItemRepository,
                                private readonly SaleReturnInvoiceItemDiscountRepository $saleReturnInvoiceItemDiscountRepository,
                                private readonly SaleReturnInvoiceItemTaxRepository $saleReturnInvoiceItemTaxRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $saleReturnInvoiceData = json_decode($this->request->getContent(), true);

        $saleReturnInvoice = $this->saleReturnInvoiceRepository->find($data->getId());

        if(!$data instanceof SaleReturnInvoice)
        {
            return new JsonResponse(['hydra:description' => 'Sale Return Invoice not found.'], 404);
        }

        if (!is_numeric($saleReturnInvoiceData['quantity'])){
            return new JsonResponse(['hydra:description' => 'Quantity must be numeric value.'], 500);
        }
        elseif ($saleReturnInvoiceData['quantity'] <= 0){
            return new JsonResponse(['hydra:description' => 'Quantity must be upper than 0.'], 500);
        }

        if (!is_numeric($saleReturnInvoiceData['pu'])){
            return new JsonResponse(['hydra:description' => 'Price must be numeric value.'], 500);
        }
        elseif ($saleReturnInvoiceData['pu'] <= 0){
            return new JsonResponse(['hydra:description' => 'Price must be upper than 0.'], 500);
        }

        if ($saleReturnInvoiceData['discount'] < 0){
            return new JsonResponse(['hydra:description' => 'Discount must be positive value.'], 500);
        }

        $stock = $this->stockRepository->find($this->getIdFromApiResourceId($saleReturnInvoiceData['item']));
        if (!$stock){
            return new JsonResponse(['hydra:description' => 'Stock not found!'], 500);
        }

        $amount = $saleReturnInvoiceData['quantity'] * $saleReturnInvoiceData['pu'];

        $saleReturnInvoiceItem = new SaleReturnInvoiceItem();

        $saleReturnInvoiceItem->setSaleReturnInvoice($saleReturnInvoice);
        $saleReturnInvoiceItem->setItem($stock->getItem());
        $saleReturnInvoiceItem->setQuantity($saleReturnInvoiceData['quantity']);
        $saleReturnInvoiceItem->setReturnQuantity($saleReturnInvoiceData['quantity']);
        $saleReturnInvoiceItem->setPu($saleReturnInvoiceData['pu']);
        $saleReturnInvoiceItem->setDiscount($saleReturnInvoiceData['discount']);
        $saleReturnInvoiceItem->setName($saleReturnInvoiceData['name']);
        $saleReturnInvoiceItem->setAmount($amount);

        $saleReturnInvoiceItem->setUser($this->getUser());
        $saleReturnInvoiceItem->setBranch($this->getUser()->getBranch());
        $saleReturnInvoiceItem->setInstitution($this->getUser()->getInstitution());
        $saleReturnInvoiceItem->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($saleReturnInvoiceItem);


        // discount
        $discountAmount =  0;

        // sale invoice item discount
        if($saleReturnInvoiceData['discount'] > 0)
        {
            // discount
            $discountAmount =  ($amount * $saleReturnInvoiceData['discount']) / 100;

            // persist discount
            $saleReturnInvoiceItemDiscount = new SaleReturnInvoiceItemDiscount();
            $saleReturnInvoiceItemDiscount->setSaleReturnInvoice($saleReturnInvoice);
            $saleReturnInvoiceItemDiscount->setSaleReturnInvoiceItem($saleReturnInvoiceItem);
            $saleReturnInvoiceItemDiscount->setRate($saleReturnInvoiceData['discount']);
            $saleReturnInvoiceItemDiscount->setAmount($discountAmount);

            $saleReturnInvoiceItemDiscount->setUser($this->getUser());
            $saleReturnInvoiceItemDiscount->setBranch($this->getUser()->getBranch());
            $saleReturnInvoiceItemDiscount->setInstitution($this->getUser()->getInstitution());
            $saleReturnInvoiceItemDiscount->setYear($this->getUser()->getCurrentYear());
            $this->manager->persist($saleReturnInvoiceItemDiscount);
        }

        $saleReturnInvoiceItem->setDiscountAmount($discountAmount);

        $totalTaxAmount = 0;

        // set sale return invoice item tax
        if (isset($saleReturnInvoiceData['taxes']))
        {
            foreach ($saleReturnInvoiceData['taxes'] as $tax){
                // get tax object
                $taxObject = $this->taxRepository->find($this->getIdFromApiResourceId($tax));

                // set tax on sale return invoice item
                $saleReturnInvoiceItem->addTax($taxObject);

                // tax amount
                $taxAmount = ($amount * $taxObject->getRate()) / 100;

                // persist sale return invoice tax
                $saleReturnInvoiceItemTax = new SaleReturnInvoiceItemTax();
                $saleReturnInvoiceItemTax->setSaleReturnInvoice($saleReturnInvoice);
                $saleReturnInvoiceItemTax->setSaleReturnInvoiceItem($saleReturnInvoiceItem);
                $saleReturnInvoiceItemTax->setTax($taxObject);
                $saleReturnInvoiceItemTax->setRate($taxObject->getRate());
                $saleReturnInvoiceItemTax->setAmount($taxAmount);

                $saleReturnInvoiceItemTax->setUser($this->getUser());
                $saleReturnInvoiceItemTax->setBranch($this->getUser()->getBranch());
                $saleReturnInvoiceItemTax->setInstitution($this->getUser()->getInstitution());
                $saleReturnInvoiceItemTax->setYear($this->getUser()->getCurrentYear());
                $this->manager->persist($saleReturnInvoiceItemTax);

                // total tax amount
                $totalTaxAmount += $taxAmount;
            }

        }

        $saleReturnInvoiceItem->setAmountWithTaxes($totalTaxAmount);

        $saleReturnInvoiceItem->setAmountTtc($amount + $totalTaxAmount - $discountAmount);

        // CHECK IF THAT STOCK IS ALREADY IN CURRENT SALE INVOICE

        $saleReturnInvoiceItemStock = new SaleReturnInvoiceItemStock();

        $saleReturnInvoiceItemStock->setSaleReturnInvoiceItem($saleReturnInvoiceItem);
        $saleReturnInvoiceItemStock->setStock($stock);
        $saleReturnInvoiceItemStock->setQuantity($saleReturnInvoiceData['quantity']);
        $saleReturnInvoiceItemStock->setCreatedAt(new \DateTimeImmutable());
        $saleReturnInvoiceItemStock->setUser($this->getUser());
        $saleReturnInvoiceItemStock->setYear($this->getUser()->getCurrentYear());
        $saleReturnInvoiceItemStock->setBranch($this->getUser()->getBranch());
        $saleReturnInvoiceItemStock->setInstitution($this->getUser()->getInstitution());

        $this->manager->persist($saleReturnInvoiceItemStock);

        $this->manager->flush();


        // update sale return invoice
        $amount = $this->saleReturnInvoiceItemRepository->saleReturnInvoiceHtAmount($saleReturnInvoice)[0][1];
        $saleReturnInvoice->setAmount($amount);

        // get sale return invoice item discounts from sale invoice
        $saleReturnInvoiceItemDiscounts = $this->saleReturnInvoiceItemDiscountRepository->findBy(['saleReturnInvoice' => $saleReturnInvoice]);
        $totalDiscountAmount = 0;
        if($saleReturnInvoiceItemDiscounts)
        {
            foreach ($saleReturnInvoiceItemDiscounts as $saleReturnInvoiceItemDiscount)
            {
                $totalDiscountAmount += $saleReturnInvoiceItemDiscount->getAmount();
            }
        }

        // get sale return invoice item taxes from sale invoice
        $saleReturnInvoiceItemTaxes = $this->saleReturnInvoiceItemTaxRepository->findBy(['saleReturnInvoice' => $saleReturnInvoice]);
        $totalTaxAmount = 0;
        if($saleReturnInvoiceItemTaxes)
        {
            foreach ($saleReturnInvoiceItemTaxes as $saleReturnInvoiceItemTax)
            {
                $totalTaxAmount += $saleReturnInvoiceItemTax->getAmount();
            }
        }

        $amountTtc = $this->saleReturnInvoiceItemRepository->saleReturnInvoiceHtAmount($saleReturnInvoice)[0][1] + $totalTaxAmount - $totalDiscountAmount;
        $saleReturnInvoice->setTtc($amountTtc);
        $saleReturnInvoice->setBalance($amountTtc);
        $saleReturnInvoice->setVirtualBalance($amountTtc);

        $this->manager->flush();

        return $this->processor->process($saleReturnInvoiceItem, $operation, $uriVariables, $context);
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
