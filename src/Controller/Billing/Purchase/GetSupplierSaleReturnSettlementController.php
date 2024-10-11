<?php

namespace App\Controller\Billing\Purchase;

use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSupplierSaleReturnSettlementController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly SaleSettlementRepository $saleSettlementRepository
                                )
    {
    }

    public function __invoke(SaleSettlementRepository $saleSettlementRepository,
                             Request $request): JsonResponse
    {

        $saleSettlements = $this->saleSettlementRepository->getSettlementBySupplier();

        $supplierSettlements = [];

        if ($saleSettlements){
            foreach ($saleSettlements as $supplierSettlement){
                $supplierSettlements[] = [
                    'id' => $supplierSettlement->getId(),
                    '@id' => '/api/get/settlement/'.$supplierSettlement->getId(),
                    'type' => 'SaleInvoice',
                    'supplier' =>$supplierSettlement->getSupplier(),
                    'settleAt' => $supplierSettlement->getSettleAt() ? $supplierSettlement->getSettleAt()->format('Y-m-d') : '',
                    'saleReturnInvoice' => $supplierSettlement->getSaleReturnInvoice(),
                    'reference' => $supplierSettlement->getReference(),

                    'amountPay' => $supplierSettlement->getAmountPay(),
                    'bank' => $supplierSettlement->getBank(),
                    'bankAccount' => $supplierSettlement->getBankAccount(),
                    'cashDesk' => $supplierSettlement->getCashDesk(),
                    'paymentGateway' => $supplierSettlement->getPaymentGateway(),
                    'paymentMethod' => $supplierSettlement->getPaymentMethod(),
                    'isValidate' => $supplierSettlement->isIsValidate(),

                    'studentRegistration' => [
                        'id' => $supplierSettlement->getStudentRegistration() ? $supplierSettlement->getStudentRegistration()->getId() : '',
                        //'@id' => '/api/get/student/registration/'. $saleReturnInvoice->getStudentRegistration()->getId(),
                        'type' => 'StudentRegistration',
                        'center' => $supplierSettlement->getStudentRegistration() ? $supplierSettlement->getStudentRegistration()->getCenter() : '',
                        'student' => [
                            'id' => $supplierSettlement->getStudentRegistration() ? $supplierSettlement->getStudentRegistration()->getStudent()->getId() : '',
                            //'@id' => '/api/students/'. $saleReturnInvoice->getStudentRegistration()->getStudent()->getId(),
                            'type' => 'Student',
                            'name' => $supplierSettlement->getStudentRegistration() ? $supplierSettlement->getStudentRegistration()->getStudent()->getName() : '',
                            'studentemail' => $supplierSettlement->getStudentRegistration() ? $supplierSettlement->getStudentRegistration()->getStudent()->getStudentemail() : '',
                            'studentphone' => $supplierSettlement->getStudentRegistration() ? $supplierSettlement->getStudentRegistration()->getStudent()->getStudentphone() : '',

                        ],
                    ],

                ];
            }
        }


        return $this->json(['hydra:member' => $supplierSettlements]);
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

    public function settlementStatus(SaleReturnInvoice $saleReturnInvoice){
        $settlementStatus = '';
        $checkSettlements = $this->saleSettlementRepository->findBy(['invoice' => $saleReturnInvoice, 'isValidate' => true]);
        if ($checkSettlements){
            $totalSettlement = $this->saleSettlementRepository->sumSettlementValidatedAmountByInvoice($saleReturnInvoice)[0][1];
            if ($saleReturnInvoice->getAmount() <= $totalSettlement){
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
