<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\DepartmentRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetDepartmentController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, DepartmentRepository $departmentRepository, SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository):JsonResponse
    {
        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $departments = $departmentRepository->findBy([], ['id' => 'DESC']);

            foreach ($departments as $department){

                $requestData [] = [
                    '@id' => "/api/department/".$department->getId(),
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
                ];
            }
        }
        else
        {
            $systemSettings = $systemSettingsRepository->findOneBy([]);
            if($systemSettings)
            {
                if($systemSettings->isIsBranches())
                {
                    $userBranches = $this->getUser()->getUserBranches();
                    foreach ($userBranches as $userBranch) {
                        $school = $schoolRepository->findOneBy(['schoolBranch' => $userBranch]);
                        if ($school) {
                            $departments = $departmentRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($departments as $department){

                                $requestData [] = [
                                    '@id' => "/api/department/".$department->getId(),
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
                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $departments = $departmentRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($departments as $department) {
                            if ($department) {
                                $requestData [] = [
                                    '@id' => "/api/department/".$department->getId(),
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
                                ];
                            }
                        }
                    }

                }
            }
        }


        return $this->json(['hydra:member' => $requestData]);
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
