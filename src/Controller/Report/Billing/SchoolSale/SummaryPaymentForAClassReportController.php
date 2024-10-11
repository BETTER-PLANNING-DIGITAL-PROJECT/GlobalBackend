<?php

namespace App\Controller\Report\Billing\SchoolSale;

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
class SummaryPaymentForAClassReportController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager, StudentRegistrationRepository $studentRegistrationRepository,
                                SaleInvoiceRepository $saleInvoiceRepository, SaleSettlementRepository $saleSettlementRepository,  SchoolClassRepository $schoolClassRepository,
                                private readonly TokenStorageInterface $tokenStorage,  YearRepository $yearRepository, SchoolRepository $schoolRepository)
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

    #[Route('/api/get/summary-payment-for-a-class/report', name: 'app_get_summary_payment_for_a_class_report')]
    public function getSummaryPaymentForAClass(Request $request): JsonResponse
    {
        $saleInvoiceData = json_decode($request->getContent(), true);

        $yearId = $this->yearRepository->find($this->getIdFromApiResourceId($saleInvoiceData['year']));
        $schoolId = $this->schoolReposotory->find($this->getIdFromApiResourceId($saleInvoiceData['school']));
        $classId = $this->schoolClassRepository->find($this->getIdFromApiResourceId($saleInvoiceData['class']));

        $filteredInvoices = [];

        $dql = 'SELECT id, invoice_id, sale_return_invoice_id, customer_id, amount_pay, amount_rest, student_registration_id FROM sale_settlement s 
            WHERE year_id = '.$yearId->getId(). ' AND school_id = '.$schoolId->getId(). ' AND class_id = '.$classId->getId();


        if (isset($saleInvoiceData['class'])){
            $classId = $this->schoolClassRepository->find($this->getIdFromApiResourceId($saleInvoiceData['class']));

            $dql = $dql .' AND class_id = '. $classId->getId();
        }

        if (isset($saleInvoiceData['studentRegistration'])){
            $studentRegistrationId = $this->studentRegistrationRepository->find($this->getIdFromApiResourceId($saleInvoiceData['studentRegistration']));

            $dql = $dql .' AND student_registration_id = '. $studentRegistrationId->getId();
        }

        $conn = $this->entityManager->getConnection();
        $resultSet = $conn->executeQuery($dql);
        $rows = $resultSet->fetchAllAssociative();

        $groupedSettlements = [];
        foreach ($rows as $row) {
            $studentRegistrationId = $row['student_registration_id'];
            if (!isset($groupedSettlements[$studentRegistrationId])) {
                $groupedSettlements[$studentRegistrationId] = [
                    'amountPay' => 0,
                    'amountRest' => 0,
                ];
            }
            $groupedSettlements[$studentRegistrationId]['amountPay'] += $row['amount_pay'];
            $groupedSettlements[$studentRegistrationId]['amountRest'] += $row['amount_rest'];
        }

        // Create response based on grouped settlements
        foreach ($groupedSettlements as $studentRegistrationId => $amounts) {
            $studentRegistration = $this->studentRegistrationRepository->find($studentRegistrationId);
            $studentName = $studentRegistration ? $studentRegistration->getStudent()->getFirstName() .''. $studentRegistration->getStudent()->getName() : '';

            $filteredInvoices[] = [
                'studentName' => $studentName,
                'amountPay' => $amounts['amountPay'],
                'amountRest' => $amounts['amountRest'],
                'totalAmount' => $amounts['amountRest'] + $amounts['amountPay'],
            ];
        }

        return $this->json($filteredInvoices);
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

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
    }
}



