<?php

namespace App\Controller\Report\Treasury;

use App\Entity\Security\User;
use App\Repository\Treasury\CashDeskHistoryRepository;
use App\Repository\Treasury\CashDeskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class CashDeskHistoryReportController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager, CashDeskHistoryRepository $cashDeskHistoryRepository, CashDeskRepository $cashDeskRepository,
                                private readonly TokenStorageInterface $tokenStorage)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->cashDeskHistoryRepository = $cashDeskHistoryRepository;
        $this->cashDeskRepository = $cashDeskRepository;
    }

    #[Route('/api/get/cash-desk-history/report', name: 'app_get_cash_desk_history_report')]
    public function getCashDeskHistory(Request $request): JsonResponse
    {
        $cashDeskHistoryData = json_decode($request->getContent(), true);

        $filteredHistories = [];

        $cashDesk = $this->cashDeskRepository->findOneBy(['operator' => $this->getUser()]);

        $dql = 'SELECT id, cash_desk_id, reference, description, debit, credit, balance FROM treasury_cash_desk_history
                 WHERE ';

        if(isset($cashDeskHistoryData['startDate']) && !isset($cashDeskHistoryData['endDate'])){
            $dql = $dql .'date_at LIKE '. '\''.$cashDeskHistoryData['startDate'].'%\'';
        }

        if(isset($cashDeskHistoryData['startDate']) && isset($cashDeskHistoryData['endDate'])){
            $dql = $dql .'date_at BETWEEN '. '\''.$cashDeskHistoryData['startDate'].'\''. ' AND '. '\''.$cashDeskHistoryData['endDate'].'\'';
        }

        $dql = $dql .'AND cash_desk_id = '. $cashDesk->getId();

        $dql = $dql .' AND branch_id = '. $this->getUser()->getBranch()->getId();


        $conn = $this->entityManager->getConnection();
        $resultSet = $conn->executeQuery($dql);
        $rows = $resultSet->fetchAllAssociative();

        foreach ($rows as $row) {

            $cashDeskHistory = $this->cashDeskHistoryRepository->find($row['id']);

            $filteredHistories[] = [
                'id' => $cashDeskHistory->getId(),
                'reference' => $cashDeskHistory->getReference(),
                'description' => $cashDeskHistory->getDescription(),
                'debit' => $cashDeskHistory->getDebit(),
                'credit' => $cashDeskHistory->getCredit(),
                'balance' => $cashDeskHistory->getBalance(),
                'dateAt' => $cashDeskHistory->getDateAt()->format('Y-m-d'),
                'cashDesk' => [
                    '@id' => "/api/cashDesk/" . $cashDeskHistory->getCashDesk()->getId(),
                    '@type' => "cash",
                    'id' => $cashDeskHistory->getCashDesk()->getId(),
                    'code' => $cashDeskHistory->getCashDesk()->getCode(),
                    'balance' => $cashDeskHistory->getCashDesk()->getBalance(),
                    'dailyDeposit' => $cashDeskHistory->getCashDesk()->getDailyDeposit(),
                    'dailyWithdrawal' => $cashDeskHistory->getCashDesk()->getDailyWithdrawal(),
                ],
            ];
        }

        return $this->json($filteredHistories);
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