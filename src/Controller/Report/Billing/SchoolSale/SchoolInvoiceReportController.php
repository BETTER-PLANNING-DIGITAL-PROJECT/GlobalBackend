<?php

namespace App\Controller\Report\Billing\SchoolSale;

use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\Security\Session\YearRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class SchoolInvoiceReportController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager, StudentRegistrationRepository $studentRegistrationRepository,
                                SaleInvoiceRepository $saleInvoiceRepository, SaleSettlementRepository $saleSettlementRepository, SchoolClassRepository $schoolClassRepository,
                                YearRepository $yearRepository, SchoolRepository $schoolRepository,
                                private readonly TokenStorageInterface $tokenStorage)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->studentRegistrationRepository = $studentRegistrationRepository;
        $this->saleInvoiceRepository = $saleInvoiceRepository;
        $this->saleSettlementRepository = $saleSettlementRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->yearRepository = $yearRepository;
        $this->schoolReposotory = $schoolRepository;
    }

    #[Route('/api/get/sale-invoice/report', name: 'app_get_sale_invoice_report')]
    public function getSchoolInvoiceByStudentRegistration(Request $request): JsonResponse
    {
        $saleInvoiceData = json_decode($request->getContent(), true);

        $yearId = $this->yearRepository->find($this->getIdFromApiResourceId($saleInvoiceData['year']));
        $schoolId = $this->schoolReposotory->find($this->getIdFromApiResourceId($saleInvoiceData['school']));
        $classId = $this->schoolClassRepository->find($this->getIdFromApiResourceId($saleInvoiceData['class']));

        $filteredInvoices = [];

        $dql = 'SELECT id, invoice_number, customer_id, invoice_at, amount, ttc, status FROM sale_invoice s 
                WHERE year_id = '.$yearId->getId(). ' AND school_id = '.$schoolId->getId(). ' AND class_id = '.$classId->getId();

        if (isset($saleInvoiceData['class'])){
            $classId = $this->schoolClassRepository->find($this->getIdFromApiResourceId($saleInvoiceData['class']));

            $dql = $dql .' AND class_id = '. $classId->getId();
        }

        if (isset($saleInvoiceData['studentRegistration'])){
            $studentRegistrationId = $this->studentRegistrationRepository->find($this->getIdFromApiResourceId($saleInvoiceData['studentRegistration']));

            $dql = $dql .' AND student_registration_id = '. $studentRegistrationId->getId();
        }

        if(isset($saleInvoiceData['invoiceAtStart']) && !isset($saleInvoiceData['invoiceAtEnd'])){
            $dql = $dql .' AND invoice_at LIKE '. '\''.$saleInvoiceData['invoiceAtStart'].'%\'';
        }

        if(isset($saleInvoiceData['invoiceAtEnd']) && !isset($saleInvoiceData['invoiceAtStart'])){
            $dql = $dql .' AND invoice_at LIKE '. '\''.$saleInvoiceData['invoiceAtEnd'].'%\'';
        }

        if(isset($saleInvoiceData['invoiceAtStart']) && isset($saleInvoiceData['invoiceAtEnd'])){
            $dql = $dql .' AND invoice_at BETWEEN '. '\''.$saleInvoiceData['invoiceAtStart'].'\''. ' AND '. '\''.$saleInvoiceData['invoiceAtEnd'].'\'';
        }

        $conn = $this->entityManager->getConnection();
        $resultSet = $conn->executeQuery($dql);
        $rows = $resultSet->fetchAllAssociative();

        if (isset($saleInvoiceData['status'])){

            $status1 = '';

            if($saleInvoiceData['status'] == 'completelyPaid'){
                $status1 = 'Complete Paid';
            }
            elseif ($saleInvoiceData['status'] == 'partiallyPaid') {
                $status1 = 'Partial Paid';
            } elseif ($saleInvoiceData['status'] == 'notPaid'){
                $status1 = 'Not paid';
            }

            foreach ($rows as $row) {
                $invoice = $this->saleInvoiceRepository->find($row['id']);
                $settlementStatus = $this->settlementStatus($invoice);
                if ($settlementStatus == $status1) {
                    $filteredInvoices[] = $this->bindSchoolInvoice($invoice);
                }

            }

        }else{
            foreach ($rows as $row) {
                $invoice = $this->saleInvoiceRepository->find($row['id']);
                $filteredInvoices[] = $this->bindSchoolInvoice($invoice);
            }
        }

        return $this->json($filteredInvoices);
    }

    public function bindSchoolInvoice(SaleInvoice $saleInvoice): array
    {
        return [
            'id' => $saleInvoice->getId(),
            'firstName' => $saleInvoice->getStudentRegistration()->getStudent() ? $saleInvoice->getStudentRegistration()->getStudent()->getFirstName() : '',
            'name' => $saleInvoice->getStudentRegistration()->getStudent() ? $saleInvoice->getStudentRegistration()->getStudent()->getName() : '',
            'class' => $saleInvoice->getClass() ? $saleInvoice->getClass()->getCode() : '',
            'school' => $saleInvoice->getSchool() ? $saleInvoice->getSchool()->getName() : '',
            'customer' => $saleInvoice->getCustomer() ? $saleInvoice->getCustomer()->getName() : '',
            'invoiceNumber' => $saleInvoice->getInvoiceNumber(),
            'invoiceAt' => $saleInvoice->getInvoiceAt()->format('d-m-Y'),
            'isStandard' => $saleInvoice->isIsStandard(),
            'amount' => $saleInvoice->getAmount(),
            'amountPaid' => $saleInvoice->getAmountPaid(),
            'balance' => $saleInvoice->getBalance(),
            'shippingAddress' => $saleInvoice->getShippingAddress(),
            'paymentReference' => $saleInvoice->getPaymentReference(),
            'dateLine' => $saleInvoice->getDeadLine(),
            'status' => $saleInvoice->getStatus(),
            'virtualBalance' => $saleInvoice->getVirtualBalance(),
            'ttc' => $saleInvoice->getTtc(),
            'settlementStatus' => $this->settlementStatus($saleInvoice),
        ];
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

    public function settlementStatus(SaleInvoice $invoice)
    {
        $checkSettlements = $this->saleSettlementRepository->findBy(['invoice' => $invoice, 'isValidate' => true]);
        if ($checkSettlements){
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

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
    }
}