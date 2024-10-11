<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Repository\School\Schooling\Configuration\CampusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CampusController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(CampusRepository $campusRepository): JsonResponse
    {
        $campuses = $campusRepository->findByInstitution($this->getUser()->getInstitution());

        $table = [];

        foreach ($campuses as $campus){

            $table [] = [
                '@id' => "/api/campuses/".$campus->getId(),
                '@type' => "Campus",
                'id' => $campus->getId(),
                'code' => $campus->getCode(),
                'name' => $campus->getName(),
            ];
        }

        return $this->json(['hydra:member' => $table]);
    }

}
