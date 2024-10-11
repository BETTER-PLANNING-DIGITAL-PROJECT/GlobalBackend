<?php

namespace App\Controller\Billing\Sale\Invoice;

use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Billing\Sale\SaleInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSaleInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly SaleSettlementRepository $saleSettlementRepository,
                                private readonly SystemSettingsRepository $systemSettingsRepository)
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository, SaleInvoiceRepository $saleInvoiceRepository, Request $request): JsonResponse
    {
        $saleInvoices = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $invoices = $saleInvoiceRepository->findBy(['isStandard' => true], ['id'=> 'DESC']);

            foreach ($invoices as $invoice){
                $saleInvoices[] = [
                    'id' => $invoice->getId(),
                    '@id' => '/api/get/sale-invoice/'.$invoice->getId(),
                    'type' => 'SaleInvoice',
                    'balance' =>$invoice->getBalance(),

                    'class' => [
                        'id' => $invoice->getClass() ? $invoice->getClass()->getId() : '',
                        'type' => 'SchoolClass',
                        'code' => $invoice->getClass() ? $invoice->getClass()->getCode() : '',
                    ],

                    'invoiceAt' => $invoice->getInvoiceAt() ? $invoice->getInvoiceAt()->format('Y-m-d') : '',
                    'deadLine' => $invoice->getDeadLine() ? $invoice->getDeadLine()->format('Y-m-d') : '',
                    'invoiceNumber' => $invoice->getInvoiceNumber(),
                    'shippingAddress' => $invoice->getShippingAddress(),
                    'paymentReference' => $invoice->getPaymentReference(),

                    'amount' => $invoice->getAmount(),
                    'amountTtc' => $invoice->getTtc(),
                    'amountPaid' => $invoice->getAmountPaid(),
                    'settlementStatus' => $this->settlementStatus($invoice),

                    'customer' => $invoice->getCustomer(),

                    'branch' => [
                        '@id' => "/api/get/branch/" . $invoice->getBranch()->getId(),
                        '@type' => "Branch",
                        'id' => $invoice->getBranch() ? $invoice->getBranch()->getId() : '',
                        'name' => $invoice->getBranch() ? $invoice->getBranch()->getName() : '',
                    ],
                    'status' => $invoice->getStatus(),
                    'otherStatus' => $invoice->getOtherStatus(),
                ];
            }
        }
        else
        {
            $systemSettings = $this->systemSettingsRepository->findOneBy([]);
            if($systemSettings)
            {
                if($systemSettings->isIsBranches())
                {
                    $userBranches = $this->getUser()->getUserBranches();
                    foreach ($userBranches as $userBranch) {

                        $invoices = $saleInvoiceRepository->findBy(['branch' => $userBranch, 'isStandard' => true], ['id' => 'DESC']);
                        foreach ($invoices as $invoice)
                        {
                            $saleInvoices[] = [
                                'id' => $invoice->getId(),
                                '@id' => '/api/get/sale-invoice/'.$invoice->getId(),
                                'type' => 'SaleInvoice',
                                'balance' =>$invoice->getBalance(),

                                'class' => [
                                    'id' => $invoice->getClass() ? $invoice->getClass()->getId() : '',
                                    'type' => 'SchoolClass',
                                    'code' => $invoice->getClass() ? $invoice->getClass()->getCode() : '',
                                ],

                                'invoiceAt' => $invoice->getInvoiceAt() ? $invoice->getInvoiceAt()->format('Y-m-d') : '',
                                'deadLine' => $invoice->getDeadLine() ? $invoice->getDeadLine()->format('Y-m-d') : '',
                                'invoiceNumber' => $invoice->getInvoiceNumber(),
                                'shippingAddress' => $invoice->getShippingAddress(),
                                'paymentReference' => $invoice->getPaymentReference(),

                                'amount' => $invoice->getAmount(),
                                'amountTtc' => $invoice->getTtc(),
                                'amountPaid' => $invoice->getAmountPaid(),
                                'settlementStatus' => $this->settlementStatus($invoice),

                                'customer' => $invoice->getCustomer(),

                                'branch' => [
                                    '@id' => "/api/get/branch/" . $invoice->getBranch()->getId(),
                                    '@type' => "Branch",
                                    'id' => $invoice->getBranch() ? $invoice->getBranch()->getId() : '',
                                    'name' => $invoice->getBranch() ? $invoice->getBranch()->getName() : '',
                                ],
                                'status' => $invoice->getStatus(),
                                'otherStatus' => $invoice->getOtherStatus(),

                            ];
                        }
                    }
                }
                else {
                    $invoices = $saleInvoiceRepository->findBy(['branch' => $this->getUser()->getBranch(), 'isStandard' => true], ['id' => 'DESC']);

                    foreach ($invoices as $invoice)
                    {
                        if ($invoices) {
                            $saleInvoices[] = [
                                'id' => $invoice->getId(),
                                '@id' => '/api/get/sale-invoice/'.$invoice->getId(),
                                'type' => 'SaleInvoice',
                                'balance' =>$invoice->getBalance(),

                                'class' => [
                                    'id' => $invoice->getClass() ? $invoice->getClass()->getId() : '',
                                    'type' => 'SchoolClass',
                                    'code' => $invoice->getClass() ? $invoice->getClass()->getCode() : '',
                                ],

                                'invoiceAt' => $invoice->getInvoiceAt() ? $invoice->getInvoiceAt()->format('Y-m-d') : '',
                                'deadLine' => $invoice->getDeadLine() ? $invoice->getDeadLine()->format('Y-m-d') : '',
                                'invoiceNumber' => $invoice->getInvoiceNumber(),
                                'shippingAddress' => $invoice->getShippingAddress(),
                                'paymentReference' => $invoice->getPaymentReference(),

                                'amount' => $invoice->getAmount(),
                                'amountTtc' => $invoice->getTtc(),
                                'amountPaid' => $invoice->getAmountPaid(),
                                'settlementStatus' => $this->settlementStatus($invoice),

                                'customer' => $invoice->getCustomer(),

                                'branch' => [
                                    '@id' => "/api/get/branch/" . $invoice->getBranch()->getId(),
                                    '@type' => "Branch",
                                    'id' => $invoice->getBranch() ? $invoice->getBranch()->getId() : '',
                                    'name' => $invoice->getBranch() ? $invoice->getBranch()->getName() : '',
                                ],
                                'status' => $invoice->getStatus(),
                                'otherStatus' => $invoice->getOtherStatus(),

                            ];
                        }
                    }

                }
            }
        }

        return $this->json(['hydra:member' => $saleInvoices]);
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

    public function taxes(SaleInvoiceItem $saleInvoiceItem): array
    {
        $taxes = [];

        foreach ($saleInvoiceItem->getTaxes() as $tax){
            $taxes[] = [
                'id' => $tax->getId(),
                'name' => $tax->getName(),
                'rate' => $tax->getRate(),
                'label' => $tax->getLabel(),
            ];
        }
        return $taxes;
    }

    public function settlementStatus(SaleInvoice $invoice): string
    {
        $checkSettlements = $this->saleSettlementRepository->findBy(['invoice' => $invoice, 'isValidate' => true]);
        if ($checkSettlements)
        {
            $totalSettlement = $this->saleSettlementRepository->sumSettlementValidatedAmountByInvoice($invoice)[0][1];
            if ($invoice->getTtc() == $totalSettlement){
                $settlementStatus = 'Complete Paid';
            }
            else{
                $settlementStatus = 'Partial Paid';
            }
        }
        else{
            $settlementStatus = 'Not paid';
        }

        return $settlementStatus;
    }
}
