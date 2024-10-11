<?php
namespace App\State\Processor\School\Study\Teacher\CoursePostponement;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Security\User;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class ValidateCoursePostponementProcessor implements ProcessorInterface
{
    private Request $request;
    private EntityManagerInterface $manager;
    private TimeTableModelDayCellRepository $timeTableModelDayCellRepo;

    public function __construct(private readonly ProcessorInterface $processor,
                                Request $request,
                                EntityManagerInterface $manager,
                                TimeTableModelDayCellRepository $timeTableModelDayCellRepo,
                                private readonly TokenStorageInterface $tokenStorage) {
        $this->req = $request;
        $this->manager = $manager;
        $this->timeTableModelDayCellRepo = $timeTableModelDayCellRepo;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // $modelData = json_decode($this->req->getContent(), true);
        $dayCells = $this->timeTableModelDayCellRepo->findAll();

        $courseId = $data->getCourse()->getCourse()->getId();
        $courseDate = $data->getCourse()->getDate();
        $courseStartAt = $data->getCourse()->getStartAt();
        $courseEndAt = $data->getCourse()->getEndAt();

        // Find the day cell with the same course
        $existingDayCell = null;
        foreach ($dayCells as $dayCell) {
            if (
                $dayCell->getCourse()->getId() === $courseId &&
                $dayCell->getDate() === $courseDate &&
                $dayCell->getStartAt() === $courseStartAt &&
                $dayCell->getEndAt() === $courseEndAt
            ) {
                $existingDayCell = $dayCell;
                break;
            }
        }

        if ($existingDayCell) {
            // If the course is found in day cells, update the existing day cell
            $existingDayCell->setStartAt($data->getStartAt());
            $existingDayCell->setEndAt($data->getEndAt());
            $existingDayCell->setDate($data->getDate());
            $this->manager->persist($existingDayCell);
        }

        $data->setIsValidated(true);

        $this->manager->flush();

        return $data;
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
