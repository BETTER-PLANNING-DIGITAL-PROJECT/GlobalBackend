<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Repository\School\Schooling\Configuration\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class RoomController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(RoomRepository $roomRepository): JsonResponse
    {
        $rooms = $roomRepository->findByInstitution($this->getUser()->getInstitution());

        $table = [];

        foreach ($rooms as $room){

            $table [] = [
                '@id' => "/api/rooms/".$room->getId(),
                '@type' => "Room",
                'id' => $room->getId(),
                'school' => $room->getSchool() ? [
                    '@id' => "/api/schools/".$room->getSchool()->getId(),
                    '@type' => "School",
                    'id' => $room->getSchool()->getId(),
                    'code' => $room->getSchool()->getCode(),
                    'name' => $room->getSchool()->getName(),
                    'email' => $room->getSchool()->getEmail(),
                    'phone' => $room->getSchool()->getPhone(),
                    'postalCode' => $room->getSchool()->getPostalCode(),
                    'city' => $room->getSchool()->getCity(),
                    'address' => $room->getSchool()->getAddress(),
                    'manager' => $room->getSchool()->getManager(),
                    'managerType' => $room->getSchool()->getManagerType() ? [
                        '@id' => "/api/manager_types/".$room->getSchool()->getManagerType()->getId(),
                        '@type' => "ManagerType",
                        'id' => $room->getSchool()->getManagerType()->getId(),
                        'code' => $room->getSchool()->getManagerType()->getCode(),
                        'name' => $room->getSchool()->getManagerType()->getName(),
                    ] : '',
                ] : '',
                'building' => $room->getBuilding() ? [
                    '@id' => "/api/buildings/".$room->getBuilding()->getId(),
                    '@type' => "Building",
                    'id' => $room->getBuilding()->getId(),
                    'campus' => [
                        '@id' => "/api/campuses/".$room->getBuilding()->getCampus()->getId(),
                        '@type' => "Campus",
                        'id' => $room->getBuilding()->getCampus()->getId(),
                        'code' => $room->getBuilding()->getCampus()->getCode(),
                        'name' => $room->getBuilding()->getCampus()->getName(),
                    ],
                    'name' => $room->getBuilding()->getName(),
                ] : '',
                'name' => $room->getName(),
                'capacity' => $room->getCapacity(),
            ];
        }

        return $this->json(['hydra:member' => $table]);
    }

}
