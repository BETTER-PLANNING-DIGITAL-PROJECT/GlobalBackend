<?php

namespace App\State\Processor\School\Schooling\Registration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class PostResignationStudentRegistrationProcessor implements ProcessorInterface
{
    private Request $request;
    private EntityManagerInterface $manager;
    private StudentRegistrationRepository $studRegistrationRepo;

    public function __construct(
                                Request $request,
                                EntityManagerInterface $manager,
                                StudentRegistrationRepository $studRegistrationRepo) {

        $this->req = $request;
        $this->manager = $manager;
        $this->studRegistrationRepo = $studRegistrationRepo;
    }

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $studentData = json_decode($this->req->getContent(), true);
        $allStudent = $studentData['studentlist'];
    
        foreach($allStudent as $stt){
            // Setting to get Id
            $filter = preg_replace("/[^0-9]/", '', $stt);
            $filterId = intval($filter);
            $studentRegistrationTaken = $this->studRegistrationRepo->find($filterId);
            // Setting to get Id
    
            // Set the status of the student to "resigned"
            if ($studentRegistrationTaken->getStudent()->getId()) {
                $studentRegistrationTaken->getStudent()->setStatus('resigned');
                $studentRegistrationTaken->setStatus('resigned');
            }
    
            // Persist the changes
            $this->manager->persist($studentRegistrationTaken);
        }
    
        // Flush the changes to the database
        $this->manager->flush();   
    
        return $studentRegistrationTaken;
    }

}
