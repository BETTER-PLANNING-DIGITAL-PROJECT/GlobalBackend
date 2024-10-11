<?php

namespace App\Controller\Report\Billing\SchoolSale;

use App\Entity\Billing\Sale\SaleSettlement;
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
class SaleSettlementReportController extends AbstractController
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

    #[Route('/api/get/sale-settlement/report', name: 'app_get_sale_settlement_report')]
    public function getSchoolInvoiceByStudentRegistration(Request $request): JsonResponse
    {
        $saleInvoiceData = json_decode($request->getContent(), true);

        $yearId = $this->yearRepository->find($this->getIdFromApiResourceId($saleInvoiceData['year']));
        $schoolId = $this->schoolReposotory->find($this->getIdFromApiResourceId($saleInvoiceData['school']));
        $classId = $this->schoolClassRepository->find($this->getIdFromApiResourceId($saleInvoiceData['class']));

        $filteredInvoices = [];

        $dql = 'SELECT id, invoice_id, sale_return_invoice_id, customer_id, supplier_id, amount_pay, amount_rest FROM sale_settlement s 
                WHERE year_id = '.$yearId->getId(). ' AND school_id = '.$schoolId->getId(). ' AND class_id = '.$classId->getId();


        if (isset($saleInvoiceData['class'])){
            $classId = $this->schoolClassRepository->find($this->getIdFromApiResourceId($saleInvoiceData['class']));

            $dql = $dql .' AND class_id = '. $classId->getId();
        }

        if (isset($saleInvoiceData['studentRegistration'])){
            $studentRegistrationId = $this->studentRegistrationRepository->find($this->getIdFromApiResourceId($saleInvoiceData['studentRegistration']));

            $dql = $dql .' AND student_registration_id = '. $studentRegistrationId->getId();
        }

        if(isset($saleInvoiceData['settleAtStart']) && !isset($saleInvoiceData['settleAtEnd'])){
            $dql = $dql .' AND settle_at LIKE '. '\''.$saleInvoiceData['settleAtStart'].'%\'';
        }

        if(isset($saleInvoiceData['settleAtEnd']) && !isset($saleInvoiceData['settleAtStart'])){
            $dql = $dql .' AND settle_at LIKE '. '\''.$saleInvoiceData['settleAtEnd'].'%\'';
        }

        if(isset($saleInvoiceData['settleAtStart']) && isset($saleInvoiceData['settleAtEnd'])){
            $dql = $dql .' AND settle_at BETWEEN '. '\''.$saleInvoiceData['settleAtStart'].'\''. ' AND '. '\''.$saleInvoiceData['settleAtEnd'].'\'';
        }

        $conn = $this->entityManager->getConnection();
        $resultSet = $conn->executeQuery($dql);
        $rows = $resultSet->fetchAllAssociative();

            foreach ($rows as $row) {
                $saleSettlement = $this->saleSettlementRepository->find($row['id']);
                $filteredInvoices[] = $this->bindSaleSettlement($saleSettlement);
            }

        return $this->json($filteredInvoices);
    }

    public function bindSaleSettlement(SaleSettlement $saleSettlement): array
    {
        return [
            'id' => $saleSettlement->getId(),
            'firstName' => $saleSettlement->getStudentRegistration()->getStudent() ? $saleSettlement->getStudentRegistration()->getStudent()->getFirstName() : '',
            'name' => $saleSettlement->getStudentRegistration()->getStudent() ? $saleSettlement->getStudentRegistration()->getStudent()->getName() : '',
            'invoice' => $saleSettlement->getInvoice() ? $saleSettlement->getInvoice()->getId() : '',
            'saleReturnInvoice' => $saleSettlement->getSaleReturnInvoice() ? $saleSettlement->getSaleReturnInvoice()->getId() :  '',
            'customer' => $saleSettlement->getCustomer() ? $saleSettlement->getCustomer()->getName() :  '',
            'supplier' => $saleSettlement->getSupplier() ? $saleSettlement->getSupplier()->getName() :  '',
            'reference' => $saleSettlement->getReference(),
            'totalAmount' => $saleSettlement->getAmountPay() + $saleSettlement->getAmountRest(),
            'amountPay' => $saleSettlement->getAmountPay(),
            'amountRest' => $saleSettlement->getAmountRest(),
            'settleAt' => $saleSettlement->getSettleAt()->format('Y-m-d'),
            'bank' => $saleSettlement->getBank() ? $saleSettlement->getBank()->getName() :  '',
            'cashDesk' => $saleSettlement->getCashDesk() ? $saleSettlement->getCashDesk()->getCode() :  '',
            'note' => $saleSettlement->getNote(),
            'paymentMethod' => $saleSettlement->getPaymentMethod() ? $saleSettlement->getPaymentMethod()->getName() :  '',
            'paymentGateway' => $saleSettlement->getPaymentGateway() ? $saleSettlement->getPaymentGateway()->getName() :  '',
            'bankAccount' => $saleSettlement->getBankAccount() ? $saleSettlement->getBankAccount()->getAccountName() :  '',
            'isValidated' => $saleSettlement->isIsValidate(),
            'validatedAt' => $saleSettlement->getValidateAt()->format('Y-m-d'),
            'validatedBy' => $saleSettlement->getValidateBy(),
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

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
    }
}