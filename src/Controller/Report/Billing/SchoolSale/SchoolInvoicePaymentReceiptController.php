<?php

namespace App\Controller\Report\Billing\SchoolSale;

use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class SchoolInvoicePaymentReceiptController extends AbstractController
{
    public function __construct(Request $req, EntityManagerInterface $entityManager,
                                private readonly TokenStorageInterface $tokenStorage)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
    }
    public function __invoke(SaleSettlementRepository $saleSettlementRepository, SaleInvoiceFeeRepository $saleInvoiceFeeRepository, Request $request, int $id): JsonResponse
    {
        $saleSettlement = $saleSettlementRepository->find($id);

        if (!$saleSettlement) {
            return $this->json([
                'error' => 'Sale settlement not found'
            ], 404);
        }

        $invoice = $saleSettlement->getStudentRegistration();

        // Fetch invoice items for the current invoice
        $invoiceItems = $saleInvoiceFeeRepository->findByStudentRegistration($invoice);

        $table = [
            '@id' => "/api/saleSettlement/" . $saleSettlement->getId(),
            '@type' => "SaleSettlement",
            'id' => $saleSettlement->getId(),
            'reference' => $saleSettlement->getReference(),
            'amountPay' => $saleSettlement->getAmountPay(),
            'amountRest' => $saleSettlement->getAmountRest(),
            'settleAt' => $saleSettlement->getSettleAt()->format('Y-m-d'),
            'year' => $saleSettlement->getYear()->getYear(),
            'firstName' => $saleSettlement->getStudentRegistration()->getStudent()->getFirstName(),
            'name' => $saleSettlement->getStudentRegistration()->getStudent()->getName(),
            'class' => $saleSettlement->getClass()->getCode(),
            'matricule' => $saleSettlement->getStudentRegistration()->getStudent()->getMatricule(),
            'invoice' => [
                '@id' => "/api/invoice/" . $invoice->getId(),
                '@type' => "Invoice",
                'id' => $invoice->getId(),
                'invoiceItems' => array_map(function ($invoiceItem) {
                    return [
                        // data from invoiceItem here
                        'id' => $invoiceItem->getId(),
                        'name' => $invoiceItem->getFee()->getName(),
                        'quantity' => $invoiceItem->getQuantity(),
                        'pu' => $invoiceItem->getPu(),
                        'amount' => $invoiceItem->getAmount(),
                        'amountTtc' => $invoiceItem->getAmountTtc(),
                        'balance' => $invoiceItem->getBalance(),
                        'amountPaid' => $invoiceItem->getAmountPaid(),
                        'discountAmount' => $invoiceItem->getDiscountAmount(),
                        'deadLine' => $invoiceItem->getSaleInvoice()->getDeadLine() ? $invoiceItem->getSaleInvoice()->getDeadLine()->format('Y-m-d') : '',
                        'invoiceNumber' => $invoiceItem->getSaleInvoice()->getInvoiceNumber(),
                        'paymentDeadLine' => $invoiceItem->getFee()->getPaymentDate() ? $invoiceItem->getFee()->getPaymentDate()->format('Y-m-d') : '',
                        'invoice' => [
                            '@id' => "/api/invoice/" . $invoiceItem->getSaleInvoice()->getId(),
                            '@type' => "Invoice",
                            'id' => $invoiceItem->getSaleInvoice()->getId(),
                            'class' => $invoiceItem->getSaleInvoice()->getClass()->getCode(),
                            'school' => $invoiceItem->getSaleInvoice()->getSchool()->getName(),
                            'invoiceNumber' => $invoiceItem->getSaleInvoice()->getInvoiceNumber(),
                            'invoiceAt' => $invoiceItem->getSaleInvoice()->getInvoiceAt()->format('Y-m-d'),
                            'amount' => $invoiceItem->getSaleInvoice()->getAmount(),
                            'amountPaid' => $invoiceItem->getSaleInvoice()->getAmountPaid(),
                            'balance' => $invoiceItem->getSaleInvoice()->getBalance(),
                            'deadLine' => $invoiceItem->getSaleInvoice()->getDeadLine(),
                            ]
                    ];
                }, $invoiceItems),
            ],
        ];


        $saleSettlements= $saleSettlementRepository->findAll();
        $registration = $saleSettlement->getStudentRegistration();
        $table1 = [];

        foreach ($saleSettlements as $saleSettlement){
            if($saleSettlement->getStudentRegistration()->getId() === $registration->getId() ){
                $table1 [] = [
                    '@id' => "/api/saleSettlement/" . $saleSettlement->getId(),
                    '@type' => "SaleSettlement",
                    'id' => $saleSettlement->getId(),
                    'reference' => $saleSettlement->getReference(),
                    'amountPay' => $saleSettlement->getAmountPay(),
                    'amountRest' => $saleSettlement->getAmountRest(),
                    'settleAt' => $saleSettlement->getSettleAt()->format('Y-m-d'),
                    'year' => $saleSettlement->getYear()->getYear(),
                    'firstName' => $saleSettlement->getStudentRegistration()->getStudent()->getFirstName(),
                    'name' => $saleSettlement->getStudentRegistration()->getStudent()->getName(),
                    'class' => $saleSettlement->getClass()->getCode(),
                    'matricule' => $saleSettlement->getStudentRegistration()->getStudent()->getMatricule(),
                    ];
            }
        }

        return $this->json(['hydra:member' => $table, 'hydra:member1' => $table1]);
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



