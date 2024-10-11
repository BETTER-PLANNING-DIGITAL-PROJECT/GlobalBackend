<?php
namespace App\State\Processor\School\Study\Timetable;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class UnPublishTimeTableModelProcessor implements ProcessorInterface
{
    private EntityManagerInterface $manager;

    public function __construct(private readonly ProcessorInterface $processor, EntityManagerInterface $manager) {
        $this->manager = $manager;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $data->setIsPublished(false);

        $this->manager->flush();

        return $data;

    }

}
