<?php

namespace App\Controller\Billing\Sale\Return;

use App\Entity\Billing\Sale\SaleInvoiceItem;
use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSaleReturnInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly SaleSettlementRepository $saleSettlementRepository,
                                private readonly SystemSettingsRepository $systemSettingsRepository)
    {
    }

    public function __invoke(SaleReturnInvoiceItemRepository $saleReturnInvoiceItemRepository, SaleReturnInvoiceRepository $saleReturnInvoiceRepository, Request $request): JsonResponse
    {
        $saleReturnInvoices = [];

        if($this->getUser()->isIsBranchManager()){
            $returnInvoices = $saleReturnInvoiceRepository->findBy(['isStandard' => true], ['id'=> 'DESC']);

            foreach ($returnInvoices as $returnInvoice){
                $saleReturnInvoices[] = [
                    'id' => $returnInvoice->getId(),
                    '@id' => '/api/get/sale-return-invoice/'.$returnInvoice->getId(),
                    'type' => 'SaleReturnInvoice',
                    'balance' =>$returnInvoice->getBalance(),

                    'saleInvoice' => $returnInvoice->getSaleInvoice() ? $returnInvoice->getSaleInvoice()->getInvoiceNumber() : '',

                    'invoiceAt' => $returnInvoice->getInvoiceAt() ? $returnInvoice->getInvoiceAt()->format('Y-m-d') : '',
                    'deadLine' => $returnInvoice->getDeadLine() ? $returnInvoice->getDeadLine()->format('Y-m-d') : '',
                    'invoiceNumber' => $returnInvoice->getInvoiceNumber(),
                    'shippingAddress' => $returnInvoice->getShippingAddress(),
                    'paymentReference' => $returnInvoice->getPaymentReference(),

                    'amount' => $returnInvoice->getAmount(),
                    'amountTtc' => $returnInvoice->getTtc(),
                    'amountPaid' => $returnInvoice->getAmountPaid(),
                    'settlementStatus' => $this->settlementStatus($returnInvoice),

                    'customer' => $returnInvoice->getCustomer(),

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

                        $returnInvoices = $saleReturnInvoiceRepository->findBy(['branch' => $userBranch, 'isStandard' => true], ['id' => 'DESC']);
                        foreach ($returnInvoices as $returnInvoice)
                        {
                            $saleReturnInvoices[] = [
                                'id' => $returnInvoice->getId(),
                                '@id' => '/api/get/sale-return-invoice/'.$returnInvoice->getId(),
                                'type' => 'SaleReturnInvoice',
                                'balance' =>$returnInvoice->getBalance(),

                                'saleInvoice' => $returnInvoice->getSaleInvoice() ? $returnInvoice->getSaleInvoice()->getInvoiceNumber() : '',

                                'invoiceAt' => $returnInvoice->getInvoiceAt() ? $returnInvoice->getInvoiceAt()->format('Y-m-d') : '',
                                'deadLine' => $returnInvoice->getDeadLine() ? $returnInvoice->getDeadLine()->format('Y-m-d') : '',
                                'invoiceNumber' => $returnInvoice->getInvoiceNumber(),
                                'shippingAddress' => $returnInvoice->getShippingAddress(),
                                'paymentReference' => $returnInvoice->getPaymentReference(),

                                'amount' => $returnInvoice->getAmount(),
                                'amountTtc' => $returnInvoice->getTtc(),
                                'amountPaid' => $returnInvoice->getAmountPaid(),
                                'settlementStatus' => $this->settlementStatus($returnInvoice),

                                'customer' => $returnInvoice->getCustomer(),

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
                else {
                    $returnInvoices = $saleReturnInvoiceRepository->findBy(['branch' => $this->getUser()->getBranch(), 'isStandard' => true], ['id' => 'DESC']);

                    foreach ($returnInvoices as $returnInvoice)
                    {
                        $saleReturnInvoices[] = [
                            'id' => $returnInvoice->getId(),
                            '@id' => '/api/get/sale-return-invoice/'.$returnInvoice->getId(),
                            'type' => 'SaleReturnInvoice',
                            'balance' =>$returnInvoice->getBalance(),

                            'saleInvoice' => $returnInvoice->getSaleInvoice() ? $returnInvoice->getSaleInvoice()->getInvoiceNumber() : '',


                            'invoiceAt' => $returnInvoice->getInvoiceAt() ? $returnInvoice->getInvoiceAt()->format('Y-m-d') : '',
                            'deadLine' => $returnInvoice->getDeadLine() ? $returnInvoice->getDeadLine()->format('Y-m-d') : '',
                            'invoiceNumber' => $returnInvoice->getInvoiceNumber(),
                            'shippingAddress' => $returnInvoice->getShippingAddress(),
                            'paymentReference' => $returnInvoice->getPaymentReference(),

                            'amount' => $returnInvoice->getAmount(),
                            'amountTtc' => $returnInvoice->getTtc(),
                            'amountPaid' => $returnInvoice->getAmountPaid(),
                            'settlementStatus' => $this->settlementStatus($returnInvoice),

                            'customer' => $returnInvoice->getCustomer(),
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

        return $this->json(['hydra:member' => $saleReturnInvoices]);
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

    public function settlementStatus(SaleReturnInvoice $saleReturnInvoice): string
    {
        $checkSettlements = $this->saleSettlementRepository->findBy(['saleReturnInvoice' => $saleReturnInvoice, 'isValidate' => true]);
        if ($checkSettlements)
        {
            $totalSettlement = $this->saleSettlementRepository->sumReturnSettlementValidatedAmountByInvoice($saleReturnInvoice)[0][1];
            if ($saleReturnInvoice->getTtc() == $totalSettlement){
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
