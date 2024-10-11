<?php

namespace App\Controller\Billing\Purchase\Invoice;

use App\Entity\Billing\Purchase\PurchaseInvoice;
use App\Entity\Billing\Purchase\PurchaseInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Billing\Purchase\PurchaseSettlementRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetPurchaseInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly PurchaseSettlementRepository $purchaseSettlementRepository,
                                private readonly SystemSettingsRepository $systemSettingsRepository,
                                )
    {
    }

    public function __invoke(PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                             PurchaseInvoiceRepository $purchaseInvoiceRepository,
                             Request $request): JsonResponse
    {

        $purchaseInvoices = [];

        if($this->getUser()->isIsBranchManager()){
            $invoices = $purchaseInvoiceRepository->findBy([], ['id'=> 'DESC']);

            foreach ($invoices as $invoice){
                $purchaseInvoices[] = [
                    'id' => $invoice->getId(),
                    '@id' => '/api/get/purchase-invoice/'.$invoice->getId(),
                    'type' => 'PurchaseInvoice',
                    'balance' =>$invoice->getBalance(),

                    'invoiceAt' => $invoice->getInvoiceAt() ? $invoice->getInvoiceAt()->format('Y-m-d') : '',
                    'deadLine' => $invoice->getDeadLine() ? $invoice->getDeadLine()->format('Y-m-d') : '',
                    'invoiceNumber' => $invoice->getInvoiceNumber(),
                    'shippingAddress' => $invoice->getShippingAddress(),
                    'paymentReference' => $invoice->getPaymentReference(),

                    'amount' => $invoice->getAmount(),
                    'amountTtc' => $invoice->getTtc(),
                    'amountPaid' => $invoice->getAmountPaid(),
                    'settlementStatus' => $this->settlementStatus($invoice),

                    'supplier' => $invoice->getSupplier(),

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

                        // get cash desk
                        $invoices = $purchaseInvoiceRepository->findBy(['branch' => $userBranch], ['id' => 'DESC']);
                        foreach ($invoices as $invoice){
                            $purchaseInvoices[] = [
                                'id' => $invoice->getId(),
                                '@id' => '/api/get/purchase-invoice/'.$invoice->getId(),
                                'type' => 'PurchaseInvoice',
                                'balance' =>$invoice->getBalance(),
                                'invoiceAt' => $invoice->getInvoiceAt() ? $invoice->getInvoiceAt()->format('Y-m-d') : '',
                                'deadLine' => $invoice->getDeadLine() ? $invoice->getDeadLine()->format('Y-m-d') : '',
                                'invoiceNumber' => $invoice->getInvoiceNumber(),
                                'shippingAddress' => $invoice->getShippingAddress(),
                                'paymentReference' => $invoice->getPaymentReference(),
                                'amount' => $invoice->getAmount(),
                                'amountTtc' => $invoice->getTtc(),
                                'amountPaid' => $invoice->getAmountPaid(),
                                'settlementStatus' => $this->settlementStatus($invoice),
                                'supplier' => $invoice->getSupplier(),
                                'status' => $invoice->getStatus(),
                                'otherStatus' => $invoice->getOtherStatus(),
                                'branch' => [
                                    '@id' => "/api/get/branch/" . $invoice->getBranch()->getId(),
                                    '@type' => "Branch",
                                    'id' => $invoice->getBranch() ? $invoice->getBranch()->getId() : '',
                                    'name' => $invoice->getBranch() ? $invoice->getBranch()->getName() : '',
                                ],

                            ];
                        }
                    }
                }
                else {
                    $invoices = $purchaseInvoiceRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

                    foreach ($invoices as $invoice){
                        if ($invoice) {
                            $purchaseInvoices[] = [
                                'id' => $invoice->getId(),
                                '@id' => '/api/get/purchase-invoice/'.$invoice->getId(),
                                'type' => 'PurchaseInvoice',
                                'balance' =>$invoice->getBalance(),
                                'invoiceAt' => $invoice->getInvoiceAt() ? $invoice->getInvoiceAt()->format('Y-m-d') : '',
                                'deadLine' => $invoice->getDeadLine() ? $invoice->getDeadLine()->format('Y-m-d') : '',
                                'invoiceNumber' => $invoice->getInvoiceNumber(),
                                'shippingAddress' => $invoice->getShippingAddress(),
                                'paymentReference' => $invoice->getPaymentReference(),
                                'amount' => $invoice->getAmount(),
                                'amountTtc' => $invoice->getTtc(),
                                'amountPaid' => $invoice->getAmountPaid(),
                                'settlementStatus' => $this->settlementStatus($invoice),
                                'supplier' => $invoice->getSupplier(),
                                'status' => $invoice->getStatus(),
                                'otherStatus' => $invoice->getOtherStatus(),
                                'branch' => [
                                    '@id' => "/api/get/branch/" . $invoice->getBranch()->getId(),
                                    '@type' => "Branch",
                                    'id' => $invoice->getBranch() ? $invoice->getBranch()->getId() : '',
                                    'name' => $invoice->getBranch() ? $invoice->getBranch()->getName() : '',
                                ],

                            ];
                        }
                    }

                }
            }
        }

        return $this->json(['hydra:member' => $purchaseInvoices]);
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

    public function taxes(PurchaseInvoiceItem $purchaseInvoiceItem){
        $taxes = [];

        foreach ($purchaseInvoiceItem->getTaxes() as $tax){
            $taxes[] = [
                'id' => $tax->getId(),
                'name' => $tax->getName(),
                'rate' => $tax->getRate(),
                'label' => $tax->getLabel(),
            ];
        }
        return $taxes;
    }

    public function settlementStatus(PurchaseInvoice $purchaseInvoice): string
    {
        $checkSettlements = $this->purchaseSettlementRepository->findBy(['invoice' => $purchaseInvoice, 'isValidate' => true]);
        if ($checkSettlements)
        {
            $totalSettlement = $this->purchaseSettlementRepository->sumSettlementValidatedAmountByPurchaseInvoice($purchaseInvoice)[0][1];
            if ($purchaseInvoice->getTtc() == $totalSettlement){
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
