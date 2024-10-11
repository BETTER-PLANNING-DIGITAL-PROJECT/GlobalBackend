<?php
namespace App\State\Processor\School\Study\Timetable;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelDayRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class DuplicateTimeTableModelProcessor implements ProcessorInterface
{
    private EntityManagerInterface $manager;
    private TimeTableModelRepository $timeTableModelRepo;
    private TimeTableModelDayRepository $timeTableModelDayRepo;
    private TimeTableModelDayCellRepository $timeTableModelDayCellRepo;

    public function __construct(private readonly ProcessorInterface $processor,
                                Request $request, EntityManagerInterface $manager,
                                TimeTableModelRepository $timeTableModelRepo,
                                TimeTableModelDayCellRepository $timeTableModelDayCellRepo,
                                TimeTableModelDayRepository $timeTableModelDayRepo) {
        $this->manager = $manager;
        $this->timeTableModelRepo = $timeTableModelRepo;
        $this->timeTableModelDayRepo = $timeTableModelDayRepo;
        $this->timeTableModelDayCellRepo = $timeTableModelDayCellRepo;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $modelDays = $this->timeTableModelDayRepo->findBy(['model'=> $data]);

        $model = $this->timeTableModelRepo->findOneBy(['id'=> $data->getId()]);

        $clonedModel = clone $model;
        $clonedModel->setName($model->getName() . " (new)"); // Add "(new)" to the name
        $this->manager->persist($clonedModel);

        foreach ($modelDays as $modelDay){
            $clonedModelDay = clone $modelDay;
            $clonedModelDay->setModel($clonedModel);
            $clonedModelDay->setDay($modelDay->getDay() . " (new)");
            $this->manager->persist($clonedModelDay);

            $modelDayCells = $this->timeTableModelDayCellRepo->findBy(['model'=> $data]);

            foreach ($modelDayCells as $modelDayCell){
                $clonedModelDayCell = clone $modelDayCell;
                $clonedModelDayCell->setModelDay($clonedModelDay);
                $this->manager->persist($clonedModelDayCell);
            }
        }

        $this->manager->flush();

        return $data;
    }

}
