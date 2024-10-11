<?php

namespace App\Controller\Product;

use App\Repository\Product\UnitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UnitController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(UnitRepository $unitRepository): JsonResponse
    {
        $units = $unitRepository->findByInstitution($this->getUser()->getInstitution());

        $table = [];

        foreach ($units as $unit){

            $table [] = [
                '@id' => "/api/units/".$unit->getId(),
                '@type' => "Unit",
                'id' => $unit->getId(),
                'unitCategory' => $unit->getUnitCategory() ? [
                    '@id' => "/api/unit_categories/".$unit->getUnitCategory()->getId(),
                    '@type' => "UnitCategory",
                    'id' => $unit->getUnitCategory()->getId(),
                    'name' => $unit->getUnitCategory()->getName(),
                ] : '',
                'name' => $unit->getName(),
                'round' => $unit->getRound(),
            ];
        }

        return $this->json(['hydra:member' => $table]);
    }

}
