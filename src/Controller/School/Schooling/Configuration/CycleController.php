<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Repository\School\Schooling\Configuration\CycleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CycleController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(CycleRepository $cycleRepository): JsonResponse
    {
        $cycles = $cycleRepository->findByInstitution($this->getUser()->getInstitution());

        $table = [];

        foreach ($cycles as $cycle){

            $table [] = [
                '@id' => "/api/cycles/".$cycle->getId(),
                '@type' => "Cycle",
                'id' => $cycle->getId(),
                'code' => $cycle->getCode(),
                'name' => $cycle->getName(),
                'position' => $cycle->getPosition(),
                'ministry' => $cycle->getMinistry() ? [
                    '@id' => "/api/ministries/".$cycle->getMinistry()->getId(),
                    '@type' => "Ministry",
                    'id' => $cycle->getMinistry()->getId(),
                    'code' => $cycle->getMinistry()->getCode(),
                    'name' => $cycle->getMinistry()->getName(),
                ] : '',
            ];
        }

        return $this->json(['hydra:member' => $table]);
    }

}
