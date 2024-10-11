<?php

namespace App\Controller\Billing\Purchase\Invoice;

use App\Entity\Billing\Purchase\PurchaseInvoice;
use App\Entity\Billing\Purchase\PurchaseReturnInvoice;
use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItem;
use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItemDiscount;
use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItemStock;
use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItemTax;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemDiscountRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemStockRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemTaxRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GeneratePurchaseReturnInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(PurchaseReturnInvoiceItemRepository $purchaseReturnInvoiceItemRepository,
                             PurchaseReturnInvoiceRepository $purchaseReturnInvoiceRepository,
                             PurchaseInvoiceRepository $purchaseInvoiceRepository,
                             PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                             PurchaseInvoiceItemDiscountRepository $purchaseInvoiceItemDiscountRepository,
                             PurchaseInvoiceItemTaxRepository $purchaseInvoiceItemTaxRepository,
                             PurchaseInvoiceItemStockRepository $purchaseInvoiceItemStockRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {
        $id = $request->get('id');

        $purchaseInvoice = $purchaseInvoiceRepository->find($id);

        if(!$purchaseInvoice instanceof PurchaseInvoice)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of purchase invoice.'], 404);
        }

        $existGeneratedInvoice = $purchaseReturnInvoiceRepository->findOneBy(['purchaseInvoice' => $purchaseInvoice]);
        if($existGeneratedInvoice)
        {
            return new JsonResponse(['hydra:description' => 'Purchase Return Invoice already generated.'], 500);
        }

        $purchaseReturnInvoice = new PurchaseReturnInvoice();

        $returnInvoice = $purchaseReturnInvoiceRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$returnInvoice){
            $uniqueNumber = 'PUR/RET/INV/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $returnInvoice->getInvoiceNumber());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'PUR/RET/INV/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $purchaseReturnInvoice->setPurchaseInvoice($purchaseInvoice);

        $purchaseReturnInvoice->setInvoiceNumber($uniqueNumber);
        $purchaseReturnInvoice->setSupplier($purchaseInvoice->getSupplier());
        $purchaseReturnInvoice->setAmount($purchaseInvoice->getAmount());
        $purchaseReturnInvoice->setAmountPaid(0);
        $purchaseReturnInvoice->setShippingAddress($purchaseInvoice->getShippingAddress());
        $purchaseReturnInvoice->setTtc($purchaseInvoice->getTtc());
        $purchaseReturnInvoice->setBalance($purchaseInvoice->getAmountPaid());
        $purchaseReturnInvoice->setVirtualBalance($purchaseInvoice->getAmountPaid());
        $purchaseReturnInvoice->setStatus('draft');
        $purchaseReturnInvoice->setOtherStatus('draft');
        $purchaseReturnInvoice->setInvoiceAt(new \DateTimeImmutable());
        $purchaseReturnInvoice->setDeadLine($purchaseInvoice->getDeadLine());

        $purchaseReturnInvoice->setUser($this->getUser());
        $purchaseReturnInvoice->setInstitution($this->getUser()->getInstitution());
        $purchaseReturnInvoice->setYear($this->getUser()->getCurrentYear());
        $purchaseReturnInvoice->setBranch($this->getUser()->getBranch());

        $entityManager->persist($purchaseReturnInvoice);


        $purchaseInvoiceItems = $purchaseInvoiceItemRepository->findBy(['purchaseInvoice' => $purchaseInvoice]);
        if ($purchaseInvoiceItems)
        {
            foreach ($purchaseInvoiceItems as $purchaseInvoiceItem)
            {
                $purchaseReturnInvoiceItem = new PurchaseReturnInvoiceItem();

                $purchaseReturnInvoiceItem->setItem($purchaseInvoiceItem->getItem());
                $purchaseReturnInvoiceItem->setQuantity($purchaseInvoiceItem->getQuantity());
                $purchaseReturnInvoiceItem->setReturnQuantity($purchaseInvoiceItem->getQuantity());
                $purchaseReturnInvoiceItem->setPu($purchaseInvoiceItem->getPu());
                $purchaseReturnInvoiceItem->setDiscount($purchaseInvoiceItem->getDiscount());
                $purchaseReturnInvoiceItem->setPurchaseReturnInvoice($purchaseReturnInvoice);
                $purchaseReturnInvoiceItem->setPurchaseInvoiceItem($purchaseInvoiceItem);
                $purchaseReturnInvoiceItem->setName($purchaseInvoiceItem->getName());
                $purchaseReturnInvoiceItem->setAmount($purchaseInvoiceItem->getAmount());
                $purchaseReturnInvoiceItem->setDiscountAmount($purchaseInvoiceItem->getDiscountAmount());
                $purchaseReturnInvoiceItem->setAmountTtc($purchaseInvoiceItem->getAmountTtc());

                if ($purchaseInvoiceItem->getTaxes()){
                    foreach ($purchaseInvoiceItem->getTaxes() as $tax){
                        $purchaseReturnInvoiceItem->addTax($tax);
                    }
                }

                $purchaseReturnInvoiceItem->setUser($this->getUser());
                $purchaseReturnInvoiceItem->setInstitution($this->getUser()->getInstitution());
                $purchaseReturnInvoiceItem->setYear($this->getUser()->getCurrentYear());
                $purchaseReturnInvoiceItem->setBranch($this->getUser()->getBranch());

                $entityManager->persist($purchaseReturnInvoiceItem);

                // find purchase invoice item discount
                $purchaseInvoiceItemDiscounts = $purchaseInvoiceItemDiscountRepository->findBy(['purchaseInvoiceItem' => $purchaseInvoiceItem]);
                if($purchaseInvoiceItemDiscounts)
                {
                    foreach ($purchaseInvoiceItemDiscounts as $purchaseInvoiceItemDiscount)
                    {
                        $purchaseReturnInvoiceItemDiscount = new PurchaseReturnInvoiceItemDiscount();

                        $purchaseReturnInvoiceItemDiscount->setPurchaseReturnInvoice($purchaseReturnInvoice);
                        $purchaseReturnInvoiceItemDiscount->setPurchaseReturnInvoiceItem($purchaseReturnInvoiceItem);
                        $purchaseReturnInvoiceItemDiscount->setRate($purchaseInvoiceItemDiscount->getRate());
                        $purchaseReturnInvoiceItemDiscount->setAmount($purchaseInvoiceItemDiscount->getAmount());

                        $purchaseReturnInvoiceItemDiscount->setUser($this->getUser());
                        $purchaseReturnInvoiceItemDiscount->setInstitution($this->getUser()->getInstitution());
                        $purchaseReturnInvoiceItemDiscount->setYear($this->getUser()->getCurrentYear());
                        $purchaseReturnInvoiceItemDiscount->setBranch($this->getUser()->getBranch());

                        $entityManager->persist($purchaseReturnInvoiceItemDiscount);
                    }
                }

                // find purchase invoice item tax
                $purchaseInvoiceItemTaxes = $purchaseInvoiceItemTaxRepository->findBy(['purchaseInvoiceItem' => $purchaseInvoiceItem]);
                if($purchaseInvoiceItemTaxes)
                {
                    foreach ($purchaseInvoiceItemTaxes as $purchaseInvoiceItemTax)
                    {
                        $purchaseReturnInvoiceItemTax = new PurchaseReturnInvoiceItemTax();

                        $purchaseReturnInvoiceItemTax->setPurchaseReturnInvoice($purchaseReturnInvoice);
                        $purchaseReturnInvoiceItemTax->setPurchaseReturnInvoiceItem($purchaseReturnInvoiceItem);
                        $purchaseReturnInvoiceItemTax->setTax($purchaseInvoiceItemTax->getTax());
                        $purchaseReturnInvoiceItemTax->setRate($purchaseInvoiceItemTax->getRate());
                        $purchaseReturnInvoiceItemTax->setAmount($purchaseInvoiceItemTax->getAmount());

                        $purchaseReturnInvoiceItemTax->setUser($this->getUser());
                        $purchaseReturnInvoiceItemTax->setInstitution($this->getUser()->getInstitution());
                        $purchaseReturnInvoiceItemTax->setYear($this->getUser()->getCurrentYear());
                        $purchaseReturnInvoiceItemTax->setBranch($this->getUser()->getBranch());

                        $entityManager->persist($purchaseReturnInvoiceItemTax);
                    }
                }

                // find purchase invoice item stock
                $purchaseInvoiceItemStocks = $purchaseInvoiceItemStockRepository->findBy(['purchaseInvoiceItem' => $purchaseInvoiceItem]);
                if($purchaseInvoiceItemStocks)
                {
                    foreach ($purchaseInvoiceItemStocks as $purchaseInvoiceItemStock)
                    {
                        $purchaseReturnInvoiceItemStock = new PurchaseReturnInvoiceItemStock();

                        $purchaseReturnInvoiceItemStock->setPurchaseReturnInvoiceItem($purchaseReturnInvoiceItem);
                        $purchaseReturnInvoiceItemStock->setStock($purchaseInvoiceItemStock->getStock());
                        $purchaseReturnInvoiceItemStock->setQuantity($purchaseInvoiceItemStock->getQuantity());

                        $purchaseReturnInvoiceItemStock->setUser($this->getUser());
                        $purchaseReturnInvoiceItemStock->setInstitution($this->getUser()->getInstitution());
                        $purchaseReturnInvoiceItemStock->setYear($this->getUser()->getCurrentYear());
                        $purchaseReturnInvoiceItemStock->setBranch($this->getUser()->getBranch());

                        $entityManager->persist($purchaseReturnInvoiceItemStock);
                    }
                }
            }
        }

        $entityManager->flush();

        return $this->json(['hydra:member' => $purchaseReturnInvoice]);
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
