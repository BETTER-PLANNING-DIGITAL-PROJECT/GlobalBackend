<?php

namespace App\Controller\Billing\Sale\Invoice;

use App\Entity\Billing\Sale\SaleInvoice;
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
class GetSchoolSaleInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly SaleSettlementRepository $settlementRepository
                                )
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             Request $request): JsonResponse
    {

        $invoices = $saleInvoiceRepository->findBy(['isStandard' => false], ['id' => 'DESC']);

        $saleInvoices = [];

        foreach ($invoices as $invoice){
            $saleInvoices[] = [
                'id' => $invoice->getId(),
                '@id' => '/api/get/sale-invoice/'.$invoice->getId(),
                'type' => 'SaleInvoice',
                'balance' => $invoice->getBalance(),

                'class' => [
                    'id' => $invoice->getClass() ? $invoice->getClass()->getId() : '',
                    //'@id' => '/api/get/class/'. $invoice->getClass() ? $invoice->getClass()->getId() : '',
                    'type' => 'SchoolClass',
                    'code' => $invoice->getClass() ? $invoice->getClass()->getCode() : '',
                ],

                'invoiceAt' => $invoice->getInvoiceAt() ? $invoice->getInvoiceAt()->format('Y-m-d') : '',
                'invoiceNumber' => $invoice->getInvoiceNumber(),
                'paymentReference' => $invoice->getPaymentReference(),

                'amount' => $invoice->getAmount(),
                'amountTtc' => $invoice->getTtc(),
                'amountPaid' => $invoice->getAmountPaid(),
                'settlementStatus' => $this->settlementStatus($invoice),

                'school' => [
                    'id' => $invoice->getSchool() ? $invoice->getSchool()->getId() : '',
                    //'@id' => '/api/get/school/item/'. $invoice->getSchool()->getId(),
                    'type' => 'School',
                    'name' => $invoice->getSchool() ? $invoice->getSchool()->getName() : '',
                ],

                'customer' => $invoice->getCustomer(),

                'studentRegistration' => [
                    'id' => $invoice->getStudentRegistration() ? $invoice->getStudentRegistration()->getId() : '',
                    //'@id' => '/api/get/student/registration/'. $invoice->getStudentRegistration()->getId(),
                    'type' => 'StudentRegistration',
                    'center' => $invoice->getStudentRegistration() ? $invoice->getStudentRegistration()->getCenter() : '',
                    'student' => [
                        'id' => $invoice->getStudentRegistration() ? $invoice->getStudentRegistration()->getStudent()->getId() : '',
                        //'@id' => '/api/students/'. $invoice->getStudentRegistration()->getStudent()->getId(),
                        'type' => 'Student',
                        'name' => $invoice->getStudentRegistration() ? $invoice->getStudentRegistration()->getStudent()->getName() : '',
                        'studentemail' => $invoice->getStudentRegistration() ? $invoice->getStudentRegistration()->getStudent()->getStudentemail() : '',
                        'studentphone' => $invoice->getStudentRegistration() ? $invoice->getStudentRegistration()->getStudent()->getStudentphone() : '',

                    ],
                ],
                'status' => $invoice->getStatus()

            ];
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

    public function settlementStatus(SaleInvoice $invoice){
        $settlementStatus = '';
        $checkSettlements = $this->settlementRepository->findBy(['invoice' => $invoice, 'isValidate' => true]);
        if ($checkSettlements){
            $totalSettlement = $this->settlementRepository->sumSettlementValidatedAmountByInvoice($invoice)[0][1];
            //if ($invoice->getAmount() == $invoice->getAmountPaid() && $invoice->getBalance() == 0){
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
