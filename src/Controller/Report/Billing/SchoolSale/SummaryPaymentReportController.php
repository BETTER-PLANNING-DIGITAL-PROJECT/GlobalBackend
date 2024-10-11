<?php

namespace App\Controller\Report\Billing\SchoolSale;

use App\Entity\Product\Item;
use App\Entity\School\Schooling\Configuration\Fee;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceFeeRepository;
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
class SummaryPaymentReportController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager, StudentRegistrationRepository $studentRegistrationRepository, SaleInvoiceItemRepository $saleInvoiceItemRepository,
                                SaleInvoiceRepository $saleInvoiceRepository, SaleSettlementRepository $saleSettlementRepository,  SchoolClassRepository $schoolClassRepository, SaleInvoiceFeeRepository $saleInvoiceFeeRepository,
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
        $this->saleInvoiceItemRepository = $saleInvoiceItemRepository;
		$this->saleInvoiceFeeRepository = $saleInvoiceFeeRepository;
    }

    #[Route('/api/get/summary-payment/report', name: 'app_get_summary_payment_report')]
    public function getSummaryPayment(Request $request): JsonResponse
    {
        $saleInvoiceData = json_decode($request->getContent(), true);

        $yearId = $this->yearRepository->find($this->getIdFromApiResourceId($saleInvoiceData['year']));
        $schoolId = $this->schoolReposotory->find($this->getIdFromApiResourceId($saleInvoiceData['school']));
        $classId = $this->schoolClassRepository->find($this->getIdFromApiResourceId($saleInvoiceData['class']));

        $filteredInvoices = [];

        $dql = 'SELECT id, sale_invoice_id, fee_id, quantity, name, amount, amount_ttc, customer_id, amount_paid, balance FROM sale_invoice_fee s
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

        $invoiceItemsByRegistration = [];

        foreach ($rows as $row) {
            $invoiceFee = $this->saleInvoiceFeeRepository->find($row['id']);
//            dd($invoiceItem);
            $registrationId = $invoiceFee->getStudentRegistration()->getId();

            $invoiceItemsByRegistration[$registrationId][] = [
                'name' => $invoiceFee->getName(),
                'amountPaid' => $invoiceFee->getAmountPaid(),
            ];
        }

        $studentRegistrations = $this->studentRegistrationRepository->findBy(['classe' => $classId]);

        $result = [];
        foreach ($studentRegistrations as $registration) {
            $registrationId = $registration->getId();
            $result[$registrationId] = [
                'name' => $registration->getStudent()->getFirstName() .' '. $registration->getStudent()->getName(),
                'invoiceItems' => $invoiceItemsByRegistration[$registrationId] ?? [],
            ];
        }

//        dd($result);

        $entityManager = $this->entityManager;
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
            ->select('i')
            ->from(Fee::class, 'i')
            //->join('i.fee', 'f')
            ->where('i.class = :classId')
            ->setParameter('classId', $classId);

        $items = $queryBuilder->getQuery()->getResult();

        usort($items, function ($a, $b) {
            return $a->getPosition() <=> $b->getPosition();
        });

//        dd($items);

        return $this->json(['data' => $items, 'data1' => $result]);
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






//#[Route('/api/get/fee-payment/report', name: 'app_get_fee_payment_report')]
//public function getFeePayment(Request $request): JsonResponse
//{
//    $saleInvoiceData = json_decode($request->getContent(), true);
//    $yearId = $this->yearRepository->find($this->getIdFromApiResourceId($saleInvoiceData['year']));
//    $schoolId = $this->schoolReposotory->find($this->getIdFromApiResourceId($saleInvoiceData['school']));
//    $classId = $this->schoolClassRepository->find($this->getIdFromApiResourceId($saleInvoiceData['class']));
//
//    $filteredInvoices = [];
//    $dql = 'SELECT id, sale_invoice_id, item_id, quantity, name, amount, amount_ttc, customer_id, amount_paid, balance FROM sale_invoice_item s
//           WHERE year_id = '.$yearId->getId(). ' AND school_id = '.$schoolId->getId(). ' AND class_id = '.$classId->getId();
//
//    if (isset($saleInvoiceData['class'])){
//        $classId = $this->schoolClassRepository->find($this->getIdFromApiResourceId($saleInvoiceData['class']));
//        $dql = $dql .' AND class_id = '. $classId->getId(); }
//    if (isset($saleInvoiceData['studentRegistration'])){
//        $studentRegistrationId = $this->studentRegistrationRepository->find($this->getIdFromApiResourceId($saleInvoiceData['studentRegistration']));
//
//        $dql = $dql .' AND student_registration_id = '. $studentRegistrationId->getId(); }
//
//    $conn = $this->entityManager->getConnection();
//    $resultSet = $conn->executeQuery($dql);
//    $rows = $resultSet->fetchAllAssociative();
//    // dd($rows);
//   $groupedInvoiceItems = [];
//   $invoiceItemsByRegistration = [];
//   foreach ($rows as $row) {
//       $invoiceItem = $this->saleInvoiceItemRepository->find($row['id']);
//       $registrationId = $invoiceItem->getStudentRegistration()->getId(); }
//   dd($invoiceItemsByRegistration);
//   $entityManager = $this->entityManager;
//   $queryBuilder = $entityManager->createQueryBuilder();
//   $queryBuilder ->select('i') ->from(Item::class, 'i') ->join('i.fee', 'f') ->where('f.class = :classId') ->setParameter('classId', $classId); $items = $queryBuilder->getQuery()->getResult(); // Sort items by position if needed usort($items, function ($a, $b) { return $a->getPosition() <=> $b->getPosition(); }); // dd($items); return $this->json($items); }