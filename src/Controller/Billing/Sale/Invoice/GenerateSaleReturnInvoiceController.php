<?php

namespace App\Controller\Billing\Sale\Invoice;

use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Billing\Sale\SaleReturnInvoiceItem;
use App\Entity\Billing\Sale\SaleReturnInvoiceItemDiscount;
use App\Entity\Billing\Sale\SaleReturnInvoiceItemStock;
use App\Entity\Billing\Sale\SaleReturnInvoiceItemTax;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemDiscountRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemTaxRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GenerateSaleReturnInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(SaleReturnInvoiceItemRepository $saleReturnInvoiceItemRepository,
                             SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceItemDiscountRepository $saleInvoiceItemDiscountRepository,
                             SaleInvoiceItemTaxRepository $saleInvoiceItemTaxRepository,
                             SaleInvoiceItemStockRepository $saleInvoiceItemStockRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {
        $id = $request->get('id');

        $saleInvoice = $saleInvoiceRepository->find($id);

        if(!$saleInvoice instanceof SaleInvoice)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of sale invoice.'], 404);
        }

        $existGeneratedInvoice = $saleReturnInvoiceRepository->findOneBy(['saleInvoice' => $saleInvoice]);
        if($existGeneratedInvoice)
        {
            return new JsonResponse(['hydra:description' => 'Sale Return Invoice already generated.'], 500);
        }

        $saleReturnInvoice = new SaleReturnInvoice();

        $returnInvoice = $saleReturnInvoiceRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$returnInvoice){
            $uniqueNumber = 'SAL/RET/INV/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $returnInvoice->getInvoiceNumber());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'SAL/RET/INV/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $saleReturnInvoice->setSaleInvoice($saleInvoice);

        $saleReturnInvoice->setInvoiceNumber($uniqueNumber);
        $saleReturnInvoice->setCustomer($saleInvoice->getCustomer());
        $saleReturnInvoice->setAmount($saleInvoice->getAmount());
        $saleReturnInvoice->setAmountPaid(0);
        $saleReturnInvoice->setShippingAddress($saleInvoice->getShippingAddress());
        $saleReturnInvoice->setTtc($saleInvoice->getTtc());
        $saleReturnInvoice->setBalance($saleInvoice->getAmountPaid());
        $saleReturnInvoice->setVirtualBalance($saleInvoice->getAmountPaid());
        $saleReturnInvoice->setStatus('draft');
        $saleReturnInvoice->setOtherStatus('draft');
        $saleReturnInvoice->setIsStandard($saleInvoice->isIsStandard());
        $saleReturnInvoice->setInvoiceAt(new \DateTimeImmutable());
        $saleReturnInvoice->setDeadLine($saleInvoice->getDeadLine());

        $saleReturnInvoice->setUser($this->getUser());
        $saleReturnInvoice->setInstitution($this->getUser()->getInstitution());
        $saleReturnInvoice->setYear($this->getUser()->getCurrentYear());
        $saleReturnInvoice->setBranch($this->getUser()->getBranch());

        $entityManager->persist($saleReturnInvoice);


        $saleInvoiceItems = $saleInvoiceItemRepository->findBy(['saleInvoice' => $saleInvoice]);
        if ($saleInvoiceItems)
        {
            foreach ($saleInvoiceItems as $saleInvoiceItem)
            {
                $saleReturnInvoiceItem = new SaleReturnInvoiceItem();

                $saleReturnInvoiceItem->setItem($saleInvoiceItem->getItem());
                $saleReturnInvoiceItem->setQuantity($saleInvoiceItem->getQuantity());
                $saleReturnInvoiceItem->setReturnQuantity($saleInvoiceItem->getQuantity());
                $saleReturnInvoiceItem->setPu($saleInvoiceItem->getPu());
                $saleReturnInvoiceItem->setDiscount($saleInvoiceItem->getDiscount());
                $saleReturnInvoiceItem->setSaleReturnInvoice($saleReturnInvoice);
                $saleReturnInvoiceItem->setSaleInvoiceItem($saleInvoiceItem);
                $saleReturnInvoiceItem->setName($saleInvoiceItem->getName());
                $saleReturnInvoiceItem->setAmount($saleInvoiceItem->getAmount());
                $saleReturnInvoiceItem->setDiscountAmount($saleInvoiceItem->getDiscountAmount());
                $saleReturnInvoiceItem->setAmountTtc($saleInvoiceItem->getAmountTtc());
                $saleReturnInvoiceItem->setAmountWithTaxes($saleInvoiceItem->getAmountWithTaxes());

                if ($saleInvoiceItem->getTaxes()){
                    foreach ($saleInvoiceItem->getTaxes() as $tax){
                        $saleReturnInvoiceItem->addTax($tax);
                    }
                }

                $saleReturnInvoiceItem->setUser($this->getUser());
                $saleReturnInvoiceItem->setInstitution($this->getUser()->getInstitution());
                $saleReturnInvoiceItem->setYear($this->getUser()->getCurrentYear());
                $saleReturnInvoiceItem->setBranch($this->getUser()->getBranch());

                $entityManager->persist($saleReturnInvoiceItem);

                // find sale invoice item discount
                $saleInvoiceItemDiscounts = $saleInvoiceItemDiscountRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);
                if($saleInvoiceItemDiscounts)
                {
                    foreach ($saleInvoiceItemDiscounts as $saleInvoiceItemDiscount)
                    {
                        $saleReturnInvoiceItemDiscount = new SaleReturnInvoiceItemDiscount();

                        $saleReturnInvoiceItemDiscount->setSaleReturnInvoice($saleReturnInvoice);
                        $saleReturnInvoiceItemDiscount->setSaleReturnInvoiceItem($saleReturnInvoiceItem);
                        $saleReturnInvoiceItemDiscount->setRate($saleInvoiceItemDiscount->getRate());
                        $saleReturnInvoiceItemDiscount->setAmount($saleInvoiceItemDiscount->getAmount());

                        $saleReturnInvoiceItemDiscount->setUser($this->getUser());
                        $saleReturnInvoiceItemDiscount->setInstitution($this->getUser()->getInstitution());
                        $saleReturnInvoiceItemDiscount->setYear($this->getUser()->getCurrentYear());
                        $saleReturnInvoiceItemDiscount->setBranch($this->getUser()->getBranch());

                        $entityManager->persist($saleReturnInvoiceItemDiscount);
                    }
                }

                // find sale invoice item tax
                $saleInvoiceItemTaxes = $saleInvoiceItemTaxRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);
                if($saleInvoiceItemTaxes)
                {
                    foreach ($saleInvoiceItemTaxes as $saleInvoiceItemTax)
                    {
                        $saleReturnInvoiceItemTax = new SaleReturnInvoiceItemTax();

                        $saleReturnInvoiceItemTax->setSaleReturnInvoice($saleReturnInvoice);
                        $saleReturnInvoiceItemTax->setSaleReturnInvoiceItem($saleReturnInvoiceItem);
                        $saleReturnInvoiceItemTax->setTax($saleInvoiceItemTax->getTax());
                        $saleReturnInvoiceItemTax->setRate($saleInvoiceItemTax->getRate());
                        $saleReturnInvoiceItemTax->setAmount($saleInvoiceItemTax->getAmount());

                        $saleReturnInvoiceItemTax->setUser($this->getUser());
                        $saleReturnInvoiceItemTax->setInstitution($this->getUser()->getInstitution());
                        $saleReturnInvoiceItemTax->setYear($this->getUser()->getCurrentYear());
                        $saleReturnInvoiceItemTax->setBranch($this->getUser()->getBranch());

                        $entityManager->persist($saleReturnInvoiceItemTax);
                    }
                }

                // find sale invoice item stock
                $saleInvoiceItemStocks = $saleInvoiceItemStockRepository->findBy(['saleInvoiceItem' => $saleInvoiceItem]);
                if($saleInvoiceItemStocks)
                {
                    foreach ($saleInvoiceItemStocks as $saleInvoiceItemStock)
                    {
                        $saleReturnInvoiceItemStock = new SaleReturnInvoiceItemStock();

                        $saleReturnInvoiceItemStock->setSaleReturnInvoiceItem($saleReturnInvoiceItem);
                        $saleReturnInvoiceItemStock->setStock($saleInvoiceItemStock->getStock());
                        $saleReturnInvoiceItemStock->setQuantity($saleInvoiceItemStock->getQuantity());

                        $saleReturnInvoiceItemStock->setUser($this->getUser());
                        $saleReturnInvoiceItemStock->setInstitution($this->getUser()->getInstitution());
                        $saleReturnInvoiceItemStock->setYear($this->getUser()->getCurrentYear());
                        $saleReturnInvoiceItemStock->setBranch($this->getUser()->getBranch());

                        $entityManager->persist($saleReturnInvoiceItemStock);
                    }
                }
            }
        }

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
