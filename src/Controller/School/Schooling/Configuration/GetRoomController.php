<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\RoomRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetRoomController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, RoomRepository $roomRepository, SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository):JsonResponse
    {
        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $rooms = $roomRepository->findBy([], ['id' => 'DESC']);

            foreach ($rooms as $room){

                $requestData [] = [
                    '@id' => "/api/room/".$room->getId(),
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
        }
        else
        {
            $systemSettings = $systemSettingsRepository->findOneBy([]);
            if($systemSettings)
            {
                if($systemSettings->isIsBranches())
                {
                    $userBranches = $this->getUser()->getUserBranches();
                    foreach ($userBranches as $userBranch) {
                        $school = $schoolRepository->findOneBy(['schoolBranch' => $userBranch]);
                        if ($school) {
                            $rooms = $roomRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($rooms as $room){

                                $requestData [] = [
                                    '@id' => "/api/room/".$room->getId(),
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
                        }
                    }
                }
                else {
                    $school = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $rooms = $roomRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($rooms as $room) {
                            if ($room) {
                                $requestData [] = [
                                    '@id' => "/api/room/".$room->getId(),
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
                        }
                    }

                }
            }
        }


        return $this->json(['hydra:member' => $requestData]);
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
