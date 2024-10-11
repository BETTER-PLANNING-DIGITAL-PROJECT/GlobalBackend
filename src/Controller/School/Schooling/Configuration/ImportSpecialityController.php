<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\School\Schooling\Configuration\Speciality;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\CycleRepository;
use App\Repository\School\Schooling\Configuration\LevelRepository;
use App\Repository\School\Schooling\Configuration\ProgramRepository;
use App\Repository\School\Schooling\Configuration\SpecialityRepository;
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
final class ImportSpecialityController extends AbstractController
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

    public function __invoke(Request $request, SpecialityRepository $specialityRepository, CycleRepository $cycleRepository, ProgramRepository $programRepository, LevelRepository $levelRepository): Response
    {
        $data = $this->jsondecode();

        if(!isset($data->data))
            return throw new BadRequestHttpException('file is compulsory');

        $specialities = $data->data;

        if (!$specialities) {
            throw new BadRequestHttpException('"file" is required');
        }

        foreach ($specialities as $speciality){

            $specialityCode = $specialityRepository->findOneBy(['code' => $speciality->code]);
            if ($specialityCode){
                return new JsonResponse(['hydra:title' => 'The code: '.$specialityCode->getCode(). ' in line '. $speciality->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $specialityName = $specialityRepository->findOneBy(['name' => $speciality->name]);
            if ($specialityName){
                return new JsonResponse(['hydra:title' => 'The name: '.$specialityName->getName(). ' in line '. $speciality->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $cycle = $cycleRepository->findOneBy(['code' => $speciality->cycle]);
            if(!$cycle){
                return new JsonResponse(['hydra:title' => 'The code: '.$speciality->cycle. ' in line '. $speciality->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $program = $programRepository->findOneBy(['code' => $speciality->program]);
            if(!$program){
                return new JsonResponse(['hydra:title' => 'The code: '.$speciality->program. ' in line '. $speciality->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $minLevel = $levelRepository->findOneBy(['name' => $speciality->minimumLevel]);
            if(!$minLevel){
                return new JsonResponse(['hydra:title' => 'The name: '.$speciality->minimumLevel. ' in line '. $speciality->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $maxLevel = $levelRepository->findOneBy(['name' => $speciality->maximumLevel]);
            if(!$maxLevel){
                return new JsonResponse(['hydra:title' => 'The name: '.$speciality->maximumLevel. ' in line '. $speciality->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $newSpeciality = new Speciality();
            $newSpeciality->setCode($speciality->code);
            $newSpeciality->setName($speciality->name);
            $newSpeciality->setCycle($cycle);
            $newSpeciality->setProgram($program);
            $newSpeciality->setMinimumLevel($minLevel);
            $newSpeciality->setMaximumLevel($maxLevel);

            $newSpeciality->setInstitution($this->getUser()->getInstitution());
            $newSpeciality->setUser($this->getUser());

            $this->entityManager->persist($newSpeciality);
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



