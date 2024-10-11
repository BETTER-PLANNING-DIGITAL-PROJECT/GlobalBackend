<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
final class ArchiveState implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$data->getId()) {
            return $this->processor->process($data, $operation, $uriVariables, $context);
        }

        $data->setIsArchive(true);

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }

}