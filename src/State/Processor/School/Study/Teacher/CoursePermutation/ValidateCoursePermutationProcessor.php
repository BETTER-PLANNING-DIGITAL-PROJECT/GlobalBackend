<?php
namespace App\State\Processor\School\Study\Teacher\CoursePermutation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\School\Study\Teacher\CoursePermutationRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class ValidateCoursePermutationProcessor implements ProcessorInterface
{
    private Request $request;
    private EntityManagerInterface $manager;
    private TimeTableModelDayCellRepository $timeTableModelDayCellRepo;
    private CoursePermutationRepository $requestCoursePermutationRepo;

    public function __construct(private readonly ProcessorInterface $processor,
                                Request $request, EntityManagerInterface $manager,
                                CoursePermutationRepository $requestCoursePermutationRepo,
                                TimeTableModelDayCellRepository     $timeTableModelDayCellRepo) {
        $this->req = $request;
        $this->manager = $manager;
        $this->timeTableModelDayCellRepo = $timeTableModelDayCellRepo;
        $this->requestCoursePermutationRepo = $requestCoursePermutationRepo;
    }


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // $modelData = json_decode($this->req->getContent(), true);

        $dayCell = $this->requestCoursePermutationRepo->findOneBy(['id' => $data]);
        $data->setIsValidated(true);

        /*$data->setInstitution($this->getUser()->getInstitution());
        $data->setUser($this->getUser());
        $data->setYear($this->getUser()->getCurrentYear());*/

        $dayCell1 = $this->timeTableModelDayCellRepo->findOneBy(['id' => $dayCell->getCourse()->getId()]);
        $dayCell2 = $this->timeTableModelDayCellRepo->findOneBy(['id' => $dayCell->getOtherCourse()->getId()]);

        // Store the original data of dayCell1
        $originalDayCell1 = clone $dayCell1;

        // Set dayCell1's data to dayCell2's data
        $dayCell1->setCourse($dayCell2->getCourse());
        $dayCell1->setTeacher($dayCell2->getTeacher());

        // Set dayCell2's data to dayCell1's original data
        $dayCell2->setCourse($originalDayCell1->getCourse());
        $dayCell2->setTeacher($originalDayCell1->getTeacher());

        $this->manager->persist($dayCell1);
        $this->manager->persist($dayCell2);
        $this->manager->flush();

        return $data;
    }

}
