<?php
namespace App\State\Processor\School\Study\Score;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelRepository;
use Doctrine\ORM\EntityManagerInterface;

final class ValidateTeacherCourseScoreProcessor implements ProcessorInterface
{
    private EntityManagerInterface $manager;
    private TeacherCourseRegistrationRepository $teacherCourseRegistrationRepo;
    private TimeTableModelDayCellRepository $timeTableModelDayCellRepo;

    public function __construct(ProcessorInterface $processor, EntityManagerInterface $manager, TimeTableModelRepository $timeTableModelRepo,
        TimeTableModelDayCellRepository $timeTableModelDayCellRepo,TeacherCourseRegistrationRepository $teacherCourseRegistrationRepo) {
        $this->manager = $manager;
        $this->timeTableModelRepo = $timeTableModelRepo;
        $this->teacherCourseRegistrationRepo = $teacherCourseRegistrationRepo;
        $this->timeTableModelDayCellRepo = $timeTableModelDayCellRepo;
    }


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // set the default timezone to use.
        date_default_timezone_set('UTC');

        // Trouver toutes les cellules du jour du modèle de temps par 'modelDay'
        $modelDayCell = $this->timeTableModelDayCellRepo->findOneBy(['id'=> $data->getId()]);

        $datetime1 = date_create($modelDayCell->getStartAt()->format('Y-m-d H:i'));
        $datetime2 = date_create($modelDayCell->getEndAt()->format('Y-m-d H:i'));
        $interval = date_diff($datetime1, $datetime2);

        $registrationCourse = $this->teacherCourseRegistrationRepo->findOneBy(['course' => $modelDayCell->getCourse(), 'teacher'=> $modelDayCell->getTeacher()]);
        $registrationCourse->setHourlyRateExhausted($registrationCourse->getHourlyRateExhausted() + $interval->h);

        // Valider chaque cellule du jour du modèle de temps
        $modelDayCell->setIsScoreValidated(true);
        $this->manager->flush();

        return $data;
    }
}
