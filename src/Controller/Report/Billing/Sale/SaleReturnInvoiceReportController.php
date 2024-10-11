<?php

namespace App\Controller\Report\Billing\Sale;

use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
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
class SaleReturnInvoiceReportController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager, CustomerRepository $customerRepository, BranchRepository $branchRepository,
                                SaleReturnInvoiceRepository $saleReturnInvoiceRepository, SaleSettlementRepository $saleSettlementRepository,
                                private readonly TokenStorageInterface $tokenStorage)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->customerRepository = $customerRepository;
        $this->branchRepository = $branchRepository;
        $this->saleReturnInvoiceRepository = $saleReturnInvoiceRepository;
        $this->saleSettlementRepository = $saleSettlementRepository;
    }

    #[Route('/api/get/sale-return-invoice-history/report', name: 'app_get_sale_return_invoice_history_report')]
    public function getSaleReturnInvoiceHistory(Request $request, SystemSettingsRepository $systemSettingsRepository): JsonResponse
    {
        $saleInvoiceData = json_decode($request->getContent(), true);

        $filteredInvoices = [];

        $dql = 'SELECT id, invoice_number, customer_id, invoice_at, amount, ttc, status FROM sale_return_invoice s WHERE ';

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

        /*if (isset($saleInvoiceData['branch'])){
            $branchId = $this->branchRepository->find($this->getIdFromApiResourceId($saleInvoiceData['branch']));

            $dql = $dql .' AND branch_id = '. $branchId->getId();
        }*/

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
                $saleReturnInvoice = $this->saleReturnInvoiceRepository->find($row['id']);
                $settlementStatus = $this->settlementStatus($saleReturnInvoice);
                if ($settlementStatus == $status1) {
                    $filteredInvoices[] = $this->bindSaleReturnInvoice($saleReturnInvoice);
                }

            }

        }else{
            foreach ($rows as $row) {
                $invoice = $this->saleReturnInvoiceRepository->find($row['id']);
                $filteredInvoices[] = $this->bindSaleReturnInvoice($invoice);
            }
        }

        return $this->json($filteredInvoices);
    }

    public function bindSaleReturnInvoice(SaleReturnInvoice $saleReturnInvoice): array
    {
        return [
            'id' => $saleReturnInvoice->getId(),
            'customer' => $saleReturnInvoice->getCustomer() ? $saleReturnInvoice->getCustomer()->getName() : '',
            'invoiceNumber' => $saleReturnInvoice->getInvoiceNumber(),
            'invoiceAt' => $saleReturnInvoice->getInvoiceAt()->format('d-m-Y'),
            'isStandard' => $saleReturnInvoice->isIsStandard(),
            'amount' => number_format($saleReturnInvoice->getAmount(), 0, null, ' '),
            'amountPaid' => $saleReturnInvoice->getAmountPaid(),
            'balance' => $saleReturnInvoice->getBalance(),
            'shippingAddress' => $saleReturnInvoice->getShippingAddress(),
            'paymentReference' => $saleReturnInvoice->getPaymentReference(),
            'dateLine' => $saleReturnInvoice->getDeadLine(),
            'status' => $saleReturnInvoice->getStatus(),
            'virtualBalance' => $saleReturnInvoice->getVirtualBalance(),
            'ttc' => number_format($saleReturnInvoice->getTtc(), 0, null, ' '),
            'settlementStatus' => $this->settlementStatus($saleReturnInvoice),
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

    public function settlementStatus(SaleReturnInvoice $saleReturnInvoice)
    {
        $checkSettlements = $this->saleSettlementRepository->findBy(['saleReturnInvoice' => $saleReturnInvoice, 'isValidate' => true]);
        if ($checkSettlements){
            $totalSettlement = $this->saleSettlementRepository->sumReturnSettlementValidatedAmountByInvoice($saleReturnInvoice)[0][1];
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

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
    }
}