<?php

namespace App\Controller\Report\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Billing\Purchase\PurchaseSettlementRepository;
use App\Repository\Partner\SupplierRepository;
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
class PurchaseInvoiceReportController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager, SupplierRepository $supplierRepository, BranchRepository $branchRepository,
                                PurchaseInvoiceRepository $purchaseInvoiceRepository, PurchaseSettlementRepository $purchaseSettlementRepository,
                                private readonly TokenStorageInterface $tokenStorage)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->supplierRepository = $supplierRepository;
        $this->branchRepository = $branchRepository;
        $this->purchaseInvoiceRepository = $purchaseInvoiceRepository;
        $this->purchaseSettlementRepository = $purchaseSettlementRepository;
    }

    #[Route('/api/get/purchase-invoice-history/report', name: 'app_get_purchase_invoice_history_report')]
    public function getPurchaseInvoiceHistory(Request $request, SystemSettingsRepository $systemSettingsRepository): JsonResponse
    {
        $purchaseInvoiceData = json_decode($request->getContent(), true);

        $filteredInvoices = [];

        $dql = 'SELECT id, invoice_number, supplier_id, invoice_at, amount, ttc, status FROM purchase_invoice s WHERE ';

        if(isset($purchaseInvoiceData['invoiceAtStart']) && !isset($purchaseInvoiceData['invoiceAtEnd'])){
            $dql = $dql .' invoice_at LIKE '. '\''.$purchaseInvoiceData['invoiceAtStart'].'%\'';
        }

        if(isset($purchaseInvoiceData['invoiceAtStart']) && isset($purchaseInvoiceData['invoiceAtEnd'])){
            $dql = $dql .' invoice_at BETWEEN '. '\''.$purchaseInvoiceData['invoiceAtStart'].'\''. ' AND '. '\''.$purchaseInvoiceData['invoiceAtEnd'].'\'';
        }

        if (isset($purchaseInvoiceData['supplier'])){
            $supplierId = $this->supplierRepository->find($this->getIdFromApiResourceId($purchaseInvoiceData['supplier']));

            $dql = $dql .' AND supplier_id = '. $supplierId->getId();
        }

        $systemSettings = $systemSettingsRepository->findOneBy([]);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {

                if (isset($purchaseInvoiceData['branch'])){
                    $branch = $this->branchRepository->find($this->getIdFromApiResourceId($purchaseInvoiceData['branch']));
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

        if (isset($purchaseInvoiceData['status'])){

            $status1 = '';

            if($purchaseInvoiceData['status'] == 'completelyPaid'){
                $status1 = 'Complete Paid';
            }
            elseif ($purchaseInvoiceData['status'] == 'partiallyPaid') {
                $status1 = 'Partial Paid';
            } elseif ($purchaseInvoiceData['status'] == 'notPaid'){
                $status1 = 'Not paid';
            }

            foreach ($rows as $row) {
                $invoice = $this->purchaseInvoiceRepository->find($row['id']);
                $settlementStatus = $this->settlementStatus($invoice);
                if ($settlementStatus == $status1) {
                    $filteredInvoices[] = $this->bindPurchaseInvoice($invoice);
                }

            }

        }else{
            foreach ($rows as $row) {
                $invoice = $this->purchaseInvoiceRepository->find($row['id']);
                $filteredInvoices[] = $this->bindPurchaseInvoice($invoice);
            }
        }

        return $this->json($filteredInvoices);
    }

    public function bindPurchaseInvoice(PurchaseInvoice $purchaseInvoice): array
    {
        return [
            'id' => $purchaseInvoice->getId(),
            'supplier' => $purchaseInvoice->getSupplier() ? $purchaseInvoice->getSupplier()->getName() : '',
            'invoiceNumber' => $purchaseInvoice->getInvoiceNumber(),
            'invoiceAt' => $purchaseInvoice->getInvoiceAt()->format('d-m-Y'),
            'amount' => number_format($purchaseInvoice->getAmount(), 0, null, ' '),
            'amountPaid' => $purchaseInvoice->getAmountPaid(),
            'balance' => $purchaseInvoice->getBalance(),
            'shippingAddress' => $purchaseInvoice->getShippingAddress(),
            'paymentReference' => $purchaseInvoice->getPaymentReference(),
            'dateLine' => $purchaseInvoice->getDeadLine(),
            'status' => $purchaseInvoice->getStatus(),
            'virtualBalance' => $purchaseInvoice->getVirtualBalance(),
            'ttc' => number_format($purchaseInvoice->getTtc(), 0, null, ' '),
            'settlementStatus' => $this->settlementStatus($purchaseInvoice),
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

    public function settlementStatus(PurchaseInvoice $purchaseInvoice): string
    {
        $checkSettlements = $this->purchaseSettlementRepository->findBy(['invoice' => $purchaseInvoice, 'isValidate' => true]);
        if ($checkSettlements){
            $totalSettlement = $this->purchaseSettlementRepository->sumSettlementValidatedAmountByPurchaseInvoice($purchaseInvoice)[0][1];
            if ($purchaseInvoice->getTtc() == $totalSettlement){
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