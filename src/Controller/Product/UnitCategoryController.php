<?php

namespace App\Controller\Product;

use App\Repository\Product\UnitCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UnitCategoryController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(UnitCategoryRepository $unitCategoryRepository): JsonResponse
    {
        $unitCategories = $unitCategoryRepository->findByInstitution($this->getUser()->getInstitution());

        $table = [];

        foreach ($unitCategories as $unitCategory){

            $table [] = [
                '@id' => "/api/get/unit-category/".$unitCategory->getId(),
                '@type' => "UnitCategory",
                'id' => $unitCategory->getId(),
                'name' => $unitCategory->getName(),
            ];
        }

        return $this->json(['hydra:member' => $table]);
    }

}
