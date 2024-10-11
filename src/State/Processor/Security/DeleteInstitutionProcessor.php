<?php

namespace App\State\Processor\Security;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Service\InstitutionFileUploader;
use App\Service\UserFileUploader;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteInstitutionProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor, private readonly EntityManagerInterface $entityManager, private readonly InstitutionFileUploader $institutionFileUploader)
    {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        if ($data->getPicture()){
            $this->institutionFileUploader->deleteUpload($data->getPicture());
        }

        $this->entityManager->remove($data);
        $this->entityManager->flush();

    }
}
