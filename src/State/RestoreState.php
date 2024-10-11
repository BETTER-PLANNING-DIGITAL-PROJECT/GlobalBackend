<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
final class RestoreState implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$data->getId()) {
            return $this->processor->process($data, $operation, $uriVariables, $context);
        }

        $data->setIsArchive(false);

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }

}