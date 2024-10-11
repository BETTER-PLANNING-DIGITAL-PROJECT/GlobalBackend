<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\School\Schooling\Registration\StudentRegistration;

final class MatriculeEditState implements ProcessorInterface
{

    private Request $request;
    private EntityManagerInterface $manager;
    private StudentRegistrationRepository $studRegistrationRepo;

    public function __construct(private readonly ProcessorInterface $processor, Request $request,
                                EntityManagerInterface              $manager,
                                StudentRegistrationRepository $studRegistrationRepo,
    StudentRepository $studentRepository) {

        $this->req = $request;
        $this->manager = $manager;
        $this->studRegistrationRepo = $studRegistrationRepo;
        $this->studentRepository = $studentRepository;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $studentData = json_decode($this->req->getContent(), true);
        $students = $this->studentRepository->findAll();

        foreach ($students as $student)
        {
            if($student->getMatricule() == $studentData['newMatricule'] && $student->getId() != $studentData['id'])
            {
                return new JsonResponse(['hydra:description' => 'A Student with this Matricule : '.$student->getMatricule().' already exist...'], 400);
            }
            else
            {
                $data->getStudent()->setMatricule($studentData['newMatricule']);
            }
        }
        $this->manager->flush();
    }

}
