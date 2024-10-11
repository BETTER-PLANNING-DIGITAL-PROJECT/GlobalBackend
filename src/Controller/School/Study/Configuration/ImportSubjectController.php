<?php

namespace App\Controller\School\Study\Configuration;

use App\Entity\School\Study\Configuration\Subject;
use App\Entity\Security\User;
use App\Repository\School\Study\Configuration\SubjectRepository;
use App\Repository\School\Study\Configuration\SubjectTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class ImportSubjectController extends AbstractController
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

    public function __invoke(Request $request, SubjectRepository $subjectRepository, SubjectTypeRepository $subjectTypeRepository): Response
    {
        $data = $this->jsondecode();

        if(!isset($data->data))
            return throw new BadRequestHttpException('file is compulsory');

        $subjects = $data->data;

        if (!$subjects) {
            throw new BadRequestHttpException('"file" is required');
        }

        foreach ($subjects as $subject){
            //dd($import);

            $subjectCode = $subjectRepository->findOneBy(['code' => $subject->code]);
            if ($subjectCode){
                return new JsonResponse(['hydra:title' => 'the code: '.$subjectCode->getCode(). ' in line '. $subject->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $subjectName = $subjectRepository->findOneBy(['name' => $subject->name]);
            if ($subjectName){
                return new JsonResponse(['hydra:title' => 'the name: '.$subjectName->getName(). ' in line '. $subject->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $subjectType = $subjectTypeRepository->findOneBy(['name' => $subject->subjectType]);
            if(!$subjectType){
                return new JsonResponse(['hydra:title' => 'the name: '.$subject->subjectType. ' in line '. $subject->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $newSubject = new Subject();
            $newSubject->setCode($subject->code);
            $newSubject->setName($subject->name);
            $newSubject->setSubjectType($subjectType);

            $newSubject->setInstitution($this->getUser()->getInstitution());
            $newSubject->setUser($this->getUser());
            $newSubject->setYear($this->getUser()->getCurrentYear());

            $this->entityManager->persist($newSubject);
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



