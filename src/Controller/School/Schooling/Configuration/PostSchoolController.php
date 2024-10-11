<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\School\Schooling\Configuration\School;
use App\Entity\Security\Institution\Branch;
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
class PostSchoolController extends AbstractController
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

        // Check for duplicates based on code within the same branch
        $duplicateCheckCode = $schoolRepository->findOneBy(['code' => $code, 'branch' => $branch]);
        if ($duplicateCheckCode) {
            return new JsonResponse(['hydra:description' => 'This code already exists in this school branch.'], 400);
        }

        // Check for duplicates based on name within the same branch
        $duplicateCheckName = $schoolRepository->findOneBy(['name' => $name, 'branch' => $branch]);
        if ($duplicateCheckName) {
            return new JsonResponse(['hydra:description' => 'This name already exists in this school branch.'], 400);
        }

        $newBranch = new Branch();
        $newBranch->setCode($schoolData['code']);
        $newBranch->setName($schoolData['name']);
        $newBranch->setPhone($schoolData['phone']);
        $newBranch->setEmail($schoolData['email']);
        $newBranch->setAddress($schoolData['address']);
        $newBranch->setWebsite('');

        $newBranch->setInstitution($this->getUser()->getInstitution());
        $newBranch->setUser($this->getUser());

        $this->manager->persist($newBranch);

        $newSchool = new School();
        $newSchool->setCode($schoolData['code']);
        $newSchool->setName($schoolData['name']);
        $newSchool->setPhone($schoolData['phone']);
        $newSchool->setEmail($schoolData['email']);
        $newSchool->setAddress($schoolData['address']);
        $newSchool->setCity($schoolData['city']);
        $newSchool->setManager($schoolData['manager']);
        $newSchool->setType($schoolData['type']);
        $newSchool->setManagesNoteType($schoolData['managesNoteType']);
        $managerType = !isset($schoolData['managerType']) ? null : $managerTypeRepository->find($this->getIdFromApiResourceId($schoolData['managerType']));
        $newSchool->setManagerType($managerType);
        $newSchool->setBranch($this->getUser()->getBranch());
        $newSchool->setSchoolBranch($newBranch);



        $newSchool->setInstitution($this->getUser()->getInstitution());
        $newSchool->setYear($this->getUser()->getCurrentYear());
        $newSchool->setUser($this->getUser());

        $schoolRepository->save($newSchool);
        $this->manager->flush();

        return $newSchool;
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
