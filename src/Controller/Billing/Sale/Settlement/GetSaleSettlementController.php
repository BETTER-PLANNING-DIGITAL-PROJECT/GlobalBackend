<?php

namespace App\Controller\Billing\Sale\Settlement;

use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Billing\Sale\SaleInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSaleSettlementController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly SaleSettlementRepository $saleSettlementRepository,
                                private readonly SaleInvoiceItemRepository $saleInvoiceItemRepository,
                                private readonly  SystemSettingsRepository $systemSettingsRepository,
    )
    {
    }

    public function __invoke(Request $request): JsonResponse
    {

        $mySettlements = [];

        if($this->getUser()->isIsBranchManager()){
            $saleSettlements = $this->saleSettlementRepository->findBy([], ['id' => 'DESC']);

            foreach ($saleSettlements as $saleSettlement){
                $mySettlements[] = [
                    'id' => $saleSettlement->getId(),
                    '@id' => '/api/get/sale-settlement/'.$saleSettlement->getId(),
                    'type' => 'SaleInvoice',
                    'invoice' => $saleSettlement->getInvoice(),
                    'saleReturnInvoice' => $saleSettlement->getSaleReturnInvoice(),
                    'customer' => $saleSettlement->getCustomer(),
                    'bank' =>$saleSettlement->getBank(),
                    'bankAccount' =>$saleSettlement->getBankAccount(),
                    'cashDesk' =>$saleSettlement->getCashDesk(),
                    'paymentMethod' =>$saleSettlement->getPaymentMethod(),
                    'reference' =>$saleSettlement->getReference(),
                    'amountPay' =>$saleSettlement->getAmountPay(),
                    'settleAt' => $saleSettlement->getSettleAt() ? $saleSettlement->getSettleAt()->format('Y-m-d') : '',
                    'note' => $saleSettlement->getNote(),
                    'isValidate' => $saleSettlement->isIsValidate(),
                    'paymentGateway' => $saleSettlement->getPaymentGateway(),
                    'branch' => [
                        '@id' => "/api/get/branch/" . $saleSettlement->getBranch()->getId(),
                        '@type' => "Branch",
                        'id' => $saleSettlement->getBranch() ? $saleSettlement->getBranch()->getId() : '',
                        'name' => $saleSettlement->getBranch() ? $saleSettlement->getBranch()->getName() : '',
                    ],

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

                        $saleSettlements = $this->saleSettlementRepository->findBy(['branch' => $userBranch], ['id' => 'DESC']);
                        foreach ($saleSettlements as $saleSettlement){
                            $mySettlements[] = [
                                'id' => $saleSettlement->getId(),
                                '@id' => '/api/get/sale-settlement/'.$saleSettlement->getId(),
                                'type' => 'SaleInvoice',
                                'invoice' => $saleSettlement->getInvoice(),
                                'saleReturnInvoice' => $saleSettlement->getSaleReturnInvoice(),
                                'customer' => $saleSettlement->getCustomer(),
                                'bank' =>$saleSettlement->getBank(),
                                'bankAccount' =>$saleSettlement->getBankAccount(),
                                'cashDesk' =>$saleSettlement->getCashDesk(),
                                'paymentMethod' =>$saleSettlement->getPaymentMethod(),
                                'reference' =>$saleSettlement->getReference(),
                                'amountPay' =>$saleSettlement->getAmountPay(),
                                'settleAt' => $saleSettlement->getSettleAt() ? $saleSettlement->getSettleAt()->format('Y-m-d') : '',
                                'note' => $saleSettlement->getNote(),
                                'isValidate' => $saleSettlement->isIsValidate(),
                                'paymentGateway' => $saleSettlement->getPaymentGateway(),
                                'branch' => [
                                    '@id' => "/api/get/branch/" . $saleSettlement->getBranch()->getId(),
                                    '@type' => "Branch",
                                    'id' => $saleSettlement->getBranch() ? $saleSettlement->getBranch()->getId() : '',
                                    'name' => $saleSettlement->getBranch() ? $saleSettlement->getBranch()->getName() : '',
                                ],

                            ];
                        }
                    }
                }
                else {
                    $saleSettlements = $this->saleSettlementRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

                    foreach ($saleSettlements as $saleSettlement) {
                        if ($saleSettlement) {
                            $mySettlements[] = [
                                'id' => $saleSettlement->getId(),
                                '@id' => '/api/get/sale-settlement/'.$saleSettlement->getId(),
                                'type' => 'SaleInvoice',
                                'invoice' => $saleSettlement->getInvoice(),
                                'saleReturnInvoice' => $saleSettlement->getSaleReturnInvoice(),
                                'customer' => $saleSettlement->getCustomer(),
                                'bank' =>$saleSettlement->getBank(),
                                'bankAccount' =>$saleSettlement->getBankAccount(),
                                'cashDesk' =>$saleSettlement->getCashDesk(),
                                'paymentMethod' =>$saleSettlement->getPaymentMethod(),
                                'reference' =>$saleSettlement->getReference(),
                                'amountPay' =>$saleSettlement->getAmountPay(),
                                'settleAt' => $saleSettlement->getSettleAt() ? $saleSettlement->getSettleAt()->format('Y-m-d') : '',
                                'note' => $saleSettlement->getNote(),
                                'isValidate' => $saleSettlement->isIsValidate(),
                                'paymentGateway' => $saleSettlement->getPaymentGateway(),
                                'branch' => [
                                    '@id' => "/api/get/branch/" . $saleSettlement->getBranch()->getId(),
                                    '@type' => "Branch",
                                    'id' => $saleSettlement->getBranch() ? $saleSettlement->getBranch()->getId() : '',
                                    'name' => $saleSettlement->getBranch() ? $saleSettlement->getBranch()->getName() : '',
                                ],

                            ];
                        }
                    }

                }
            }
        }

        return $this->json(['hydra:member' => $mySettlements]);
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

    public function saleInvoiceItem(SaleInvoice $saleInvoice): array
    {
        $items = [];
        $saleInvoiceItems = $this->saleInvoiceItemRepository->findBy(['saleInvoice' => $saleInvoice]);
        if ($saleInvoiceItems){
            foreach ($saleInvoiceItems as $saleInvoiceItem){
                $items[] = [
                    'id' => $saleInvoiceItem->getId(),
                    '@id' => '/api/get/sale-invoice-item/'.$saleInvoiceItem->getId(),
                    'type' => 'SaleInvoiceItem',
                    'item' => $saleInvoiceItem->getItem(),
                    'name' => $saleInvoiceItem->getName(),
                    'amount' => $saleInvoiceItem->getAmount(),
                    'amountTtc' => $saleInvoiceItem->getAmountTtc(),
                    'quantity' => $saleInvoiceItem->getQuantity(),
                    'returnQuantity' => $saleInvoiceItem->getReturnQuantity(),
                    'discount' => $saleInvoiceItem->getDiscount(),
                    'discountAmount' => $saleInvoiceItem->getDiscountAmount(),
                    'amountWithTaxes' => $saleInvoiceItem->getAmountWithTaxes(),
                    'pu' => $saleInvoiceItem->getPu(),
                    'taxes' => $this->taxes($saleInvoiceItem)
                ];


            }
        }

        return $items;
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
}
