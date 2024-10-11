<?php

namespace App\Controller\Billing\Sale\School\Return;

use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class ValidateSchoolSaleReturnInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleReturnInvoiceFeeRepository $saleReturnInvoiceFeeRepository,
                             SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $saleReturnInvoice = $saleReturnInvoiceRepository->find($id);

        if(!$saleReturnInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This return invoice is not found.'], 404);
        }

        if(!$saleReturnInvoice instanceof SaleReturnInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This data must be type of return invoice.'], 404);
        }

        $saleReturnInvoiceFee = $saleReturnInvoiceFeeRepository->findOneBy(['saleReturnInvoice' => $saleReturnInvoice]);

        if(!$saleReturnInvoiceFee)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'Cannot proceed : the cart is empty.'], 404);
        }

        // Start : Save invoice modification
        $saleReturnInvoice->setInvoiceAt(new \DateTimeImmutable());
        if (isset($invoiceData['paymentReference']) && $invoiceData['paymentReference']){
            $saleReturnInvoice->setPaymentReference($invoiceData['paymentReference']);
        }

        $amount = $saleReturnInvoiceFeeRepository->saleReturnInvoiceHtAmount($saleReturnInvoice)[0][1];

        $saleReturnInvoice->setAmount($amount);

        $saleReturnInvoice->setUser($this->getUser());
        $saleReturnInvoice->setInstitution($this->getUser()->getInstitution());
        $saleReturnInvoice->setYear($this->getUser()->getCurrentYear());

        $taxResult = 0;
        $discountAmount = 0;
        $saleReturnInvoiceItems = $saleReturnInvoiceFeeRepository->findBy(['saleReturnInvoice' => $saleReturnInvoice]);

        foreach ($saleReturnInvoiceItems as $saleReturnInvoiceItem){
            if ($saleReturnInvoiceItem->getTaxes()){
                foreach ($saleReturnInvoiceItem->getTaxes() as $tax){
                    $taxResult = $taxResult + $saleReturnInvoiceItem->getAmount() * $tax->getRate() / 100;
                }
            }
            $discountAmount = $discountAmount + $saleReturnInvoiceItem->getAmount() * $saleReturnInvoiceItem->getDiscount() / 100;
        }

        $amountTtc = $saleReturnInvoiceFeeRepository->saleReturnInvoiceHtAmount($saleReturnInvoice)[0][1] + $taxResult - $discountAmount;

        if ($saleReturnInvoice->getSaleInvoice()){
            /*if ($amountTtc > $saleReturnInvoice->getSaleInvoice()->getAmountPaid()){
                $amountTtc = $saleReturnInvoice->getSaleInvoice()->getAmountPaid();
				$saleReturnInvoice->setAmount($amountTtc);
            }*/
			$saleReturnInvoice->setTtc($amountTtc);
        }else{
			$saleReturnInvoice->setTtc($amountTtc);
			$saleReturnInvoice->setBalance($amountTtc);
			$saleReturnInvoice->setVirtualBalance($amountTtc);
		}


        
        
        // End : Save invoice modification



        $saleReturnInvoice->setStatus('return invoice');

        $entityManager->flush();


        return $this->json(['hydra:member' => '200']);
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
