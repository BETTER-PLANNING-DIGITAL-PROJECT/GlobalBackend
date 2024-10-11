<?php
namespace App\State\Processor\Billing\Sale;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Billing\Sale\SaleInvoiceItem;
use App\Entity\Billing\Sale\SaleInvoiceItemDiscount;
use App\Entity\Billing\Sale\SaleInvoiceItemStock;
use App\Entity\Billing\Sale\SaleInvoiceItemTax;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemDiscountRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemTaxRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Inventory\StockRepository;
use App\Repository\Setting\Finance\TaxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class CreateSaleInvoiceItemProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly TaxRepository $taxRepository,
                                private readonly StockRepository $stockRepository,
                                private readonly SaleInvoiceRepository $saleInvoiceRepository,
                                private readonly SaleInvoiceItemRepository $saleInvoiceItemRepository,
                                private readonly SaleInvoiceItemDiscountRepository $saleInvoiceItemDiscountRepository,
                                private readonly SaleInvoiceItemTaxRepository $saleInvoiceItemTaxRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $saleInvoiceData = json_decode($this->request->getContent(), true);

        $saleInvoice = $this->saleInvoiceRepository->find($data->getId());
        if(!$data instanceof SaleInvoice)
        {
            return new JsonResponse(['hydra:description' => 'Sale Invoice not found.'], 404);
        }

        if (!is_numeric($saleInvoiceData['quantity'])){
            return new JsonResponse(['hydra:description' => 'Quantity must be numeric value.'], 500);
        }
        elseif ($saleInvoiceData['quantity'] <= 0){
            return new JsonResponse(['hydra:description' => 'Quantity must be upper than 0.'], 500);
        }

        if (!is_numeric($saleInvoiceData['pu'])){
            return new JsonResponse(['hydra:description' => 'Price must be numeric value.'], 500);
        }
        elseif ($saleInvoiceData['pu'] <= 0){
            return new JsonResponse(['hydra:description' => 'Price must be upper than 0.'], 500);
        }

        if ($saleInvoiceData['discount'] < 0){
            return new JsonResponse(['hydra:description' => 'Discount must be positive value.'], 500);
        }

        $stock = $this->stockRepository->find($this->getIdFromApiResourceId($saleInvoiceData['item']));
        if (!$stock){
            return new JsonResponse(['hydra:description' => 'Stock not found!'], 500);
        }

        if ($saleInvoiceData['quantity'] > $stock->getAvailableQte()){
            return new JsonResponse(['hydra:description' => 'Request quantity must be less than available quantity.'], 500);
        }

        $amount = $saleInvoiceData['quantity'] * $saleInvoiceData['pu'];

        $saleInvoiceItem = new SaleInvoiceItem();

        $saleInvoiceItem->setItem($stock->getItem());
        $saleInvoiceItem->setQuantity($saleInvoiceData['quantity']);
        $saleInvoiceItem->setPu($saleInvoiceData['pu']);
        $saleInvoiceItem->setDiscount($saleInvoiceData['discount']);
        $saleInvoiceItem->setSaleInvoice($saleInvoice);
        $saleInvoiceItem->setName($saleInvoiceData['name']);
        $saleInvoiceItem->setAmount($amount);

        $saleInvoiceItem->setUser($this->getUser());
        $saleInvoiceItem->setBranch($this->getUser()->getBranch());
        $saleInvoiceItem->setInstitution($this->getUser()->getInstitution());
        $saleInvoiceItem->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($saleInvoiceItem);

        // discount
        $discountAmount =  0;

        // sale invoice item discount
        if($saleInvoiceData['discount'] > 0)
        {
            // discount
            $discountAmount =  ($amount * $saleInvoiceData['discount']) / 100;

            // persist discount
            $saleInvoiceItemDiscount = new SaleInvoiceItemDiscount();
            $saleInvoiceItemDiscount->setSaleInvoice($saleInvoice);
            $saleInvoiceItemDiscount->setSaleInvoiceItem($saleInvoiceItem);
            $saleInvoiceItemDiscount->setRate($saleInvoiceData['discount']);
            $saleInvoiceItemDiscount->setAmount($discountAmount);

            $saleInvoiceItemDiscount->setUser($this->getUser());
            $saleInvoiceItemDiscount->setBranch($this->getUser()->getBranch());
            $saleInvoiceItemDiscount->setInstitution($this->getUser()->getInstitution());
            $saleInvoiceItemDiscount->setYear($this->getUser()->getCurrentYear());
            $this->manager->persist($saleInvoiceItemDiscount);
        }

        $saleInvoiceItem->setDiscountAmount($discountAmount);

        $totalTaxAmount = 0;

        // set sale invoice item tax
        if (isset($saleInvoiceData['taxes']))
        {
            foreach ($saleInvoiceData['taxes'] as $tax){
                // get tax object
                $taxObject = $this->taxRepository->find($this->getIdFromApiResourceId($tax));

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
                $this->manager->persist($saleInvoiceItemTax);

                // total tax amount
                $totalTaxAmount += $taxAmount;
            }

        }

        $saleInvoiceItem->setAmountWithTaxes($totalTaxAmount);

        $saleInvoiceItem->setAmountTtc($amount + $totalTaxAmount - $discountAmount);

        // sale invoice item stock
        $saleInvoiceItemStock = new SaleInvoiceItemStock();

        $saleInvoiceItemStock->setSaleInvoiceItem($saleInvoiceItem);
        $saleInvoiceItemStock->setStock($stock);
        $saleInvoiceItemStock->setQuantity($saleInvoiceData['quantity']);
        $saleInvoiceItemStock->setCreatedAt(new \DateTimeImmutable());
        $saleInvoiceItemStock->setUser($this->getUser());
        $saleInvoiceItemStock->setYear($this->getUser()->getCurrentYear());
        $saleInvoiceItemStock->setBranch($this->getUser()->getBranch());
        $saleInvoiceItemStock->setInstitution($this->getUser()->getInstitution());

        $this->manager->persist($saleInvoiceItemStock);

        $this->manager->flush();

        // update sale invoice
        $amount = $this->saleInvoiceItemRepository->saleInvoiceHtAmount($saleInvoice)[0][1];
        $saleInvoice->setAmount($amount);

        // get sale invoice item discounts from sale invoice
        $saleInvoiceItemDiscounts = $this->saleInvoiceItemDiscountRepository->findBy(['saleInvoice' => $saleInvoice]);
        $totalDiscountAmount = 0;
        if($saleInvoiceItemDiscounts)
        {
            foreach ($saleInvoiceItemDiscounts as $saleInvoiceItemDiscount)
            {
                $totalDiscountAmount += $saleInvoiceItemDiscount->getAmount();
            }
        }

        // get sale invoice item taxes from sale invoice
        $saleInvoiceItemTaxes = $this->saleInvoiceItemTaxRepository->findBy(['saleInvoice' => $saleInvoice]);
        $totalTaxAmount = 0;
        if($saleInvoiceItemTaxes)
        {
            foreach ($saleInvoiceItemTaxes as $saleInvoiceItemTax)
            {
                $totalTaxAmount += $saleInvoiceItemTax->getAmount();
            }
        }

        $amountTtc = $this->saleInvoiceItemRepository->saleInvoiceHtAmount($saleInvoice)[0][1] + $totalTaxAmount - $totalDiscountAmount;
        $saleInvoice->setTtc($amountTtc);
        $saleInvoice->setBalance($amountTtc);
        $saleInvoice->setVirtualBalance($amountTtc);

        $this->manager->flush();

        return $this->processor->process($saleInvoiceItem, $operation, $uriVariables, $context);
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
