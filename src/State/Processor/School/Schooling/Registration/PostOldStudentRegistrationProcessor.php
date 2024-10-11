<?php

namespace App\State\Processor\School\Schooling\Registration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use App\Repository\Security\Session\YearRepository;
use App\Repository\Security\SystemSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PostOldStudentRegistrationProcessor implements ProcessorInterface
{
    private Request $request;
    private EntityManagerInterface $manager;
    private ClassProgramRepository $classProgramRepo;
    private StudentRegistrationRepository $studRegistrationRepo;
      private  BranchRepository $branchRepository;
    private SchoolRepository $schoolRepo;
   private SystemSettingsRepository $systemSettingsRepository;

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                Request $request,
                                EntityManagerInterface              $manager,
                                SchoolClassRepository               $classRepo,
                                ClassProgramRepository $classProgramRepo,
                                BranchRepository $branchRepository,
                                SchoolRepository $schoolRepo,
                                SystemSettingsRepository $systemSettingsRepository,
                                StudentRegistrationRepository $studRegistrationRepo,
                                YearRepository$yearRepo) {

        $this->req = $request;
        $this->manager = $manager;
        $this->yearRepo = $yearRepo;
        $this->schoolRepo = $schoolRepo;
        $this->classRepo = $classRepo;
        $this->classProgramRepo = $classProgramRepo;
        $this->studRegistrationRepo = $studRegistrationRepo;
        $this->branchRepository = $branchRepository;
        $this->systemSettingsRepository = $systemSettingsRepository;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $year = $data->getCurrentYear();
        $class = $data->getCurrentClass();
        $school = $class->getSchool();
        $classPrograms  = $this->classProgramRepo->findBy(['class' => $class,  'year' => $year, 'school' => $school, 'isSubjectObligatory'=>true]);

        $data->setStudent($data->getStudentRegistration()->getStudent());
//        $data->setSchool($school);
        $systemSettings = $this->systemSettingsRepository->findOneBy([]);
        $schools = $this->schoolRepo->findOneBy(['branch' => $this->getUser()->getBranch()]);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $data->setSchool($school);
            } else {
                $data->setSchool($schools);
            }
        }
        $data->setStatus('registered');
        $user = $this->getUser();
        $institution = $user->getInstitution();
        $data->setInstitution($institution);
        $data->setUser($user);
        $result = $this->processor->process($data, $operation, $uriVariables, $context);
        $studRegistration = $this->studRegistrationRepo->findOneBy([], ['id' => 'DESC']);

        foreach ($classPrograms as $classProgram){
            $studCourseRegistration = new StudentCourseRegistration();
            $studCourseRegistration->setInstitution($institution);
            $studCourseRegistration->setYear($year);
            $studCourseRegistration->setClass($class);
            if($systemSettings) {
                if ($systemSettings->isIsBranches()) {
                    $data->setSchool($school);
                } else {
                    $data->setSchool($schools);
                }
            }
            $studCourseRegistration->setClassProgram($classProgram);
            $studCourseRegistration->setEvaluationPeriod($classProgram->getEvaluationPeriod());
            $studCourseRegistration->setModule($classProgram->getModule());
            $studCourseRegistration->setStudRegistration($studRegistration);
            $studCourseRegistration->setUser($user);
            $this->manager->persist($studCourseRegistration);
        }

        $this->manager->flush();

        return $result;
    }

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
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
