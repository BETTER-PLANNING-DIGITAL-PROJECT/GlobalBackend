<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Repository\School\Schooling\Configuration\DepartmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DepartmentController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(DepartmentRepository $departmentRepository): JsonResponse
    {
        $departments = $departmentRepository->findByInstitution($this->getUser()->getInstitution());

        $table = [];

        foreach ($departments as $department){

            $table [] = [
                '@id' => "/api/departments/".$department->getId(),
                '@type' => "Department",
                'id' => $department->getId(),
                'school' => $department->getSchool() ? [
                    '@id' => "/api/schools/".$department->getSchool()->getId(),
                    '@type' => "School",
                    'id' => $department->getSchool()->getId(),
                    'code' => $department->getSchool()->getCode(),
                    'name' => $department->getSchool()->getName(),
                    'email' => $department->getSchool()->getEmail(),
                    'phone' => $department->getSchool()->getPhone(),
                    'postalCode' => $department->getSchool()->getPostalCode(),
                    'city' => $department->getSchool()->getCity(),
                    'address' => $department->getSchool()->getAddress(),
                    'manager' => $department->getSchool()->getManager(),
                    'managerType' => $department->getSchool()->getManagerType() ? [
                        '@id' => "/api/manager_types/".$department->getSchool()->getManagerType()->getId(),
                        '@type' => "ManagerType",
                        'id' => $department->getSchool()->getManagerType()->getId(),
                        'code' => $department->getSchool()->getManagerType()->getCode(),
                        'name' => $department->getSchool()->getManagerType()->getName(),
                    ] : '',
                ] : '',
                'code' => $department->getCode(),
                'name' => $department->getName(),
                'position' => $department->getPosition(),
            ];
        }

        return $this->json(['hydra:member' => $table]);
    }

}
