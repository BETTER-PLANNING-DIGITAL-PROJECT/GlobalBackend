<?php
# api/src/State/UpdateUserPasswordProcessor.php

namespace App\State\Processor\Security;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Service\UserFileUploader;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteUserProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $entityManager,
                                private readonly UserFileUploader $fileUploader)
    {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data->getPicture()){
            $this->fileUploader->deleteUpload($data->getPicture());

        }

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        // return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
