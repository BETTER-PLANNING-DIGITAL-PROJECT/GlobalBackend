<?php
namespace App\State\Processor\School\Study\Timetable;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class SwapTimeTableModelDayCellProcessor implements ProcessorInterface
{
    private EntityManagerInterface $manager;
    private TimeTableModelDayCellRepository $timeTableModelDayCellRepo;
    private TimeTableModelRepository $timeTableModelRepository;

    public function __construct(private readonly ProcessorInterface $processor,
                                Request $request, EntityManagerInterface $manager,
                                TimeTableModelDayCellRepository $timeTableModelDayCellRepo,
                                TimeTableModelRepository $timeTableModelRepository) {
        $this->req = $request;
        $this->manager = $manager;
        $this->timeTableModelDayCellRepo = $timeTableModelDayCellRepo;
        $this->timeTableModelRepository = $timeTableModelRepository;
    }


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // $modelDayCellData = json_decode($this->req->getContent(), true);

        $modelDayCells = $this->timeTableModelDayCellRepo->findOneBy(['id'=> $data->getId()]);

        foreach ($modelDayCells as $modelDayCell){
            $modelDayCellByModelDays = $this->timeTableModelDayCellRepo->findBy(['modelDay'=> $modelDayCell]);

            foreach ($modelDayCellByModelDays as $modelDayCellByModelDay){
                $modelDayCellByModelDay->setIsValidated(true);
            }
        }

        $model = $this->timeTableModelRepository->findOneBy(['id'=> $data->getId()]);
        $model->setIsValidated(true);

        $this->manager->flush();

        return $data;

    }
}
