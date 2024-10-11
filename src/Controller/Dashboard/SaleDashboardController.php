<?php

namespace App\Controller\Dashboard;

use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Billing\School\SettlementRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class SaleDashboardController extends AbstractController
{
    private EntityManagerInterface $manager;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                Request                                $req, EntityManagerInterface $manager,
                                SaleSettlementRepository    $settlementRepository,)
    {
        $this->manager = $manager;
        $this->settlementRepository = $settlementRepository;
    }


    #[Route('/api/get/sale/dashboard/number-of-settlements', name: 'app_get_sale_dashboard_number_of_settlements')]
    public function NumberOfSettlements(): JsonResponse
    {

        $settlementCount= $this->settlementRepository->countSettlements();
        return new JsonResponse(['hydra:description' => $settlementCount]);

    }

    #[Route('/api/get/sale/dashboard/recent-settlements', name: 'app_get_sale_dashboard_recent_settlements')]
    public function RecentSettlement(): JsonResponse
    {
        $settlements = $this->settlementRepository
            ->createQueryBuilder('s')
            ->orderBy('s.id', 'DESC') // Order by creation date descending
            ->setMaxResults(5) // Limit to 5 results
            ->getQuery()
            ->getResult();

        $recentSettlements = [];
        foreach ($settlements as $settlement) {
            $recentSettlements[] = [
                'id' => $settlement->getId(),
                'class' => $settlement->getClass()->getCode(),
                'school' => $settlement->getSchool()->getName(),
                'paymentMethod' => $settlement->getPaymentMethod()->getName(),
                'customer' => $settlement->getCustomer() ? $settlement->getCustomer()->getName(): '',
                'reference' => $settlement->getReference(),
                'amount' => $settlement->getAmountPay(),
            ];
        }

        return new JsonResponse(['hydra:description' => $recentSettlements]);
    }

    #[Route('/api/get/sale/dashboard/total-amount', name: 'app_get_sale_dashboard_total_amount')]
    public function getTotalAmount(): JsonResponse
    {
        $saleSettlements = $this->settlementRepository->findAll();

        $totalAmountPay = 0;
        $totalAmountLeft = 0;

        foreach ($saleSettlements as $settlement) {
            $totalAmountPay += $settlement->getAmountPay();
            $totalAmountLeft += $settlement->getAmountRest();
        }

        $totalAmount = $totalAmountPay + $totalAmountLeft;

        return new JsonResponse([
            'totalAmountPay' => $totalAmountPay,
            'totalAmountLeft' => $totalAmountLeft,
            'totalAmount' => $totalAmount,
        ]);
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

}
