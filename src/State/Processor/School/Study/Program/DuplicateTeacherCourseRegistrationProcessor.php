<?php
namespace App\State\Processor\School\Study\Program;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

final class DuplicateTeacherCourseRegistrationProcessor implements ProcessorInterface
{
    private Request $request;
    private EntityManagerInterface $manager;
    private TeacherCourseRegistrationRepository $teacherCourseRegistrationRepo;

    public function __construct(private readonly ProcessorInterface $processor, Request $request, EntityManagerInterface $manager, TeacherCourseRegistrationRepository $teacherCourseRegistrationRepo,
       ) {
        $this->req = $request;
        $this->manager = $manager;
        $this->teacherCourseRegistrationRepo = $teacherCourseRegistrationRepo;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $teacherCourseRegistration = $this->teacherCourseRegistrationRepo->findOneBy(['id'=> $data->getId()]);

        $clonedTeacherCourseRegistration = clone $teacherCourseRegistration;
        $this->manager->persist($clonedTeacherCourseRegistration);

        $this->manager->flush();

        return $data;
    }
}
