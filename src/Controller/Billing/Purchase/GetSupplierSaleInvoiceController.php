<?php

namespace App\Controller\Billing\Purchase;

use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Billing\Sale\SaleInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSupplierSaleInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly SaleSettlementRepository $saleSettlementRepository,
                                )
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             Request $request): JsonResponse
    {

        $supplierInvoices = $saleInvoiceRepository->findBy(['isCustomer' => false], ['id' => 'DESC']);

        $saleInvoices = [];
        if ($supplierInvoices){

        foreach ($supplierInvoices as $supplierInvoice){
            $saleInvoices[] = [
                'id' => $supplierInvoice->getId(),
                '@id' => '/api/get/supplier-sale-invoice/'.$supplierInvoice->getId(),
                'type' => 'SaleInvoice',
                'balance' =>$supplierInvoice->getBalance(),

                'class' => [
                    'id' => $supplierInvoice->getClass() ? $supplierInvoice->getClass()->getId() : '',
                    'type' => 'SchoolClass',
                    'code' => $supplierInvoice->getClass() ? $supplierInvoice->getClass()->getCode() : '',
                ],

                'invoiceAt' => $supplierInvoice->getInvoiceAt() ? $supplierInvoice->getInvoiceAt()->format('Y-m-d') : '',
                'deadLine' => $supplierInvoice->getDeadLine() ? $supplierInvoice->getDeadLine()->format('Y-m-d') : '',
                'invoiceNumber' => $supplierInvoice->getInvoiceNumber(),
                'shippingAddress' => $supplierInvoice->getShippingAddress(),
                'paymentReference' => $supplierInvoice->getPaymentReference(),

                'amount' => $supplierInvoice->getAmount(),
                'amountTtc' => $supplierInvoice->getTtc(),
                'amountPaid' => $supplierInvoice->getAmountPaid(),
                'settlementStatus' => $this->settlementStatus($supplierInvoice),

                'supplier' => $supplierInvoice->getSupplier(),

                'school' => [
                    'id' => $supplierInvoice->getSchool() ? $supplierInvoice->getSchool()->getId() : '',
                    //'@id' => '/api/get/school/item/'. $supplierInvoice->getSchool()->getId(),
                    'type' => 'School',
                    'name' => $supplierInvoice->getSchool() ? $supplierInvoice->getSchool()->getName() : '',
                ],
                'studentRegistration' => [
                    'id' => $supplierInvoice->getStudentRegistration() ? $supplierInvoice->getStudentRegistration()->getId() : '',
                    //'@id' => '/api/get/student/registration/'. $supplierInvoice->getStudentRegistration()->getId(),
                    'type' => 'StudentRegistration',
                    'center' => $supplierInvoice->getStudentRegistration() ? $supplierInvoice->getStudentRegistration()->getCenter() : '',
                    'student' => [
                        'id' => $supplierInvoice->getStudentRegistration() ? $supplierInvoice->getStudentRegistration()->getStudent()->getId() : '',
                        //'@id' => '/api/students/'. $supplierInvoice->getStudentRegistration()->getStudent()->getId(),
                        'type' => 'Student',
                        'name' => $supplierInvoice->getStudentRegistration() ? $supplierInvoice->getStudentRegistration()->getStudent()->getName() : '',
                        'studentemail' => $supplierInvoice->getStudentRegistration() ? $supplierInvoice->getStudentRegistration()->getStudent()->getStudentemail() : '',
                        'studentphone' => $supplierInvoice->getStudentRegistration() ? $supplierInvoice->getStudentRegistration()->getStudent()->getStudentphone() : '',

                    ],
                ],
                'status' => $supplierInvoice->getStatus()

            ];
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

    public function taxes(SaleInvoiceItem $saleInvoiceItem){
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

    public function settlementStatus(SaleInvoice $supplierInvoice){
        $settlementStatus = '';
        $checkSettlements = $this->saleSettlementRepository->findBy(['invoice' => $supplierInvoice, 'isValidate' => true]);
        if ($checkSettlements){
            $totalSettlement = $this->saleSettlementRepository->sumSettlementValidatedAmountByInvoice($supplierInvoice)[0][1];
            //if ($supplierInvoice->getAmount() == $supplierInvoice->getAmountPaid() && $supplierInvoice->getBalance() == 0){
            if ($supplierInvoice->getTtc() == $totalSettlement){
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
