<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\ClassCategoryRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetClassCategoryController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, ClassCategoryRepository $classCategoryRepository, SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository):JsonResponse
    {
        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $classCategories = $classCategoryRepository->findBy([], ['id' => 'DESC']);

            foreach ($classCategories as $classCategory){

                $requestData [] = [
                    '@id' => "/api/class-category/".$classCategory->getId(),
                    '@type' => "ClassCategory",
                    'id' => $classCategory->getId(),
                    'school' => $classCategory->getSchool() ? [
                        '@id' => "/api/schools/".$classCategory->getSchool()->getId(),
                        '@type' => "School",
                        'id' => $classCategory->getSchool()->getId(),
                        'code' => $classCategory->getSchool()->getCode(),
                        'name' => $classCategory->getSchool()->getName(),
                        'email' => $classCategory->getSchool()->getEmail(),
                        'phone' => $classCategory->getSchool()->getPhone(),
                        'postalCode' => $classCategory->getSchool()->getPostalCode(),
                        'city' => $classCategory->getSchool()->getCity(),
                        'address' => $classCategory->getSchool()->getAddress(),
                        'manager' => $classCategory->getSchool()->getManager(),
                        'managerType' => $classCategory->getSchool()->getManagerType() ? [
                            '@id' => "/api/manager_types/".$classCategory->getSchool()->getManagerType()->getId(),
                            '@type' => "ManagerType",
                            'id' => $classCategory->getSchool()->getManagerType()->getId(),
                            'code' => $classCategory->getSchool()->getManagerType()->getCode(),
                            'name' => $classCategory->getSchool()->getManagerType()->getName(),
                        ] : '',
                    ] : '',
                    'code' => $classCategory->getCode(),
                    'name' => $classCategory->getName(),
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
                            $classCategories = $classCategoryRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($classCategories as $classCategory){

                                $requestData [] = [
                                    '@id' => "/api/class-category/".$classCategory->getId(),
                                    '@type' => "ClassCategory",
                                    'id' => $classCategory->getId(),
                                    'school' => $classCategory->getSchool() ? [
                                        '@id' => "/api/schools/".$classCategory->getSchool()->getId(),
                                        '@type' => "School",
                                        'id' => $classCategory->getSchool()->getId(),
                                        'code' => $classCategory->getSchool()->getCode(),
                                        'name' => $classCategory->getSchool()->getName(),
                                        'email' => $classCategory->getSchool()->getEmail(),
                                        'phone' => $classCategory->getSchool()->getPhone(),
                                        'postalCode' => $classCategory->getSchool()->getPostalCode(),
                                        'city' => $classCategory->getSchool()->getCity(),
                                        'address' => $classCategory->getSchool()->getAddress(),
                                        'manager' => $classCategory->getSchool()->getManager(),
                                        'managerType' => $classCategory->getSchool()->getManagerType() ? [
                                            '@id' => "/api/manager_types/".$classCategory->getSchool()->getManagerType()->getId(),
                                            '@type' => "ManagerType",
                                            'id' => $classCategory->getSchool()->getManagerType()->getId(),
                                            'code' => $classCategory->getSchool()->getManagerType()->getCode(),
                                            'name' => $classCategory->getSchool()->getManagerType()->getName(),
                                        ] : '',
                                    ] : '',
                                    'code' => $classCategory->getCode(),
                                    'name' => $classCategory->getName(),
                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $classCategories = $classCategoryRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($classCategories as $classCategory) {
                            if ($classCategory) {
                                $requestData [] = [
                                    '@id' => "/api/class-category/".$classCategory->getId(),
                                    '@type' => "ClassCategory",
                                    'id' => $classCategory->getId(),
                                    'school' => $classCategory->getSchool() ? [
                                        '@id' => "/api/schools/".$classCategory->getSchool()->getId(),
                                        '@type' => "School",
                                        'id' => $classCategory->getSchool()->getId(),
                                        'code' => $classCategory->getSchool()->getCode(),
                                        'name' => $classCategory->getSchool()->getName(),
                                        'email' => $classCategory->getSchool()->getEmail(),
                                        'phone' => $classCategory->getSchool()->getPhone(),
                                        'postalCode' => $classCategory->getSchool()->getPostalCode(),
                                        'city' => $classCategory->getSchool()->getCity(),
                                        'address' => $classCategory->getSchool()->getAddress(),
                                        'manager' => $classCategory->getSchool()->getManager(),
                                        'managerType' => $classCategory->getSchool()->getManagerType() ? [
                                            '@id' => "/api/manager_types/".$classCategory->getSchool()->getManagerType()->getId(),
                                            '@type' => "ManagerType",
                                            'id' => $classCategory->getSchool()->getManagerType()->getId(),
                                            'code' => $classCategory->getSchool()->getManagerType()->getCode(),
                                            'name' => $classCategory->getSchool()->getManagerType()->getName(),
                                        ] : '',
                                    ] : '',
                                    'code' => $classCategory->getCode(),
                                    'name' => $classCategory->getName(),
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
