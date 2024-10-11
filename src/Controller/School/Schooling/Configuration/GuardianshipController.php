<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Repository\School\Schooling\Configuration\GuardianshipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GuardianshipController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(GuardianshipRepository $guardianshipRepository): JsonResponse
    {
        $guardianships = $guardianshipRepository->findByInstitution($this->getUser()->getInstitution());

        $table = [];

        foreach ($guardianships as $guardianship){

            $table [] = [
                '@id' => "/api/guardianships/".$guardianship->getId(),
                '@type' => "Guardianship",
                'id' => $guardianship->getId(),
                'school' => $guardianship->getSchool() ? [
                    '@id' => "/api/schools/".$guardianship->getSchool()->getId(),
                    '@type' => "School",
                    'id' => $guardianship->getSchool()->getId(),
                    'code' => $guardianship->getSchool()->getCode(),
                    'name' => $guardianship->getSchool()->getName(),
                    'email' => $guardianship->getSchool()->getEmail(),
                    'phone' => $guardianship->getSchool()->getPhone(),
                    'postalCode' => $guardianship->getSchool()->getPostalCode(),
                    'city' => $guardianship->getSchool()->getCity(),
                    'address' => $guardianship->getSchool()->getAddress(),
                    'manager' => $guardianship->getSchool()->getManager(),
                    'managerType' => $guardianship->getSchool()->getManagerType() ? [
                        '@id' => "/api/manager_types/".$guardianship->getSchool()->getManagerType()->getId(),
                        '@type' => "ManagerType",
                        'id' => $guardianship->getSchool()->getManagerType()->getId(),
                        'code' => $guardianship->getSchool()->getManagerType()->getCode(),
                        'name' => $guardianship->getSchool()->getManagerType()->getName(),
                    ] : '',
                ] : '',
                'building' => $guardianship->getBuilding() ? [
                    '@id' => "/api/buildings/".$guardianship->getBuilding()->getId(),
                    '@type' => "Building",
                    'id' => $guardianship->getBuilding()->getId(),
                    'campus' => [
                        '@id' => "/api/campuses/".$guardianship->getBuilding()->getCampus()->getId(),
                        '@type' => "Campus",
                        'id' => $guardianship->getBuilding()->getCampus()->getId(),
                        'code' => $guardianship->getBuilding()->getCampus()->getCode(),
                        'name' => $guardianship->getBuilding()->getCampus()->getName(),
                    ],
                    'name' => $guardianship->getBuilding()->getName(),
                ] : '',
                'code' => $guardianship->getCode(),
                'name' => $guardianship->getName(),
            ];
        }

        return $this->json(['hydra:member' => $table]);
    }

}
