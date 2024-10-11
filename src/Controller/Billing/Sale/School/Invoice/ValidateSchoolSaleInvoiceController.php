<?php

namespace App\Controller\Billing\Sale\School\Invoice;

use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Inventory\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class ValidateSchoolSaleInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleInvoiceFeeRepository $saleInvoiceFeeRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             StockRepository $stockRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $saleInvoice = $saleInvoiceRepository->find($id);
        if(!$saleInvoice instanceof SaleInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This data must be type of invoice.'], 404);
        }

        if(!$saleInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'Invoice not found.'], 404);
        }

        $saleInvoiceFee = $saleInvoiceFeeRepository->findOneBy(['saleInvoice' => $saleInvoice]);
        if(!$saleInvoiceFee)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'Cannot proceed : the cart is empty.'], 404);
        }

        $saleInvoice->setStatus('invoice');

        // Update sale invoice amount
        $amount = $saleInvoiceFeeRepository->saleInvoiceHtAmount($saleInvoice)[0][1];
        $saleInvoice->setAmount($amount);

        $taxResult = 0;
        $discountAmount = 0;

        $saleInvoiceFees = $saleInvoiceFeeRepository->findBy(['saleInvoice' => $saleInvoice]);
        foreach ($saleInvoiceFees as $saleInvoiceFee){
            if ($saleInvoiceFee->getTaxes()){
                foreach ($saleInvoiceFee->getTaxes() as $tax){
                    $taxResult = $taxResult + $saleInvoiceFee->getAmount() * $tax->getRate() / 100;
                }
            }

            $discountAmount = $discountAmount + $saleInvoiceFee->getAmount() * $saleInvoiceFee->getDiscount() / 100;
        }

        $amountTtc = $saleInvoiceFeeRepository->saleInvoiceHtAmount($saleInvoice)[0][1] + $taxResult - $discountAmount;

        $saleInvoice->setTtc($amountTtc);
        $saleInvoice->setBalance($amountTtc);
        $saleInvoice->setVirtualBalance($amountTtc);

        // Update sale invoice amount end

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
