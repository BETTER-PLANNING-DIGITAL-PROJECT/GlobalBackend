<?php
namespace App\State\Processor\School\Study\Score;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class StartTeacherCourseProcessor implements ProcessorInterface
{
    private EntityManagerInterface $manager;

    public function __construct(ProcessorInterface $processor, Request $request, EntityManagerInterface $manager) {
        $this->req = $request;
        $this->manager = $manager;
    }
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $data->setCourseStartTime(new \DateTime('now'));

        $this->manager->flush();
        return $data;
    }
}
