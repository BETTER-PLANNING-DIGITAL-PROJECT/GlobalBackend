<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\ProgramRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetProgramController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, ProgramRepository $programRepository, SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository):JsonResponse
    {
        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $programs = $programRepository->findBy([], ['id' => 'DESC']);

            foreach ($programs as $program){

                $requestData [] = [
                    '@id' => "/api/program/".$program->getId(),
                    '@type' => "Program",
                    'id' => $program->getId(),
                    'school' => $program->getSchool() ? [
                        '@id' => "/api/schools/".$program->getSchool()->getId(),
                        '@type' => "School",
                        'id' => $program->getSchool()->getId(),
                        'code' => $program->getSchool()->getCode(),
                        'name' => $program->getSchool()->getName(),
                        'email' => $program->getSchool()->getEmail(),
                        'phone' => $program->getSchool()->getPhone(),
                        'postalCode' => $program->getSchool()->getPostalCode(),
                        'city' => $program->getSchool()->getCity(),
                        'address' => $program->getSchool()->getAddress(),
                        'manager' => $program->getSchool()->getManager(),
                        'managerType' => $program->getSchool()->getManagerType() ? [
                            '@id' => "/api/manager_types/".$program->getSchool()->getManagerType()->getId(),
                            '@type' => "ManagerType",
                            'id' => $program->getSchool()->getManagerType()->getId(),
                            'code' => $program->getSchool()->getManagerType()->getCode(),
                            'name' => $program->getSchool()->getManagerType()->getName(),
                        ] : '',
                    ] : '',
                    'code' => $program->getCode(),
                    'name' => $program->getName(),
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
                            $programs = $programRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($programs as $program){

                                $requestData [] = [
                                    '@id' => "/api/program/".$program->getId(),
                                    '@type' => "Program",
                                    'id' => $program->getId(),
                                    'school' => $program->getSchool() ? [
                                        '@id' => "/api/schools/".$program->getSchool()->getId(),
                                        '@type' => "School",
                                        'id' => $program->getSchool()->getId(),
                                        'code' => $program->getSchool()->getCode(),
                                        'name' => $program->getSchool()->getName(),
                                        'email' => $program->getSchool()->getEmail(),
                                        'phone' => $program->getSchool()->getPhone(),
                                        'postalCode' => $program->getSchool()->getPostalCode(),
                                        'city' => $program->getSchool()->getCity(),
                                        'address' => $program->getSchool()->getAddress(),
                                        'manager' => $program->getSchool()->getManager(),
                                        'managerType' => $program->getSchool()->getManagerType() ? [
                                            '@id' => "/api/manager_types/".$program->getSchool()->getManagerType()->getId(),
                                            '@type' => "ManagerType",
                                            'id' => $program->getSchool()->getManagerType()->getId(),
                                            'code' => $program->getSchool()->getManagerType()->getCode(),
                                            'name' => $program->getSchool()->getManagerType()->getName(),
                                        ] : '',
                                    ] : '',
                                    'code' => $program->getCode(),
                                    'name' => $program->getName(),
                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $programs = $programRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($programs as $program) {
                            if ($program) {
                                $requestData [] = [
                                    '@id' => "/api/program/".$program->getId(),
                                    '@type' => "Program",
                                    'id' => $program->getId(),
                                    'school' => $program->getSchool() ? [
                                        '@id' => "/api/schools/".$program->getSchool()->getId(),
                                        '@type' => "School",
                                        'id' => $program->getSchool()->getId(),
                                        'code' => $program->getSchool()->getCode(),
                                        'name' => $program->getSchool()->getName(),
                                        'email' => $program->getSchool()->getEmail(),
                                        'phone' => $program->getSchool()->getPhone(),
                                        'postalCode' => $program->getSchool()->getPostalCode(),
                                        'city' => $program->getSchool()->getCity(),
                                        'address' => $program->getSchool()->getAddress(),
                                        'manager' => $program->getSchool()->getManager(),
                                        'managerType' => $program->getSchool()->getManagerType() ? [
                                            '@id' => "/api/manager_types/".$program->getSchool()->getManagerType()->getId(),
                                            '@type' => "ManagerType",
                                            'id' => $program->getSchool()->getManagerType()->getId(),
                                            'code' => $program->getSchool()->getManagerType()->getCode(),
                                            'name' => $program->getSchool()->getManagerType()->getName(),
                                        ] : '',
                                    ] : '',
                                    'code' => $program->getCode(),
                                    'name' => $program->getName(),
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
