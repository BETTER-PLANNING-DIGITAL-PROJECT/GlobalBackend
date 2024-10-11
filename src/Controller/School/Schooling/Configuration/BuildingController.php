<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Repository\School\Schooling\Configuration\BuildingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class BuildingController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(BuildingRepository $buildingRepository): JsonResponse
    {
        $buildings = $buildingRepository->findByInstitution($this->getUser()->getInstitution());

        $table = [];

        foreach ($buildings as $building){

            $table [] = [
                '@id' => "/api/buildings/".$building->getId(),
                '@type' => "Building",
                'id' => $building->getId(),
                'campus' => [
                    '@id' => "/api/campuses/".$building->getCampus()->getId(),
                    '@type' => "Campus",
                    'id' => $building->getCampus()->getId(),
                    'code' => $building->getCampus()->getCode(),
                    'name' => $building->getCampus()->getName(),
                ],
                'name' => $building->getName(),
            ];
        }

        return $this->json(['hydra:member' => $table]);
    }

}
