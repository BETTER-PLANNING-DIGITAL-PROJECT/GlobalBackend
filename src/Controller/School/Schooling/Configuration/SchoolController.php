<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Repository\School\Schooling\Configuration\SchoolRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class SchoolController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(SchoolRepository $schoolRepository): JsonResponse
    {
        $schools = $schoolRepository->findByInstitution($this->getUser()->getInstitution());

        $table = [];

        foreach ($schools as $school){

            $table [] = [
                '@id' => "/api/schools/".$school->getId(),
                '@type' => "School",
                'id' => $school->getId(),
                'code' => $school->getCode(),
                'name' => $school->getName(),
                'email' => $school->getEmail(),
                'phone' => $school->getPhone(),
                'postalCode' => $school->getPostalCode(),
                'city' => $school->getCity(),
                'address' => $school->getAddress(),
                'manager' => $school->getManager(),
                'managerType' => $school->getManagerType() ? [
                    '@id' => "/api/manager_types/".$school->getManagerType()->getId(),
                    '@type' => "ManagerType",
                    'id' => $school->getManagerType()->getId(),
                    'code' => $school->getManagerType()->getCode(),
                    'name' => $school->getManagerType()->getName(),
                ] : '',
            ];
        }

        return $this->json(['hydra:member' => $table]);
    }

}
