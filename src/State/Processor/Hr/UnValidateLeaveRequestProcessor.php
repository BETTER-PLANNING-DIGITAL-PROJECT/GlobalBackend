<?php
namespace App\State\Processor\Hr;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class UnValidateLeaveRequestProcessor implements ProcessorInterface
{
    private Request $request;
    private EntityManagerInterface $manager;

    public function __construct(private readonly ProcessorInterface $processor, Request $request, EntityManagerInterface $manager) {
        $this->req = $request;
        $this->manager = $manager;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // $modelData = json_decode($this->req->getContent(), true);

        $data->setIsValidated(false);
        $this->manager->flush();

        return $data;
    }

}
