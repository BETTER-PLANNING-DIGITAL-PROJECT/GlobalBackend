<?php

namespace App\State\Processor\School\Schooling\Registration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PostOldStudentRegistrationPerClassProcessor implements ProcessorInterface
{
    private Request $request;
    private EntityManagerInterface $manager;
    private ClassProgramRepository $classProgramRepo;
    private StudentRegistrationRepository $studRegistrationRepo;
    private  BranchRepository $branchRepository;
    private SchoolRepository $schoolRepo;
    private SystemSettingsRepository $systemSettingsRepository;

    public function __construct(private readonly ProcessorInterface $processor, Request $request,
                                private readonly TokenStorageInterface $tokenStorage,
                                BranchRepository $branchRepository,
                                SchoolRepository $schoolRepo,
                                SystemSettingsRepository $systemSettingsRepository,
                                EntityManagerInterface              $manager,
                                ClassProgramRepository $classProgramRepo, StudentRegistrationRepository $studRegistrationRepo) {

        $this->req = $request;
        $this->manager = $manager;
        $this->classProgramRepo = $classProgramRepo;
        $this->schoolRepo = $schoolRepo;
        $this->studRegistrationRepo = $studRegistrationRepo;
        $this->branchRepository = $branchRepository;
        $this->systemSettingsRepository = $systemSettingsRepository;
    }

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $studentData = json_decode($this->req->getContent(), true);
        $studentRegistrationsSelected = $studentData['studentlist'];

        $user = $this->getUser();
        $institution = $user->getInstitution();
        $year = $data->getCurrentYear();
        $class = $data->getCurrentClass();
        $school = $class->getSchool();
        $classPrograms  = $this->classProgramRepo->findBy(['institution' => $institution, 'class' => $class,  'year' => $year, 'school' => $school, 'isSubjectObligatory'=>true]);

        foreach($studentRegistrationsSelected as $studentRegistrationSelected)
        {
            // Setting to get Id
            $filter = preg_replace("/[^0-9]/", '', $studentRegistrationSelected);
            $filterId = intval($filter);
            $studentRegistrationTaken = $this->studRegistrationRepo->find($filterId);

            // New Student Registration
            $studentRegistration = new StudentRegistration();
            $studentRegistration->setStudentRegistration($studentRegistrationTaken);
            $studentRegistration->setStudent($studentRegistrationTaken->getStudent());

            $studentRegistration->setClasse($data->getClasse());
            $studentRegistration->setYear($data->getYear());

            $studentRegistration->setCurrentYear($data->getCurrentYear());
            $studentRegistration->setCurrentClass($data->getCurrentClass());
            $studentRegistration->setRegime($data->getRegime());
            $studentRegistration->setOptions($data->getOptions());
            $studentRegistration->setStatus('registered');
//            $studentRegistration-
            $systemSettings = $this->systemSettingsRepository->findOneBy([]);
            $schools = $this->schoolRepo->findOneBy(['branch' => $this->getUser()->getBranch()]);
            if($systemSettings) {
                if ($systemSettings->isIsBranches()) {
                    $studentRegistration->setSchool($school);
                } else {
                    $studentRegistration->setSchool($schools);
                }
            }
            $studentRegistration->setInstitution($institution);
            $studentRegistration->setUser($user);

            $this->manager->persist($studentRegistration);

            // Student Course Registration
            foreach ($classPrograms as $classProgram){
                $studCourseRegistration = new StudentCourseRegistration();
                $studCourseRegistration->setInstitution($institution);
                $studCourseRegistration->setYear($year);
//                $studCourseRegistration->setSchool($school);
                if($systemSettings) {
                    if ($systemSettings->isIsBranches()) {
                        $studCourseRegistration->setSchool($school);
                    } else {
                        $studCourseRegistration->setSchool($schools);
                    }
                }
                $studCourseRegistration->setClass($class);

                $studCourseRegistration->setClassProgram($classProgram);
                $studCourseRegistration->setEvaluationPeriod($classProgram->getEvaluationPeriod());
                $studCourseRegistration->setModule($classProgram->getModule());
                $studCourseRegistration->setStudRegistration($studentRegistration);
                $studCourseRegistration->setUser($user);
                $this->manager->persist($studCourseRegistration);
            }
        }

        $this->manager->flush();   
       
        return $studentRegistration;

        // if (!$data->getId()) {
        //     return $this->processor->process($data, $operation, $uriVariables, $context);
        // }

        // return $this->processor->process($data, $operation, $uriVariables, $context);
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
