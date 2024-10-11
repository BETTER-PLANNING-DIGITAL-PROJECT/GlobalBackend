<?php

namespace App\Controller\School\Schooling\Registration;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\Security\Institution\BranchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class UpdateRegistrationClassController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data, Request $request, StudentRegistrationRepository $studentRegistrationRepository, StudentRepository $studentRepository, StudentCourseRegistrationRepository $studentCourseRegistrationRepository, BranchRepository $branchRepository,
                             SchoolClassRepository $schoolClassRepository)
    {
        $requestData = json_decode($request->getContent(), true);
        $class = !isset($requestData['classe']) ? null : $schoolClassRepository->find($this->getIdFromApiResourceId($requestData['classe']));

        if($data){
            $checkStudentCourseRegistration = $studentCourseRegistrationRepository->findOneBy(['id' => $data->getId()]);
            if($checkStudentCourseRegistration) {
                return new JsonResponse(['hydra:description' => 'This student is already registered for courses.'], 400);
            } else {
                $data->setClasse($class);
                $data->setCurrentClass($class);
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
