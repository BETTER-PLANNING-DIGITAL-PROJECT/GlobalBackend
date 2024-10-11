<?php

namespace App\Controller\Report\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseSettlement;
use App\Entity\Security\User;
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
class PurchaseInvoiceSettlementReportController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager,
                               PurchaseSettlementRepository $purchaseSettlementRepository,
                                private readonly TokenStorageInterface $tokenStorage,  BranchRepository $branchRepository, SupplierRepository $supplierRepository)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->purchaseSettlementRepository = $purchaseSettlementRepository;
        $this->branchRepository = $branchRepository;
        $this->supplierRepository = $supplierRepository;
    }

    #[Route('/api/get/purchase-invoice-settlement/report', name: 'app_get_purchase_invoice_settlement_history_report')]
    public function getPurchaseSettlementHistory(Request $request, SystemSettingsRepository $systemSettingsRepository): JsonResponse
    {
        $purchaseInvoiceData = json_decode($request->getContent(), true);

        $filteredInvoices = [];

        $dql = 'SELECT id, invoice_id, supplier_id, amount_pay, amount_rest FROM purchase_settlement s WHERE purchase_return_invoice_id is null AND is_validate = 1 ';

        if(isset($purchaseInvoiceData['settleAtStart']) && !isset($purchaseInvoiceData['settleAtEnd'])){
            $dql = $dql .' AND settle_at LIKE '. '\''.$purchaseInvoiceData['settleAtStart'].'%\'';
        }

        if(isset($purchaseInvoiceData['settleAtStart']) && isset($purchaseInvoiceData['settleAtEnd'])){
            $dql = $dql .' AND settle_at BETWEEN '. '\''.$purchaseInvoiceData['settleAtStart'].'\''. ' AND '. '\''.$purchaseInvoiceData['settleAtEnd'].'\'';
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

            foreach ($rows as $row) {
                $purchaseSettlement = $this->purchaseSettlementRepository->find($row['id']);
                $filteredInvoices[] = $this->bindPurchaseSettlement($purchaseSettlement);
            }

        return $this->json($filteredInvoices);
    }

    public function bindPurchaseSettlement(PurchaseSettlement $purchaseSettlement): array
    {
        return [
            'id' => $purchaseSettlement->getId(),
            'invoice' => $purchaseSettlement->getInvoice() ? $purchaseSettlement->getInvoice()->getId() : '',
            'supplier' => $purchaseSettlement->getSupplier() ? $purchaseSettlement->getSupplier()->getName() :  '',
            'reference' => $purchaseSettlement->getReference(),
            'amountPay' => number_format($purchaseSettlement->getAmountPay(), 0, null, ' '),
            'settleAt' => $purchaseSettlement->getSettleAt()->format('Y-m-d'),
            'bank' => $purchaseSettlement->getBank() ? $purchaseSettlement->getBank()->getName() :  '',
            'cashDesk' => $purchaseSettlement->getCashDesk() ? $purchaseSettlement->getCashDesk()->getCode() :  '',
            'note' => $purchaseSettlement->getNote(),
            'paymentMethod' => $purchaseSettlement->getPaymentMethod() ? $purchaseSettlement->getPaymentMethod()->getName() :  '',
            'bankAccount' => $purchaseSettlement->getBankAccount() ? $purchaseSettlement->getBankAccount()->getAccountName() :  '',
            'isValidated' => $purchaseSettlement->isIsValidate(),
            'validatedAt' => $purchaseSettlement->getValidateAt() ? $purchaseSettlement->getValidateAt()->format('Y-m-d') : '',
            'validatedBy' => $purchaseSettlement->getValidateBy(),
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