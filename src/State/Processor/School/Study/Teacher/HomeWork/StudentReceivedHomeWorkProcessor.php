<?php
namespace App\State\Processor\School\Study\Teacher\HomeWork;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Teacher\HomeWorkRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class StudentReceivedHomeWorkProcessor implements ProcessorInterface
{
    private EntityManagerInterface $manager;
    private HomeWorkRegistrationRepository $homeWorkRegistrationRepository;
    private StudentRepository $studentRepository;
    private StudentRegistrationRepository $studentRegistrationRepository;


    public function __construct(private readonly ProcessorInterface $processor,EntityManagerInterface $manager,
         private readonly TokenStorageInterface $tokenStorage, HomeWorkRegistrationRepository $homeWorkRegistrationRepository, StudentRepository $studentRepository,
                                StudentRegistrationRepository $studentRegistrationRepository
     ) {
        $this->manager = $manager;
        $this->homeWorkRegistrationRepository = $homeWorkRegistrationRepository;
        $this->studentRepository = $studentRepository;
        $this->studentRegistrationRepository = $studentRegistrationRepository;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $student = $this->studentRepository->findOneBy(['operator' => $this->getUser()]);
        $studentRegistration = $this->studentRegistrationRepository->findOneBy(['student' => $student]);
        $studentId = $studentRegistration->getId();

        $homeWorkRegistrations = $this->homeWorkRegistrationRepository->findBy(['student' => $studentId, 'homeWork' => $data]);

        foreach ($homeWorkRegistrations as $homeWorkRegistration) {
            $homeWorkRegistration->setIsReceived(true);
            $this->manager->flush();
        }

    }

    public function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }
}
