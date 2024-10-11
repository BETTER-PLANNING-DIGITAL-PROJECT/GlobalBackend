<?php

namespace App\Controller\Report\Inventory;

use App\Entity\Inventory\StockMovement;
use App\Entity\Security\User;
use App\Repository\Inventory\StockMovementRepository;
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
class StockMovementHistoryReportController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager,
                                private readonly TokenStorageInterface $tokenStorage,  StockMovementRepository $stockMovementRepository, BranchRepository $branchRepository)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->stockMovementRepository = $stockMovementRepository;
        $this->branchRepository = $branchRepository;
    }

    #[Route('/api/get/stock-movement-history/report', name: 'app_get_stock_movement_history_report')]
    public function getStockMovementHistory(Request $request, SystemSettingsRepository $systemSettingsRepository): JsonResponse
    {
        $stockMovementData = json_decode($request->getContent(), true);

        $filteredStockMovements = [];

        $dql = 'SELECT id, item_id, from_warehouse_id, from_location_id, to_location_id, from_warehouse_id, quantity, unit_cost, note FROM inventory_stock_movement s 
            WHERE ';


        $systemSettings = $systemSettingsRepository->findOneBy([]);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {

                if (isset($saleInvoiceData['branch'])){
                    $branch = $this->branchRepository->find($this->getIdFromApiResourceId($stockMovementData['branch']));
                    $dql = $dql .' branch_id = '. $branch->getId();

                    if(isset($stockMovementData['stockAt'])){
                        $dql = $dql .' AND stock_at LIKE '. '\''.$stockMovementData['stockAt'].'%\'';
                    }
                }
                else{
                    if(isset($stockMovementData['stockAt'])){
                        $dql = $dql .' stock_at LIKE '. '\''.$stockMovementData['stockAt'].'%\'';
                    }
                }

            }
            else{
                $branch = $this->getUser()->getBranch();
                $dql = $dql .' branch_id = '. $branch->getId();

                if(isset($stockMovementData['stockAt'])){
                    $dql = $dql .' AND stock_at LIKE '. '\''.$stockMovementData['stockAt'].'%\'';
                }
            }
        }
        else{
            $branch = $this->getUser()->getBranch();
            $dql = $dql .' branch_id = '. $branch->getId();

            if(isset($stockMovementData['stockAt'])){
                $dql = $dql .' AND stock_at LIKE '. '\''.$stockMovementData['stockAt'].'%\'';
            }
        }

        /*if (isset($stockMovementData['branch'])){
            $branch = $this->branchRepository->find($this->getIdFromApiResourceId($stockMovementData['branch']));

            $dql = $dql .' branch_id = '. $branch->getId();
        }*/

        /*if(isset($stockMovementData['stockAt'])){
            $dql = $dql .' AND stock_at LIKE '. '\''.$stockMovementData['stockAt'].'%\'';
            if (isset($stockMovementData['branch'])){
                $dql = $dql .' AND stock_at LIKE '. '\''.$stockMovementData['stockAt'].'%\'';
            }
            else{
                $dql = $dql .' stock_at LIKE '. '\''.$stockMovementData['stockAt'].'%\'';
            }
        }*/

        $conn = $this->entityManager->getConnection();
        $resultSet = $conn->executeQuery($dql);
        $rows = $resultSet->fetchAllAssociative();

        foreach ($rows as $row) {
            $stockMovement = $this->stockMovementRepository->find($row['id']);
            $filteredStockMovements[] = $this->bindStockMovement($stockMovement);
        }

        return $this->json($filteredStockMovements);
    }

    public function bindStockMovement(StockMovement $stockMovement): array
    {
        return [
            'id' => $stockMovement->getId(),
            'dateAt' => $stockMovement->getStockAt()->format('d/m/y H:i'),
            'reference' => $stockMovement->getReference(),
            'item' => $stockMovement->getItem() ? $stockMovement->getItem()->getName() : '',
            'from' => $stockMovement->getFromWarehouse() ? $stockMovement->getFromWarehouse()->getName() : '',
            'to' => $stockMovement->getToWarehouse() ? $stockMovement->getToWarehouse()->getName() : '',
            'quantity' => $stockMovement->getQuantity(),
            'type' => $stockMovement->getType(),
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



