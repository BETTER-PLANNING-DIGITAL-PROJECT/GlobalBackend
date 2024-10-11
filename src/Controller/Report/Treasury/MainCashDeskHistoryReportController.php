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
class MainCashDeskHistoryReportController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager, CashDeskHistoryRepository $cashDeskHistoryRepository, CashDeskRepository $cashDeskRepository,
                                private readonly TokenStorageInterface $tokenStorage)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->cashDeskRepository = $cashDeskRepository;
        $this->cashDeskHistoryRepository = $cashDeskHistoryRepository;
    }

    #[Route('/api/get/main-cash-desk-history/report', name: 'app_get_main_cash_desk_history_report')]
    public function getCashDeskHistories(Request $request): JsonResponse
    {
        $cashDeskHistoryData = json_decode($request->getContent(), true);

        $filteredInvoices = [];

        $dql = 'SELECT id, cash_desk_id, reference, description, debit, credit, balance FROM treasury_cash_desk_history
                 WHERE ';

        if(isset($cashDeskHistoryData['startDate']) && !isset($cashDeskHistoryData['endDate'])){
            $dql = $dql .' date_at LIKE \'' . $cashDeskHistoryData['startDate'] . '%\'';
        }


        if(isset($cashDeskHistoryData['startDate']) && isset($cashDeskHistoryData['endDate'])){
            $dql = $dql .' date_at BETWEEN \'' . $cashDeskHistoryData['startDate'] . '\' AND \'' . $cashDeskHistoryData['endDate'] . '\''; // Use proper escaping with \ and add space
        }

        if (isset($cashDeskHistoryData['cashDesk'])){
            $cashDeskId = $this->cashDeskRepository->find($this->getIdFromApiResourceId($cashDeskHistoryData['cashDesk']));

            $dql = $dql .' AND cash_desk_id = '. $cashDeskId->getId();
        }


        $conn = $this->entityManager->getConnection();
        $resultSet = $conn->executeQuery($dql);
        $rows = $resultSet->fetchAllAssociative();


        foreach ($rows as $row) {

            $cashDeskHistory = $this->cashDeskHistoryRepository->find($row['id']);

            $filteredInvoices[] = [
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

        return $this->json($filteredInvoices);
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