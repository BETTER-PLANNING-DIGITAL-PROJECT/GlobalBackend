<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\School\Schooling\Configuration\Department;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\DepartmentRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class ImportDepartmentController extends AbstractController
{
    public function jsondecode()
    {
        try {
            return file_get_contents('php://input') ?
                json_decode(file_get_contents('php://input'), false) :
                [];
        }catch (\Exception $ex)
        {
            return [];
        }
    }

    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(Request $request, DepartmentRepository $departmentRepository, SchoolRepository $schoolRepository): Response
    {
        $data = $this->jsondecode();

        if(!isset($data->data))
            return throw new BadRequestHttpException('file is compulsory');

        $departments = $data->data;

        if (!$departments) {
            throw new BadRequestHttpException('"file" is required');
        }

        foreach ($departments as $department){
            //dd($import);

            $departmentCode = $departmentRepository->findOneBy(['code' => $department->code]);
            if ($departmentCode){
                return new JsonResponse(['hydra:title' => 'The code: '.$departmentCode->getCode(). ' in line '. $department->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $departmentName = $departmentRepository->findOneBy(['name' => $department->name]);
            if ($departmentName){
                return new JsonResponse(['hydra:title' => 'The name: '.$departmentName->getName(). ' in line '. $department->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $school = $schoolRepository->findOneBy(['code' => $department->school]);
            if(!$school){
                return new JsonResponse(['hydra:title' => 'The code: '.$department->school. ' in line '. $department->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $newDepartment = new Department();
            $newDepartment->setCode($department->code);
            $newDepartment->setName($department->name);
            $newDepartment->setSchool($school);

            $newDepartment->setInstitution($this->getUser()->getInstitution());
            $newDepartment->setUser($this->getUser());

            $this->entityManager->persist($newDepartment);
        }

        $this->entityManager->flush();

        return new Response(null, Response::HTTP_OK);

        //return $this->json(['hydra:member' => $this->roleResultName($profile->getName())]);

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



