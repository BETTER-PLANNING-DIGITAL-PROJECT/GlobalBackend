<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

final class EnableState implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
//        if (!$data instanceof Njangi) {
//            throw new \Exception('Invalid object type');
//        }
        if (!$data->getId()) {
            return $this->processor->process($data, $operation, $uriVariables, $context);
        }

        $data->setIsEnable(true);

        return $this->processor->process($data, $operation, $uriVariables, $context);
        //return new JsonResponse(data: ['Djangi enable successfully'], status: Response::HTTP_OK);
    }


}