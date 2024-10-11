<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\School\Schooling\Configuration\Cycle;
use App\Repository\School\Schooling\Configuration\CycleRepository;
use App\Repository\Setting\Institution\MinistryRepository;
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
final class ImportCycleController extends AbstractController
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

    public function __invoke(Request $request, CycleRepository $cycleRepository, MinistryRepository $ministryRepository): Response
    {
        $data = $this->jsondecode();

        if(!isset($data->data))
            return throw new BadRequestHttpException('file is compulsory');

        $cycles = $data->data;

        if (!$cycles) {
            throw new BadRequestHttpException('"file" is required');
        }

        foreach ($cycles as $cycle){

            $cycleCode = $cycleRepository->findOneBy(['code' => $cycle->code]);
            if ($cycleCode){
                return new JsonResponse(['hydra:title' => 'The code: '.$cycleCode->getCode(). ' in line '. $cycle->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $cycleName = $cycleRepository->findOneBy(['name' => $cycle->name]);
            if ($cycleName){
                return new JsonResponse(['hydra:title' => 'The name: '.$cycleName->getName(). ' in line '. $cycle->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $ministry = $ministryRepository->findOneBy(['name' => $cycle->ministry]);
            if(!$ministry){
                return new JsonResponse(['hydra:title' => 'The name: '.$cycle->ministry. ' in line '. $cycle->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $newCycle = new Cycle();
            $newCycle->setCode($cycle->code);
            $newCycle->setName($cycle->name);
            $newCycle->setPosition($cycle->position);
            $newCycle->setMinistry($ministry);

            $newCycle->setInstitution($this->getUser()->getInstitution());
            $newCycle->setUser($this->getUser());
            $newCycle->setUser($this->getUser()->getCurrentYear());

            $this->entityManager->persist($newCycle);
        }

        $this->entityManager->flush();

        return new Response(null, Response::HTTP_OK);

        //return $this->json(['hydra:member' => $this->roleResultName($profile->getName())]);
    }

}



