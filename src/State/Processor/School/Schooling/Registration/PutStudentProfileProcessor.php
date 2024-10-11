<?php

namespace App\State\Processor\School\Schooling\Registration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\School\Schooling\Registration\StudentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class PutStudentProfileProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,private readonly StudentRepository $studentRepository)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // Handle the state
        $matricule = $data->getMatricule();
        $othermatricule = $data->getOthermatricule();
        $internalmatricule = $data->getInternalmatricule();

        // Check if student matricule already exist
        $student = $this->studentRepository->findOneBy(['matricule' => $matricule]);
        if($student && ($student != $data))
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This matricule already exist.'], 400);
        }

        // Check if student other matricule already exist
        $student = $this->studentRepository->findOneBy(['othermatricule' => $othermatricule]);
        if($othermatricule != null && $student && ($student != $data))
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This ministry matricule already exist.'], 400);
        }

        // Check if student internal matricule already exist
        $student = $this->studentRepository->findOneBy(['internalmatricule' => $internalmatricule]);
        if($internalmatricule != null && $student && ($student != $data))
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This internal matricule already exist.'], 400);
        }
        $result = $this->processor->process($data, $operation, $uriVariables, $context);
        return $result;
    }
}
