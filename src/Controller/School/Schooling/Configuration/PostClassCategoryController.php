<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\School\Schooling\Configuration\ClassCategory;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\ClassCategoryRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PostClassCategoryController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data, Request $request, ClassCategoryRepository $classCategoryRepository, BranchRepository $branchRepository,
    SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository)
    {
        $requestData = json_decode($request->getContent(), true);
        $school = !isset($requestData['school']) ? null : $schoolRepository->find($this->getIdFromApiResourceId($requestData['school']));

        $code = $requestData['code'];
        $name = $requestData['name'];

        $systemSettings = $systemSettingsRepository->findOneBy([]);

        $schools = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $duplicateCheckCode = $classCategoryRepository->findOneBy(['code' => $code, 'school' => $school, 'year' => $this->getUser()->getCurrentYear()]);
            } else {
                $duplicateCheckCode = $classCategoryRepository->findOneBy(['code' => $code, 'school' => $schools, 'year' => $this->getUser()->getCurrentYear()]);
            }
            if ($duplicateCheckCode) {
                return new JsonResponse(['hydra:description' => 'This code already exists in this school.'], 400);
            }
        }
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $duplicateCheckName = $classCategoryRepository->findOneBy(['name' => $name, 'school' => $school, 'year' => $this->getUser()->getCurrentYear()]);
            } else {
                $duplicateCheckName = $classCategoryRepository->findOneBy(['name' => $name, 'school' => $schools, 'year' => $this->getUser()->getCurrentYear()]);
            }
            if ($duplicateCheckName) {
                return new JsonResponse(['hydra:description' => 'This name already exists in this school.'], 400);
            }
        }

        $classCategory = new ClassCategory();
        $classCategory->setCode($requestData['code']);
        $classCategory->setName($requestData['name']);
        $schools = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $classCategory->setSchool($school);
            } else {
                $classCategory->setSchool($schools);
            }
        }

        $classCategory->setInstitution($this->getUser()->getInstitution());
        $classCategory->setUser($this->getUser());
        $classCategory->setYear($this->getUser()->getCurrentYear());

        $classCategoryRepository->save($classCategory);

        return $classCategory;
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
