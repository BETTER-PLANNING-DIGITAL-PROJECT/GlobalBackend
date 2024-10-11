<?php

namespace App\State\Processor\School\Study\Timetable;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelDayRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class DeleteTimeTableModelProcessor implements ProcessorInterface
{
    private TimeTableModelRepository $timeTableModelRepo;
    private TimeTableModelDayRepository $timeTableModelDayRepo;
    private TimeTableModelDayCellRepository $timeTableModelDayCellRepo;

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $entityManager,
                                TimeTableModelDayRepository $timeTableModelDayRepo,
                                TimeTableModelDayCellRepository $timeTableModelDayCellRepo,
                                TimeTableModelRepository $timeTableModelRepo){

        $this->timeTableModelDayRepo = $timeTableModelDayRepo;
        $this->timeTableModelDayCellRepo = $timeTableModelDayCellRepo;
        $this->timeTableModelRepo = $timeTableModelRepo;
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $modelDays = $this->timeTableModelDayRepo->findBy(['model'=> $data]);

        foreach ($modelDays as $modelDay){
            $modelDayCells = $this->timeTableModelDayCellRepo->findBy(['modelDay'=> $modelDay]);

            foreach ($modelDayCells as $modelDayCell){
                $this->entityManager->remove($modelDayCell);
            }
            $this->entityManager->remove($modelDay);
        }

        $model = $this->timeTableModelRepo->findOneBy(['id'=> $data->getId()]);

        $this->entityManager->remove($model);
        $this->entityManager->flush();

       // return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
