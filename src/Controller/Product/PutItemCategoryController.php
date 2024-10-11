<?php

namespace App\Controller\Product;

use App\Entity\Security\User;
use App\Repository\Product\ItemCategoryRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use App\Repository\Setting\Inventory\PriceStrategyRepository;
use App\Repository\Setting\Inventory\StockStrategyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PutItemCategoryController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data, Request $request, PriceStrategyRepository $priceStrategyRepository, StockStrategyRepository $stockStrategyRepository, ItemCategoryRepository $itemCategoryRepository, BranchRepository $branchRepository,
                             SystemSettingsRepository $systemSettingsRepository)
    {
        $itemCategoryData = json_decode($request->getContent(), true);

        $systemSettings = $systemSettingsRepository->findOneBy([]);
        $name = $itemCategoryData['name'];
        // $branch = !isset($itemCategoryData['branch']) ? null : $branchRepository->find($this->getIdFromApiResourceId($itemCategoryData['branch']));

        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                if (!isset($itemCategoryData['branch'])) {
                    return new JsonResponse(['hydra:description' => 'Branch not found!.'], 400);
                }
                $branch = $branchRepository->find($this->getIdFromApiResourceId($itemCategoryData['branch']));
            }
            else{
                $branch = $this->getUser()->getBranch();
            }
        }
        else{
            $branch = $this->getUser()->getBranch();
        }

        // Check for duplicates based on name within the same branch
        $duplicateCheckName = $itemCategoryRepository->findOneBy(['name' => $name, 'branch' => $branch]);
        if ($duplicateCheckName && ($duplicateCheckName != $data)) {
            return new JsonResponse(['hydra:description' => 'This item category already exists in this branch.'], 400);
        }

        $data->setName($itemCategoryData['name']);
        $stockStrategy = !isset($itemCategoryData['stockStrategy']) ? null : $stockStrategyRepository->find($this->getIdFromApiResourceId($itemCategoryData['stockStrategy']));
        $data->setStockStrategy($stockStrategy);
        $priceStrategy = !isset($itemCategoryData['priceStrategy']) ? null : $priceStrategyRepository->find($this->getIdFromApiResourceId($itemCategoryData['priceStrategy']));
        $data->setPriceStrategy($priceStrategy);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $data->setBranch($branch);
            }
        }

        return $data;
    }

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
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
