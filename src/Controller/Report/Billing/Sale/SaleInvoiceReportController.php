<?php

namespace App\Controller\Report\Billing\Sale;

use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Partner\CustomerRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class SaleInvoiceReportController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager, CustomerRepository $customerRepository, BranchRepository $branchRepository,
                                SaleInvoiceRepository $saleInvoiceRepository, SaleSettlementRepository $saleSettlementRepository,
                                private readonly TokenStorageInterface $tokenStorage)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->customerRepository = $customerRepository;
        $this->branchRepository = $branchRepository;
        $this->saleInvoiceRepository = $saleInvoiceRepository;
        $this->saleSettlementRepository = $saleSettlementRepository;
    }

    #[Route('/api/get/sale-invoice-history/report', name: 'app_get_sale_invoice_history_report')]
    public function getSaleInvoiceHistory(Request $request, SystemSettingsRepository $systemSettingsRepository): JsonResponse
    {
        $saleInvoiceData = json_decode($request->getContent(), true);

        $filteredInvoices = [];

        $dql = 'SELECT id, invoice_number, customer_id, invoice_at, amount, ttc, status FROM sale_invoice s WHERE ';

        if(isset($saleInvoiceData['invoiceAtStart']) && !isset($saleInvoiceData['invoiceAtEnd'])){
            $dql = $dql .' invoice_at LIKE '. '\''.$saleInvoiceData['invoiceAtStart'].'%\'';
        }

        if(isset($saleInvoiceData['invoiceAtStart']) && isset($saleInvoiceData['invoiceAtEnd'])){
            $dql = $dql .' invoice_at BETWEEN '. '\''.$saleInvoiceData['invoiceAtStart'].'\''. ' AND '. '\''.$saleInvoiceData['invoiceAtEnd'].'\'';
        }

        if (isset($saleInvoiceData['customer'])){
            $customerId = $this->customerRepository->find($this->getIdFromApiResourceId($saleInvoiceData['customer']));

            $dql = $dql .' AND customer_id = '. $customerId->getId();
        }

        /*if (isset($saleInvoiceData['branch'])){
            $branchId = $this->branchRepository->find($this->getIdFromApiResourceId($saleInvoiceData['branch']));

            $dql = $dql .' AND branch_id = '. $branchId->getId();
        }*/

        $systemSettings = $systemSettingsRepository->findOneBy([]);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {

                if (isset($saleInvoiceData['branch'])){
                    $branch = $this->branchRepository->find($this->getIdFromApiResourceId($saleInvoiceData['branch']));
                    $dql = $dql .' AND branch_id = '. $branch->getId();
                }
            }
            else{
                $branch = $this->getUser()->getBranch();
                $dql = $dql .' AND branch_id = '. $branch->getId();
            }
        }
        else{
            $branch = $this->getUser()->getBranch();
            $dql = $dql .' AND branch_id = '. $branch->getId();
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
            'customer' => $saleInvoice->getCustomer() ? $saleInvoice->getCustomer()->getName() : '',
            'invoiceNumber' => $saleInvoice->getInvoiceNumber(),
            'invoiceAt' => $saleInvoice->getInvoiceAt()->format('d-m-Y'),
            'isStandard' => $saleInvoice->isIsStandard(),
            'amount' => number_format($saleInvoice->getAmount(), 0, null, ' '),
            'amountPaid' => $saleInvoice->getAmountPaid(),
            'balance' => $saleInvoice->getBalance(),
            'shippingAddress' => $saleInvoice->getShippingAddress(),
            'paymentReference' => $saleInvoice->getPaymentReference(),
            'dateLine' => $saleInvoice->getDeadLine(),
            'status' => $saleInvoice->getStatus(),
            'virtualBalance' => $saleInvoice->getVirtualBalance(),
            'ttc' => number_format($saleInvoice->getTtc(), 0, null, ' '),
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