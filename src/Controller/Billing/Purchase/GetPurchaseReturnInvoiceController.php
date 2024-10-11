<?php

namespace App\Controller\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseReturnInvoice;
use App\Entity\Billing\Purchase\PurchaseReturnInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceRepository;
use App\Repository\Billing\Purchase\PurchaseSettlementRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetPurchaseReturnInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly PurchaseSettlementRepository $purchaseSettlementRepository,
                                private readonly SystemSettingsRepository $systemSettingsRepository)
    {
    }

    public function __invoke(PurchaseReturnInvoiceItemRepository $purchaseReturnInvoiceItemRepository,
                             PurchaseReturnInvoiceRepository $purchaseReturnInvoiceRepository,
                             Request $request): JsonResponse
    {
        $purchaseReturnInvoices = [];

        if($this->getUser()->isIsBranchManager()){
            $returnInvoices = $purchaseReturnInvoiceRepository->findBy([], ['id'=> 'DESC']);

            foreach ($returnInvoices as $returnInvoice){
                $purchaseReturnInvoices[] = [
                    'id' => $returnInvoice->getId(),
                    '@id' => '/api/get/purchase-return-invoice/'.$returnInvoice->getId(),
                    'type' => 'PurchaseReturnInvoice',
                    'balance' =>$returnInvoice->getBalance(),

                    'purchaseInvoice' => $returnInvoice->getPurchaseInvoice() ? $returnInvoice->getPurchaseInvoice()->getInvoiceNumber() : '',

                    'invoiceAt' => $returnInvoice->getInvoiceAt() ? $returnInvoice->getInvoiceAt()->format('Y-m-d') : '',
                    'deadLine' => $returnInvoice->getDeadLine() ? $returnInvoice->getDeadLine()->format('Y-m-d') : '',
                    'invoiceNumber' => $returnInvoice->getInvoiceNumber(),
                    'shippingAddress' => $returnInvoice->getShippingAddress(),
                    'paymentReference' => $returnInvoice->getPaymentReference(),

                    'amount' => $returnInvoice->getAmount(),
                    'amountTtc' => $returnInvoice->getTtc(),
                    'amountPaid' => $returnInvoice->getAmountPaid(),
                    'settlementStatus' => $this->settlementStatus($returnInvoice),

                    'supplier' => $returnInvoice->getSupplier(),

                    'branch' => [
                        '@id' => "/api/get/branch/" . $returnInvoice->getBranch()->getId(),
                        '@type' => "Branch",
                        'id' => $returnInvoice->getBranch() ? $returnInvoice->getBranch()->getId() : '',
                        'name' => $returnInvoice->getBranch() ? $returnInvoice->getBranch()->getName() : '',
                    ],
                    'status' => $returnInvoice->getStatus(),
                    'otherStatus' => $returnInvoice->getOtherStatus(),
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

                        $returnInvoices = $purchaseReturnInvoiceRepository->findBy(['branch' => $userBranch], ['id' => 'DESC']);
                        foreach ($returnInvoices as $returnInvoice)
                        {
                            $purchaseReturnInvoices[] = [
                                'id' => $returnInvoice->getId(),
                                '@id' => '/api/get/purchase-return-invoice/'.$returnInvoice->getId(),
                                'type' => 'PurchaseReturnInvoice',
                                'balance' =>$returnInvoice->getBalance(),

                                'purchaseInvoice' => $returnInvoice->getPurchaseInvoice() ? $returnInvoice->getPurchaseInvoice()->getInvoiceNumber() : '',

                                'invoiceAt' => $returnInvoice->getInvoiceAt() ? $returnInvoice->getInvoiceAt()->format('Y-m-d') : '',
                                'deadLine' => $returnInvoice->getDeadLine() ? $returnInvoice->getDeadLine()->format('Y-m-d') : '',
                                'invoiceNumber' => $returnInvoice->getInvoiceNumber(),
                                'shippingAddress' => $returnInvoice->getShippingAddress(),
                                'paymentReference' => $returnInvoice->getPaymentReference(),

                                'amount' => $returnInvoice->getAmount(),
                                'amountTtc' => $returnInvoice->getTtc(),
                                'amountPaid' => $returnInvoice->getAmountPaid(),
                                'settlementStatus' => $this->settlementStatus($returnInvoice),

                                'supplier' => $returnInvoice->getSupplier(),

                                'branch' => [
                                    '@id' => "/api/get/branch/" . $returnInvoice->getBranch()->getId(),
                                    '@type' => "Branch",
                                    'id' => $returnInvoice->getBranch() ? $returnInvoice->getBranch()->getId() : '',
                                    'name' => $returnInvoice->getBranch() ? $returnInvoice->getBranch()->getName() : '',
                                ],
                                'status' => $returnInvoice->getStatus(),
                                'otherStatus' => $returnInvoice->getOtherStatus(),

                            ];
                        }
                    }
                }
                else
                {
                    $returnInvoices = $purchaseReturnInvoiceRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

                    foreach ($returnInvoices as $returnInvoice)
                    {
                        $purchaseReturnInvoices[] = [
                            'id' => $returnInvoice->getId(),
                            '@id' => '/api/get/purchase-return-invoice/'.$returnInvoice->getId(),
                            'type' => 'PurchaseReturnInvoice',
                            'balance' =>$returnInvoice->getBalance(),

                            'purchaseInvoice' => $returnInvoice->getPurchaseInvoice() ? $returnInvoice->getPurchaseInvoice()->getInvoiceNumber() : '',

                            'invoiceAt' => $returnInvoice->getInvoiceAt() ? $returnInvoice->getInvoiceAt()->format('Y-m-d') : '',
                            'deadLine' => $returnInvoice->getDeadLine() ? $returnInvoice->getDeadLine()->format('Y-m-d') : '',
                            'invoiceNumber' => $returnInvoice->getInvoiceNumber(),
                            'shippingAddress' => $returnInvoice->getShippingAddress(),
                            'paymentReference' => $returnInvoice->getPaymentReference(),

                            'amount' => $returnInvoice->getAmount(),
                            'amountTtc' => $returnInvoice->getTtc(),
                            'amountPaid' => $returnInvoice->getAmountPaid(),
                            'settlementStatus' => $this->settlementStatus($returnInvoice),

                            'supplier' => $returnInvoice->getSupplier(),

                            'branch' => [
                                '@id' => "/api/get/branch/" . $returnInvoice->getBranch()->getId(),
                                '@type' => "Branch",
                                'id' => $returnInvoice->getBranch() ? $returnInvoice->getBranch()->getId() : '',
                                'name' => $returnInvoice->getBranch() ? $returnInvoice->getBranch()->getName() : '',
                            ],
                            'status' => $returnInvoice->getStatus(),
                            'otherStatus' => $returnInvoice->getOtherStatus(),

                        ];
                    }

                }
            }
        }

        return $this->json(['hydra:member' => $purchaseReturnInvoices]);
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

    public function taxes(PurchaseReturnInvoiceItem $purchaseReturnInvoiceItem): array
    {
        $taxes = [];

        foreach ($purchaseReturnInvoiceItem->getTaxes() as $tax){
            $taxes[] = [
                'id' => $tax->getId(),
                'name' => $tax->getName(),
                'rate' => $tax->getRate(),
                'label' => $tax->getLabel(),
            ];
        }
        return $taxes;
    }

    public function settlementStatus(PurchaseReturnInvoice $purchaseReturnInvoice): string
    {
        $checkSettlements = $this->purchaseSettlementRepository->findBy(['purchaseReturnInvoice' => $purchaseReturnInvoice, 'isValidate' => true]);
        if ($checkSettlements)
        {
            $totalSettlement = $this->purchaseSettlementRepository->sumSettlementValidatedAmountByPurchaseReturnInvoice($purchaseReturnInvoice)[0][1];
            if ($purchaseReturnInvoice->getTtc() == $totalSettlement){
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