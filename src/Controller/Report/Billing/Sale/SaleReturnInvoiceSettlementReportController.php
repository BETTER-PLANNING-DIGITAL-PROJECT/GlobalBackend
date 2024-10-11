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
class SaleReturnInvoiceSettlementReportController extends AbstractController
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

    #[Route('/api/get/sale-return-invoice-settlement/report', name: 'app_get_sale_return_invoice_settlement_history_report')]
    public function getSaleSettlementHistory(Request $request, SystemSettingsRepository $systemSettingsRepository): JsonResponse
    {
        $saleInvoiceData = json_decode($request->getContent(), true);

        $filteredInvoices = [];

        $dql = 'SELECT id, invoice_id, customer_id, amount_pay, amount_rest FROM sale_settlement s WHERE invoice_id is null AND is_validate = 1 ';

        if(isset($saleInvoiceData['settleAtStart']) && !isset($saleInvoiceData['settleAtEnd'])){
            $dql = $dql .' AND settle_at LIKE '. '\''.$saleInvoiceData['settleAtStart'].'%\'';
        }

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
            'returnInvoice' => $saleSettlement->getSaleReturnInvoice() ? $saleSettlement->getSaleReturnInvoice()->getId() : '',
            'customer' => $saleSettlement->getCustomer() ? $saleSettlement->getCustomer()->getName() :  '',
            'reference' => $saleSettlement->getReference(),
            'amountPay' => number_format($saleSettlement->getAmountPay(), 0, null, ' '),
            'settleAt' => $saleSettlement->getSettleAt()->format('Y-m-d'),
            'note' => $saleSettlement->getNote(),
            'branch' => $saleSettlement->getBranch()->getCode(),
            'paymentMethod' => $saleSettlement->getPaymentMethod() ? $saleSettlement->getPaymentMethod()->getName() :  '',
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