<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\School\Schooling\Configuration\Option;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\LevelRepository;
use App\Repository\School\Schooling\Configuration\OptionRepository;
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
final class ImportOptionController extends AbstractController
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

    public function __invoke(Request $request, OptionRepository $optionRepository, LevelRepository $levelRepository, SpecialityRepository $specialityRepository): Response
    {
        $data = $this->jsondecode();

        if(!isset($data->data))
            return throw new BadRequestHttpException('file is compulsory');

        $options = $data->data;

        if (!$options) {
            throw new BadRequestHttpException('"file" is required');
        }

        foreach ($options as $option){

            $optionCode = $optionRepository->findOneBy(['code' => $option->code]);
            if ($optionCode){
                return new JsonResponse(['hydra:title' => 'The code: '.$optionCode->getCode(). ' in line '. $option->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $optionName = $optionRepository->findOneBy(['name' => $option->name]);
            if ($optionName){
                return new JsonResponse(['hydra:title' => 'The name: '.$optionName->getName(). ' in line '. $option->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $level = $levelRepository->findOneBy(['name' => $option->level]);
            if(!$level){
                return new JsonResponse(['hydra:title' => 'The name: '.$option->level. ' in line '. $option->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $speciality = $specialityRepository->findOneBy(['code' => $option->speciality]);
            if(!$speciality){
                return new JsonResponse(['hydra:title' => 'The code: '.$option->speciality. ' in line '. $option->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $newOption = new Option();
            $newOption->setCode($option->code);
            $newOption->setName($option->name);
            $newOption->setLevel($level);
            $newOption->setSpeciality($speciality);

            $newOption->setInstitution($this->getUser()->getInstitution());
            $newOption->setUser($this->getUser());

            $this->entityManager->persist($newOption);
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



