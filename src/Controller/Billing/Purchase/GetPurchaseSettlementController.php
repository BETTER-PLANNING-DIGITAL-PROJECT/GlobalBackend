<?php

namespace App\Controller\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseInvoice;
use App\Entity\Billing\Purchase\PurchaseInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseSettlementRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetPurchaseSettlementController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface        $tokenStorage,
                                private readonly PurchaseSettlementRepository $purchaseSettlementRepository,
                                private readonly PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                                private readonly SystemSettingsRepository     $systemSettingsRepository
    )
    {
    }

    public function __invoke(Request $request): JsonResponse
    {

        $mySettlements = [];

        if($this->getUser()->isIsBranchManager()){
            $purchaseSettlements = $this->purchaseSettlementRepository->findBy([], ['id' => 'DESC']);

            foreach ($purchaseSettlements as $purchaseSettlement){
                $mySettlements[] = [
                    'id' => $purchaseSettlement->getId(),
                    '@id' => '/api/get/purchase-settlement/'.$purchaseSettlement->getId(),
                    'type' => 'PurchaseSettlement',
                    'invoice' => $purchaseSettlement->getInvoice(),
                    'purchaseReturnInvoice' => $purchaseSettlement->getPurchaseReturnInvoice(),
                    'supplier' => $purchaseSettlement->getSupplier(),
                    'bank' => $purchaseSettlement->getBank() ? $purchaseSettlement->getBank() : '',
                    'cashDesk' => $purchaseSettlement->getCashDesk() ? $purchaseSettlement->getCashDesk() : '',
                    'paymentMethod' =>$purchaseSettlement->getPaymentMethod(),
                    'reference' =>$purchaseSettlement->getReference(),
                    'amountPay' =>$purchaseSettlement->getAmountPay(),
                    'settleAt' => $purchaseSettlement->getSettleAt() ? $purchaseSettlement->getSettleAt()->format('Y-m-d') : '',
                    'note' => $purchaseSettlement->getNote(),
                    'isValidate' => $purchaseSettlement->isIsValidate(),
                    'paymentGateway' => $purchaseSettlement->getPaymentGateway() ?: '',
                    'branch' => [
                        '@id' => "/api/get/branch/" . $purchaseSettlement->getBranch()->getId(),
                        '@type' => "Branch",
                        'id' => $purchaseSettlement->getBranch() ? $purchaseSettlement->getBranch()->getId() : '',
                        'name' => $purchaseSettlement->getBranch() ? $purchaseSettlement->getBranch()->getName() : '',
                    ],
//                    'validateBy' => [
//                        '@id' => "/api/get/validateBy/" . $purchaseSettlement->getValidateBy() ? $purchaseSettlement->getValidateBy()->getId() :'',
//                        '@type' => "firstname",
//                        'id' => $purchaseSettlement->getValidateBy() ? $purchaseSettlement->getValidateBy()->getId() : '',
//                        'firstname' => $purchaseSettlement->getValidateBy() ? $purchaseSettlement->getValidateBy()->getFirstname() : '',
//                    ],

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

                        $purchaseSettlements = $this->purchaseSettlementRepository->findBy(['branch' => $userBranch], ['id' => 'DESC']);
                        foreach ($purchaseSettlements as $purchaseSettlement){
                            $mySettlements[] = [
                                'id' => $purchaseSettlement->getId(),
                                '@id' => '/api/get/purchase-settlement/'.$purchaseSettlement->getId(),
                                'type' => 'PurchaseSettlement',
                                'invoice' => $purchaseSettlement->getInvoice() ? $purchaseSettlement->getInvoice() : '',
                                'purchaseReturnInvoice' => $purchaseSettlement->getPurchaseReturnInvoice(),
                                'supplier' => $purchaseSettlement->getSupplier(),
                                'bank' => $purchaseSettlement->getBank() ? $purchaseSettlement->getBank() : '',
                                'cashDesk' => $purchaseSettlement->getCashDesk() ? $purchaseSettlement->getCashDesk() : '',
                                'paymentMethod' =>$purchaseSettlement->getPaymentMethod(),
                                'reference' =>$purchaseSettlement->getReference(),
                                'amountPay' =>$purchaseSettlement->getAmountPay(),
                                'settleAt' => $purchaseSettlement->getSettleAt() ? $purchaseSettlement->getSettleAt()->format('Y-m-d') : '',
                                'note' => $purchaseSettlement->getNote(),
                                'isValidate' => $purchaseSettlement->isIsValidate(),
                                'paymentGateway' => $purchaseSettlement->getPaymentGateway() ?: '',
                                'branch' => [
                                    '@id' => "/api/get/branch/" . $purchaseSettlement->getBranch()->getId(),
                                    '@type' => "Branch",
                                    'id' => $purchaseSettlement->getBranch() ? $purchaseSettlement->getBranch()->getId() : '',
                                    'name' => $purchaseSettlement->getBranch() ? $purchaseSettlement->getBranch()->getName() : '',
                                ],
//                                'validateBy' => [
//                                    '@id' => "/api/get/validateBy/" . $purchaseSettlement->getValidateBy() ? $purchaseSettlement->getValidateBy()->getId() :'',
//                                    '@type' => "firstname",
//                                    'id' => $purchaseSettlement->getValidateBy() ? $purchaseSettlement->getValidateBy()->getId() : '',
//                                    'firstname' => $purchaseSettlement->getValidateBy() ? $purchaseSettlement->getValidateBy()->getFirstname() : '',
//                                ],

                            ];
                        }
                    }
                }
                else {
                    $purchaseSettlements = $this->purchaseSettlementRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

                    foreach ($purchaseSettlements as $purchaseSettlement) {
                        if ($purchaseSettlement) {
                            $mySettlements[] = [
                                'id' => $purchaseSettlement->getId(),
                                '@id' => '/api/get/purchase-settlement/'.$purchaseSettlement->getId(),
                                'type' => 'PurchaseSettlement',
                                'invoice' => $purchaseSettlement->getInvoice() ? $purchaseSettlement->getInvoice() : '',
                                'purchaseReturnInvoice' => $purchaseSettlement->getPurchaseReturnInvoice(),
                                'supplier' => $purchaseSettlement->getSupplier(),
                                'bank' => $purchaseSettlement->getBank() ? $purchaseSettlement->getBank() : '',
                                'cashDesk' => $purchaseSettlement->getCashDesk() ? $purchaseSettlement->getCashDesk() : '',
                                'paymentMethod' =>$purchaseSettlement->getPaymentMethod(),
                                'reference' =>$purchaseSettlement->getReference(),
                                'amountPay' =>$purchaseSettlement->getAmountPay(),
                                'settleAt' => $purchaseSettlement->getSettleAt() ? $purchaseSettlement->getSettleAt()->format('Y-m-d') : '',
                                'note' => $purchaseSettlement->getNote(),
                                'isValidate' => $purchaseSettlement->isIsValidate(),
                                'paymentGateway' => $purchaseSettlement->getPaymentGateway() ?: '',
                                'branch' => [
                                    '@id' => "/api/get/branch/" . $purchaseSettlement->getBranch()->getId(),
                                    '@type' => "Branch",
                                    'id' => $purchaseSettlement->getBranch() ? $purchaseSettlement->getBranch()->getId() : '',
                                    'name' => $purchaseSettlement->getBranch() ? $purchaseSettlement->getBranch()->getName() : '',
                                ],
//                                'validateBy' => [
//                                    '@id' => "/api/get/validateBy/" . $purchaseSettlement->getValidateBy() ? $purchaseSettlement->getValidateBy()->getId() :'',
//                                    '@type' => "firstname",
//                                    'id' => $purchaseSettlement->getValidateBy() ? $purchaseSettlement->getValidateBy()->getId() : '',
//                                    'firstname' => $purchaseSettlement->getValidateBy() ? $purchaseSettlement->getValidateBy()->getFirstname() : '',
//                                ],

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

    public function purchaseInvoiceItem(PurchaseInvoice $purchaseInvoice): array
    {
        $items = [];
        $purchaseInvoiceItems = $this->purchaseInvoiceItemRepository->findBy(['purchaseInvoice' => $purchaseInvoice]);
        if ($purchaseInvoiceItems){
            foreach ($purchaseInvoiceItems as $purchaseInvoiceItem){
                $items[] = [
                    'id' => $purchaseInvoiceItem->getId(),
                    '@id' => '/api/get/purchase-invoice-item/'.$purchaseInvoiceItem->getId(),
                    'type' => 'PurchaseInvoiceItem',
                    'item' => $purchaseInvoiceItem->getItem(),
                    'name' => $purchaseInvoiceItem->getName(),
                    'amount' => $purchaseInvoiceItem->getAmount(),
                    'amountTtc' => $purchaseInvoiceItem->getAmountTtc(),
                    'quantity' => $purchaseInvoiceItem->getQuantity(),
                    'returnQuantity' => $purchaseInvoiceItem->getReturnQuantity(),
                    'discount' => $purchaseInvoiceItem->getDiscount(),
                    'discountAmount' => $purchaseInvoiceItem->getDiscountAmount(),
                    'amountWithTaxes' => $purchaseInvoiceItem->getTaxAmount(),
                    'pu' => $purchaseInvoiceItem->getPu(),
                    'taxes' => $this->taxes($purchaseInvoiceItem)
                ];

            }
        }

        return $items;
    }

    public function taxes(PurchaseInvoiceItem $purchaseInvoiceItem): array
    {
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
}

