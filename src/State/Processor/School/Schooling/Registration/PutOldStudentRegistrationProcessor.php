<?php

namespace App\State\Processor\School\Schooling\Registration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Controller\StudentRegistrationController;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\Session\YearRepository;
use App\Repository\Security\SystemSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PutOldStudentRegistrationProcessor implements ProcessorInterface
{
    private Request $request;
    private EntityManagerInterface $manager;
    private YearRepository $yearRepo;
    private SchoolClassRepository $classRepo;
    private ClassProgramRepository $classProgramRepo;
    private  BranchRepository $branchRepository;
    private SystemSettingsRepository $systemSettingsRepository;
    private StudentRegistrationController $studRegistrationController;

    public function __construct(private readonly ProcessorInterface                  $processor,
                                private readonly TokenStorageInterface               $tokenStorage,
                                Request                                              $request, EntityManagerInterface $manager, YearRepository $yearRepo,
                                SchoolClassRepository                                $classRepo, ClassProgramRepository $classProgramRepo,
                                StudentRegistrationController                        $studRegistrationController,
                                BranchRepository $branchRepository,
                                SystemSettingsRepository $systemSettingsRepository,
                                private readonly StudentCourseRegistrationRepository $studentCourseRegistrationRepository)
    {

        $this->req = $request;
        $this->manager = $manager;
        $this->yearRepo = $yearRepo;
        $this->classRepo = $classRepo;
        $this->classProgramRepo = $classProgramRepo;
        $this->studRegistrationController = $studRegistrationController;
        $this->branchRepository = $branchRepository;
        $this->systemSettingsRepository = $systemSettingsRepository;
    }

    public function getIdFromApiResourceId(string $apiId)
    {
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf + 1);
        return intval($id);
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // Handle the state
        $oldStudentRegistrationData = json_decode($this->req->getContent(), true);

        $previousYear = !isset($oldStudentRegistrationData['previousYear']) ? null : $this->yearRepo->find($oldStudentRegistrationData['previousYear']);
        $previousClass = !isset($oldStudentRegistrationData['previousClass']) ? null : $this->classRepo->find($oldStudentRegistrationData['previousClass']);

        $year = $data->getCurrentYear();
        $class = $data->getCurrentClass();
        $school = $class->getSchool();

        $hasInformationChanged = ($previousClass !== $class) || ($previousYear !== $year);

//        return [$hasInformationChanged,$class,$year,$previousClass,$previousYear];
        $result = null;

        if ($hasInformationChanged) {
//            $this->studRegistrationController->delete($data ->getId());

            $result = $this->processor->process($data, $operation, $uriVariables, $context);
            $user = $this->getUser();
            $institution = $user->getInstitution();

            $studentRegistration = $data;
            $classPrograms = $this->classProgramRepo->findBy(['institution' => $institution, 'class' => $class, 'year' => $year, 'school' => $school, 'isSubjectObligatory' => true]);

            foreach ($classPrograms as $classProgram) {
                $existingCourseRegistration = $this->studentCourseRegistrationRepository->findOneBy([
                    'class' => $class,
                    'classProgram' => $classProgram,
                    'StudRegistration' => $studentRegistration,
                ]);

                if (!$existingCourseRegistration) {
                    $studCourseRegistration = new StudentCourseRegistration();
                    $studCourseRegistration->setInstitution($institution);
                    $studCourseRegistration->setYear($year);
                    $systemSettings = $this->systemSettingsRepository->findOneBy([]);
                    if($systemSettings) {
                        if ($systemSettings->isIsBranches()) {
                            $data->setSchool($school);
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
        } else $result = $this->processor->process($data, $operation, $uriVariables, $context);

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
