<?php

namespace App\Controller\Report\Billing\Sale;

use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Security\User;
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
class SaleSettlementReportController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager,
                               SaleSettlementRepository $saleSettlementRepository,
                                private readonly TokenStorageInterface $tokenStorage,  BranchRepository $branchRepository, CustomerRepository $customerRepository)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->saleSettlementRepository = $saleSettlementRepository;
        $this->branchRepository = $branchRepository;
        $this->customerRepository = $customerRepository;
    }

    #[Route('/api/get/sale-settlement/report', name: 'app_get_sale_settlement_history_report')]
    public function getSaleSettlementHistory(Request $request, SystemSettingsRepository $systemSettingsRepository): JsonResponse
    {
        $saleInvoiceData = json_decode($request->getContent(), true);

        $filteredInvoices = [];

        $dql = 'SELECT id, invoice_id, sale_return_invoice_id, customer_id, amount_pay, amount_rest FROM sale_settlement s WHERE is_validate = 1 ';

        if(isset($saleInvoiceData['settleAtStart']) && !isset($saleInvoiceData['settleAtEnd'])){
            $dql = $dql .' AND settle_at LIKE '. '\''.$saleInvoiceData['settleAtStart'].'%\'';
        }

        /*if(isset($saleInvoiceData['settleAtEnd']) && !isset($saleInvoiceData['settleAtStart'])){
            $dql = $dql .' AND settle_at LIKE '. '\''.$saleInvoiceData['settleAtEnd'].'%\'';
        }*/

        if(isset($saleInvoiceData['settleAtStart']) && isset($saleInvoiceData['settleAtEnd'])){
            $dql = $dql .' AND settle_at BETWEEN '. '\''.$saleInvoiceData['settleAtStart'].'\''. ' AND '. '\''.$saleInvoiceData['settleAtEnd'].'\'';
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
            'invoice' => $saleSettlement->getInvoice() ? $saleSettlement->getInvoice()->getId() : '',
            'returnInvoice' => $saleSettlement->getSaleReturnInvoice() ? $saleSettlement->getSaleReturnInvoice()->getId() :  '',
            'customer' => $saleSettlement->getCustomer() ? $saleSettlement->getCustomer()->getName() :  '',
            'reference' => $saleSettlement->getReference(),
            // 'totalAmount' => $saleSettlement->getAmountPay() + $saleSettlement->getAmountRest(),
            'amountPay' => number_format($saleSettlement->getAmountPay(), 0, null, ' '),
            // 'totalInvoice' => number_format($totalInvoice, 0, null, ' '),
            // 'totalReturn' => number_format($totalReturn, 0, null, ' '),
            // 'amountRest' => $saleSettlement->getAmountRest(),
            'settleAt' => $saleSettlement->getSettleAt()->format('Y-m-d'),
            // 'bank' => $saleSettlement->getBank() ? $saleSettlement->getBank()->getName() :  '',
            // 'cashDesk' => $saleSettlement->getCashDesk() ? $saleSettlement->getCashDesk()->getCode() :  '',
            'note' => $saleSettlement->getNote(),
            'branch' => $saleSettlement->getBranch()->getCode(),
            'paymentMethod' => $saleSettlement->getPaymentMethod() ? $saleSettlement->getPaymentMethod()->getName() :  '',
            // 'bankAccount' => $saleSettlement->getBankAccount() ? $saleSettlement->getBankAccount()->getAccountName() :  '',
            // 'isValidated' => $saleSettlement->isIsValidate(),
            // 'validatedAt' => $saleSettlement->getValidateAt() ? $saleSettlement->getValidateAt()->format('Y-m-d') : '',
            // 'validatedBy' => $saleSettlement->getValidateBy(),
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