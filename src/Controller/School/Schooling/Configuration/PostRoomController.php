<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\School\Schooling\Configuration\Room;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\BuildingRepository;
use App\Repository\School\Schooling\Configuration\RoomRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PostRoomController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data, Request $request, RoomRepository $roomRepository, BranchRepository $branchRepository,
    SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository, BuildingRepository $buildingRepository)
    {
        $requestData = json_decode($request->getContent(), true);
        $school = !isset($requestData['school']) ? null : $schoolRepository->find($this->getIdFromApiResourceId($requestData['school']));

        $name = $requestData['name'];

        $systemSettings = $systemSettingsRepository->findOneBy([]);
        $schools = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);

        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $duplicateCheckName = $roomRepository->findOneBy(['name' => $name, 'school' => $school, 'year' => $this->getUser()->getCurrentYear()]);
            } else {
                $duplicateCheckName = $roomRepository->findOneBy(['name' => $name, 'school' => $schools, 'year' => $this->getUser()->getCurrentYear()]);
            }
            if ($duplicateCheckName) {
                return new JsonResponse(['hydra:description' => 'This name already exists in this school.'], 400);
            }
        }

        $room = new Room();
        $building = !isset($requestData['building']) ? null : $buildingRepository->find($this->getIdFromApiResourceId($requestData['building']));
        $room->setBuilding($building);
        $room->setName($requestData['name']);
        $room->setCapacity($requestData['capacity']);
        $schools = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
//        dd($schools);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $room->setSchool($school);
            } else {
                $room->setSchool($schools);
            }
        }

        $room->setInstitution($this->getUser()->getInstitution());
        $room->setUser($this->getUser());
        $room->setYear($this->getUser()->getCurrentYear());

        $roomRepository->save($room);

        return $room;
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
