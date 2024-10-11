<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

final class TimeTableModelDayCellPostState implements ProcessorInterface
{

    private EntityManagerInterface $manager;
    private InstitutionRepository $institutionRepo;
    private TimeTableModelDayCellRepository $timeTableModelDayCellRepo;


    public function __construct(ProcessorInterface $processor, Request $request,
                                EntityManagerInterface $manager,
                                InstitutionRepository $institutionRepo,
                                TimeTableModelDayCellRepository $timeTableModelDayCellRepo)
    {
        $this->req = $request;
        $this->manager = $manager;
        $this->institutionRepo = $institutionRepo;
        $this->timeTableModelDayCellRepo = $timeTableModelDayCellRepo;
    }



    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $institution = $this->institutionRepo->findOneBy(['id' => 1]);
        $modelDayCells = $this->timeTableModelDayCellRepo->findAll();

        $data->setInstitution($institution);

        $principalRoom = $data->getCourse()->getPrincipalRoom();

        $data->setRoom($principalRoom);
        $data->setModel($data->getModelDay()->getModel());

        foreach ($modelDayCells as $modelDayCell) {
            $time = $data->getStartAt()->format('H:i:s');
            $time1 = $modelDayCell->getStartAt()->format('H:i:s');
            $time2 = $data->getEndAt()->format('H:i:s');
            $time3 = $modelDayCell->getEndAt()->format('H:i:s');

            if (
                $modelDayCell->getModelDay()->getDay() == $data->getModelDay()->getDay() &&
                $time1 == $time &&
                $time3 == $time2 &&
                $modelDayCell->getCourse()->getNameuvc() == $data->getCourse()->getNameuvc() &&
                $modelDayCell->getTeacher()->getId() == $data->getTeacher()->getId() &&
                $modelDayCell->getRoom()->getId() == $data->getRoom()->getId()
            ) {
                throw new \Exception('A TimeTableModelDayCell with the same parameters already exists.');
            }

            if (
                $modelDayCell->getModelDay()->getDay() == $data->getModelDay()->getDay() &&
                $time1 == $time &&
                $time3 == $time2
            ) {
                throw new \Exception('This time slot is already occupied.');
            }

            if (
                $modelDayCell->getModelDay()->getDay() == $data->getModelDay()->getDay() &&
                $time1 == $time &&
                $time3 == $time2
            ) {
                if ($modelDayCell->getCourse()->getNameuvc() == $data->getCourse()->getNameuvc()) {
                    throw new \Exception('A course cannot be assigned to two different rooms at the same time.');
                }
                if ($modelDayCell->getTeacher()->getId() == $data->getTeacher()->getId()) {
                    throw new \Exception('A teacher cannot be assigned to two different courses at the same time.');
                }
                if ($modelDayCell->getRoom()->getId() == $data->getRoom()->getId()) {
                    throw new \Exception('A room cannot be assigned to two different courses at the same time.');
                }
            }
        }

        $this->manager->persist($data);
        $this->manager->flush();

        return $data;
    }
}

