<?php

namespace App\State\Provider\School\Schooling\Configuration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Controller\GlobalController;
use App\Repository\School\Schooling\Configuration\RoomRepository;
use Symfony\Component\HttpFoundation\Request;

final class RoomProvider implements ProviderInterface
{
    public function __construct(private readonly RoomRepository $roomRepository,
                                private readonly GlobalController $globalController,
                                Request $request){
        $this->req = $request;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Find rooms : institution
        $rooms = $this->roomRepository->findByInstitution($this->globalController->getUser()->getInstitution());

        $table = [];

        foreach ($rooms as $room){

            $table [] = [
                'id' => $room->getId(),
                'school' => $room->getSchool() ? $room->getSchool()->getCode() : '',
                'building' => $room->getBuilding()->getName(),
                'name' => $room->getName(),
                'capacity' => $room->getCapacity(),
            ];
        }

        return array(['table' => $table]);
    }

}
