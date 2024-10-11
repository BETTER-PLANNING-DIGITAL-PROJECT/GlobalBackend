<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\GuardianshipRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetGuardianshipController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, GuardianshipRepository $guardianshipRepository, SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository):JsonResponse
    {
        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $guardianships = $guardianshipRepository->findBy([], ['id' => 'DESC']);

            foreach ($guardianships as $guardianship){

                $requestData [] = [
                    '@id' => "/api/guardianship/".$guardianship->getId(),
                    '@type' => "Room",
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
                    'code' => $guardianship->getCode(),
                    'name' => $guardianship->getName(),
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
                            $guardianships = $guardianshipRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($guardianships as $guardianship){

                                $requestData [] = [
                                    '@id' => "/api/guardianship/".$guardianship->getId(),
                                    '@type' => "Room",
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
                                    'code' => $guardianship->getCode(),
                                    'name' => $guardianship->getName(),
                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $guardianships = $guardianshipRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($guardianships as $guardianship) {
                            if ($guardianship) {
                                $requestData [] = [
                                    '@id' => "/api/guardianship/".$guardianship->getId(),
                                    '@type' => "Room",
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
                                    'code' => $guardianship->getCode(),
                                    'name' => $guardianship->getName(),
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
