<?php
namespace App\State\Processor\School\Study\Teacher\CoursePostponement;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Security\User;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PostCoursePostponementProcessor implements ProcessorInterface
{
    private EntityManagerInterface $manager;
    private TimeTableModelDayCellRepository $timeTableModelDayCellRepo;

    public function __construct(ProcessorInterface              $processor,
                                Request $request,
                                EntityManagerInterface          $manager,
                                TimeTableModelDayCellRepository $timeTableModelDayCellRepo,
                                private readonly TokenStorageInterface $tokenStorage)
    {
        $this->req = $request;
        $this->manager = $manager;
        $this->timeTableModelDayCellRepo = $timeTableModelDayCellRepo;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $modelDayCells = $this->timeTableModelDayCellRepo->findAll();

        foreach ($modelDayCells as $modelDayCell) {
            $time = $data->getStartAt()->format('H:i:s');
            $time1 = $modelDayCell->getStartAt()->format('H:i:s');
            $time2 = $data->getEndAt()->format('H:i:s');
            $time3 = $modelDayCell->getEndAt()->format('H:i:s');

            if (
                $time1 == $time &&
                $time3 == $time2 &&
                $modelDayCell->getDate() == $data->getDate()
            ) {
                return new JsonResponse(['hydra:description' => 'A Course is already Planned for this Period !'], 400);
            }
        }

        $data->setInstitution($this->getUser()->getInstitution());
        $data->setUser($this->getUser());
        $data->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($data);
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

