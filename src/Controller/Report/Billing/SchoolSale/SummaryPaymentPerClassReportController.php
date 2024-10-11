<?php

namespace App\Controller\Report\Billing\SchoolSale;

use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
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
class SummaryPaymentPerClassReportController extends AbstractController
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

    public function __invoke(SaleSettlementRepository $saleSettlementRepository, SaleInvoiceItemRepository $saleInvoiceItemRepository, Request $request, SchoolClassRepository $schoolClassRepository): array
    {
        $saleSettlements = $saleSettlementRepository->findAll();
        $allClasses = $schoolClassRepository->findAll();

        $classTotals = [];
        foreach ($allClasses as $class) {
            $classId = $class->getId();
            $classTotals[$classId] = [
                'totalPaid' => 0,
                'totalLeft' => 0,
                'class' => $class->getCode(),
                'school' => null,
                'paymentMethod' => null,
                'totalAmount' => 0,
            ];
        }

        foreach ($saleSettlements as $settlement) {
            $classId = $settlement->getClass()?->getId();

            if (isset($classTotals[$classId])) {
                $classTotals[$classId]['totalPaid'] += $settlement->getAmountPay();
                $classTotals[$classId]['totalLeft'] += $settlement->getAmountRest();
                $classTotals[$classId]['totalAmount'] = $classTotals[$classId]['totalPaid'] + $classTotals[$classId]['totalLeft'];
                $classTotals[$classId]['school'] = $settlement->getSchool()->getName();
                $classTotals[$classId]['paymentMethod'] = $settlement->getPaymentMethod()->getName();
            }
        }

        return $classTotals;
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



