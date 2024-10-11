<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\School\Schooling\Configuration\School;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Setting\Institution\ManagerTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PutSchoolController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(mixed $data, Request $request, SchoolRepository $schoolRepository, ManagerTypeRepository $managerTypeRepository, BranchRepository $branchRepository,
   )
    {
        $schoolData = json_decode($request->getContent(), true);

        $code = $schoolData['code'];
        $name = $schoolData['name'];
        $branch = !isset($schoolData['branch']) ? null : $branchRepository->find($this->getIdFromApiResourceId($schoolData['branch']));

        if(!$data instanceof School){
            return new JsonResponse(['hydra:description' => 'Not found.'], 400);
        }

        // Check for duplicates based on code within the same branch
        $duplicateCheckCode = $schoolRepository->findOneBy(['code' => $code, 'branch' => $branch]);
        if ($duplicateCheckCode  && ($duplicateCheckCode != $data)) {
            return new JsonResponse(['hydra:description' => 'This code already exists in this school branch.'], 400);
        }

        // Check for duplicates based on name within the same branch
        $duplicateCheckName = $schoolRepository->findOneBy(['name' => $name, 'branch' => $branch]);
        if ($duplicateCheckName  && ($duplicateCheckName != $data)) {
            return new JsonResponse(['hydra:description' => 'This name already exists in this school branch.'], 400);
        }

        $data->setCode($schoolData['code']);
        $data->setName($schoolData['name']);
        $data->setPhone($schoolData['phone']);
        $data->setEmail($schoolData['email']);
        $data->setAddress($schoolData['address']);
        $data->setCity($schoolData['city']);
        $data->setManager($schoolData['manager']);
        $data->setType($schoolData['type']);
        $data->setManagesNoteType($schoolData['managesNoteType']);
        $managerType = !isset($schoolData['managerType']) ? null : $managerTypeRepository->find($this->getIdFromApiResourceId($schoolData['managerType']));
        $data->setManagerType($managerType);

        $data->getSchoolBranch()->setCode($schoolData['code']);
        $data->getSchoolBranch()->setName($schoolData['name']);
        $data->getSchoolBranch()->setPhone($schoolData['phone']);
        $data->getSchoolBranch()->setEmail($schoolData['email']);
        $data->getSchoolBranch()->setAddress($schoolData['address']);

        $this->manager->flush();

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
