<?php
namespace App\State\Processor\School\Study\Timetable;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelDayRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class ValidateTimeTableModelProcessor implements ProcessorInterface
{
    private Request $request;
    private EntityManagerInterface $manager;
    private TimeTableModelRepository $timeTableModelRepo;
    private TimeTableModelDayRepository $timeTableModelDayRepo;
    private TimeTableModelDayCellRepository $timeTableModelDayCellRepo;

    public function __construct(private readonly ProcessorInterface $processor,
                                Request $request, EntityManagerInterface $manager,
                                TimeTableModelRepository $timeTableModelRepo,
                                TimeTableModelDayCellRepository $timeTableModelDayCellRepo,
                                TimeTableModelDayRepository $timeTableModelDayRepo) {
        $this->req = $request;
        $this->manager = $manager;
        $this->timeTableModelRepo = $timeTableModelRepo;
        $this->timeTableModelDayRepo = $timeTableModelDayRepo;
        $this->timeTableModelDayCellRepo = $timeTableModelDayCellRepo;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $modelDays = $this->timeTableModelDayRepo->findBy(['model'=> $data]);

        foreach ($modelDays as $modelDay){
            $modelDayCells = $this->timeTableModelDayCellRepo->findBy(['model'=> $data]);

            foreach ($modelDayCells as $modelDayCell){
                    $modelDayCell->setIsValidated(true);
            }

            $modelDay->setIsValidated(true);

        }

        $model = $this->timeTableModelRepo->findOneBy(['id'=> $data->getId()]);
        $model->setIsValidated(true);

        $this->manager->flush();

        return $data;
    }

}
