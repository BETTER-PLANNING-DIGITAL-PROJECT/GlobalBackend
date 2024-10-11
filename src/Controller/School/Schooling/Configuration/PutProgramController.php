<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\BuildingRepository;
use App\Repository\School\Schooling\Configuration\ProgramRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PutProgramController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data, Request $request, ProgramRepository $programRepository, BranchRepository $branchRepository,
    SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository, BuildingRepository $buildingRepository)
    {
        $requestData = json_decode($request->getContent(), true);
        $school = !isset($requestData['school']) ? null : $schoolRepository->find($this->getIdFromApiResourceId($requestData['school']));

        $code = $requestData['code'];
        $name = $requestData['name'];

        $systemSettings = $systemSettingsRepository->findOneBy([]);

        $schools = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $duplicateCheckCode = $programRepository->findOneBy(['code' => $code, 'school' => $school, 'year' => $this->getUser()->getCurrentYear()]);
            } else {
                $duplicateCheckCode = $programRepository->findOneBy(['code' => $code, 'school' => $schools, 'year' => $this->getUser()->getCurrentYear()]);
            }
            if ($duplicateCheckCode && ($duplicateCheckCode != $data)) {
                return new JsonResponse(['hydra:description' => 'This code already exists in this school.'], 400);
            }
        }
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $duplicateCheckName = $programRepository->findOneBy(['name' => $name, 'school' => $school, 'year' => $this->getUser()->getCurrentYear()]);
            } else {
                $duplicateCheckName = $programRepository->findOneBy(['name' => $name, 'school' => $schools, 'year' => $this->getUser()->getCurrentYear()]);
            }
            if ($duplicateCheckName && ($duplicateCheckName != $data)) {
                return new JsonResponse(['hydra:description' => 'This name already exists in this school.'], 400);
            }
        }

        $data->setCode($requestData['code']);
        $data->setName($requestData['name']);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $data->setSchool($school);
            }
        }

        return $data;
    }

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
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
