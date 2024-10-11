<?php

namespace App\Controller\Billing\Sale\School\Return;

use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSchoolSaleReturnInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly SaleSettlementRepository $settlementRepository
                                )
    {
    }

    public function __invoke(SaleReturnInvoiceItemRepository $saleReturnInvoiceItemRepository,
                             SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                             Request $request): JsonResponse
    {

        $invoices = $saleReturnInvoiceRepository->findBy(['isStandard' => false], ['id' => 'DESC']);

        $saleReturnInvoices = [];

        foreach ($invoices as $invoice){
            $saleReturnInvoices[] = [
                'id' => $invoice->getId(),
                '@id' => '/api/get/sale-return-invoice/'.$invoice->getId(),
                'type' => 'SaleReturnInvoice',
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

    public function settlementStatus(SaleReturnInvoice $saleReturnInvoice){
        $checkSettlements = $this->settlementRepository->findBy(['saleReturnInvoice' => $saleReturnInvoice, 'isValidate' => true]);
        if ($checkSettlements){
            $totalSettlement = $this->settlementRepository->sumReturnSettlementValidatedAmountByInvoice($saleReturnInvoice)[0][1];
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
