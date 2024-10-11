<?php
# api/src/State/UpdateUserPasswordProcessor.php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class StudentRegistrationDeleteProcessor implements ProcessorInterface
{
    private StudentRepository $studentRepo;

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $entityManager,
                                private readonly FileUploader $fileUploader,
                                StudentRepository $studentRepo,
                                Request $request, EntityManagerInterface $manager)
    {

        $this->req = $request;
        $this->manager = $manager;
        $this->studentRepo = $studentRepo;
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data->getStudent()->getPicture()) {
            $this->fileUploader->deleteUpload($data->getStudent()->getPicture());
        }

        // We will check if there are mark on the registration before delete
//      if($data->getHasSchoolMark()){
//            return new JsonResponse(['hydra:description' => 'This registration has a mark. You can not delete!'], 400);
//        }

        // Remove student object
        $this->entityManager->remove($data->getStudent());

        // Remove registration itself
        $this->entityManager->remove($data);

        // Persist changes in a single flush
        $this->entityManager->flush();
    }


}
