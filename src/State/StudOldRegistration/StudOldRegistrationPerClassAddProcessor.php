<?php

namespace App\State\StudOldRegistration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Repository\School\Study\Program\ClassProgramRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StudOldRegistrationPerClassAddProcessor implements ProcessorInterface
{
    private Request $request;
    private EntityManagerInterface $manager;
    // private YearRepository $yearRepo;
    private InstitutionRepository $institutionRepo;
    private SchoolClassRepository $classRepo;
    private ClassProgramRepository $classProgramRepo;
    private StudentRegistrationRepository $studRegistrationRepo;

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,Request $request,
                                EntityManagerInterface $manager, InstitutionRepository $institutionRepo,
                                SchoolClassRepository $classRepo, ClassProgramRepository $classProgramRepo, StudentRegistrationRepository $studRegistrationRepo) {

        $this->req = $request;
        $this->manager = $manager;
        // $this->yearRepo = $yearRepo;
        $this->institutionRepo = $institutionRepo;
        $this->classRepo = $classRepo;
        $this->classProgramRepo = $classProgramRepo;
        $this->studRegistrationRepo = $studRegistrationRepo;
    }

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // Handle the state
        $studOldRegistrationData = json_decode($this->req->getContent(), true);
        // dd($studOldRegistrationData, $data);

        $institution = $this->institutionRepo->find(1);
        $year = $data->getCurrentYear();
        $class = $data->getCurrentClass();
        $school = $class->getSchool();
        $classPrograms  = $this->classProgramRepo->findBy(['institution' => $institution, 'class' => $class,  'year' => $year, 'school' => $school]);

        // dd($institution, $year, $class, $school, $classPrograms);

        $result = $this->processor->process($data, $operation, $uriVariables, $context);
        $studRegistration = $this->studRegistrationRepo->findOneBy([], ['id' => 'DESC']);

        foreach ($classPrograms as $classProgram){
            $studCourseRegistration = new StudentCourseRegistration();
            $studCourseRegistration->setInstitution($institution);
            $studCourseRegistration->setYear($year);
            $studCourseRegistration->setClass($class);
            $subject = $classProgram->getSubject();
            $studCourseRegistration->setSubject($subject);
            $studCourseRegistration->setClassProgram($classProgram);
            $studCourseRegistration->setStudRegistration($studRegistration);
            $this->manager->persist($studCourseRegistration);
        }

        $this->manager->flush();

        return $result;
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
