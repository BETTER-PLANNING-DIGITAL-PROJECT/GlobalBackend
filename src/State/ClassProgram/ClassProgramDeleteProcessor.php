<?php

namespace App\State\ClassProgram;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Configuration\SubjectRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ClassProgramDeleteProcessor implements ProcessorInterface
{
    private Request $req;
    private EntityManagerInterface $manager;
    private InstitutionRepository $institutionRepo;
    private SchoolClassRepository $classRepo;
    private SchoolRepository $schoolRepo;
    private SubjectRepository $subjectRepo;
    private ClassProgramRepository $classProgramRepo;
    private StudentRegistrationRepository $studRegistrationRepo;
    private StudentCourseRegistrationRepository $studCourseRegRepo;

    /**
     * @param Request $req
     * @param EntityManagerInterface $manager
     * @param InstitutionRepository $institutionRepo
     * @param SchoolClassRepository $classRepo
     * @param SchoolRepository $schoolRepo
     * @param SubjectRepository $subjectRepo
     * @param ClassProgramRepository $classProgramRepo
     * @param StudentRegistrationRepository $studRegistrationRepo
     * @param StudentCourseRegistrationRepository $studCourseRegRepo
     */
    public function __construct(private readonly ProcessorInterface $processor, Request $req, EntityManagerInterface $manager, InstitutionRepository $institutionRepo, SchoolClassRepository $classRepo, SubjectRepository $subjectRepo, ClassProgramRepository $classProgramRepo, StudentRegistrationRepository $studRegistrationRepo, StudentCourseRegistrationRepository $studCourseRegRepo, SchoolRepository $schoolRepo)
    {
        $this->req = $req;
        $this->manager = $manager;
        $this->institutionRepo = $institutionRepo;
        // $this->yearRepo = $yearRepo;
        $this->classRepo = $classRepo;
        $this->schoolRepo = $schoolRepo;
        $this->subjectRepo = $subjectRepo;
        $this->classProgramRepo = $classProgramRepo;
        $this->studRegistrationRepo = $studRegistrationRepo;
        $this->studCourseRegRepo = $studCourseRegRepo;
    }

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $classProgramData = json_decode($this->req->getContent(), true);
        // Handle the state

//        dd($data, $classProgramData);
        $institution = $data->getInstitution();
        $year = $data->getYear();
        $class = !isset($classProgramData['class']) ? null : $this->classRepo->find($this->getIdFromApiResourceId($classProgramData['class']));
        $subject = $data->getSubject();
        $school = $data->getSchool();


        $previousSchool = !isset($classProgramData['previousSchool']) ? null : $this->schoolRepo->find($classProgramData['previousSchool']);
        $previousSubject = !isset($classProgramData['previousSubject']) ? null : $this->subjectRepo->find($classProgramData['previousSubject']);
        // $previousYear = !isset($classProgramData['previousYear']) ? null : $this->yearRepo->find($classProgramData['previousYear']);

        $classProgram = $this->classProgramRepo->find($data->getId());
        $previousClass = !isset($classProgramData['previousClass']) ? null : $this->classRepo->find($classProgramData['previousClass']);
        $previousIsSubjectObligatory = !isset($classProgramData['previousIsSubjectObligatory']) ? null: $classProgramData['previousIsSubjectObligatory'];
        $isSubjectObligatory = $data->isIsSubjectObligatory();
        $isNewIsSubjectObligatory = $isSubjectObligatory !== $previousIsSubjectObligatory;
        $result = null;

        // $hasInformationsChanged = ($previousClass !== $class)  || ($previousSchool !== $school) || ($previousYear !== $year)|| ($previousSubject !== $subject);

        if ($hasInformationsChanged){
            $this->deleteStudCourseReg($classProgram);
            $result = $this->processor->process($data, $operation, $uriVariables, $context);
            if ($isSubjectObligatory) $this->createStudCourseReg($data, $year, $school, $institution, $subject);
        }
        else {
            $result = $this->processor->process($data, $operation, $uriVariables, $context);
            if ($isNewIsSubjectObligatory){
                if ($isSubjectObligatory)  $this->createStudCourseReg($data, $year, $school, $institution, $subject);
                else $this->deleteStudCourseReg($classProgram);
            }
        }

        $this->manager->flush();
        return $result;
    }

    /**
     * @param mixed $data
     * @param mixed $year
     * @param mixed $school
     * @param mixed $institution
     * @param mixed $subject
     * @return void
     */
    public function createStudCourseReg(mixed $data, mixed $year, mixed $school, mixed $institution, mixed $subject): void
    {
        $classProgram = $data;  //$this->classProgramRepo->findOneBy([], ['id' => 'DESC']);
        $class = $classProgram->getClass();

        $studRegistrations = $this->studRegistrationRepo->findByClassAndYear($class, $year, $school);
        foreach ($studRegistrations as $studRegistration) {
            $studCourseRegistration = new StudentCourseRegistration();
            $studCourseRegistration->setInstitution($institution);
            // $studCourseRegistration->setYear($year);
            $studCourseRegistration->setClass($class);
            $studCourseRegistration->setSubject($subject);
            $studCourseRegistration->setClassProgram($classProgram);
            $studCourseRegistration->setStudRegistration($studRegistration);
            $studCourseRegistration->setSchool($school);
            $this->manager->persist($studCourseRegistration);
        }
    }

    /**
     * @param \App\Entity\School\Study\Program\ClassProgram|null $classProgram
     * @return void
     */
    public function deleteStudCourseReg(?\App\Entity\School\Study\Program\ClassProgram $classProgram): void
    {
        $studCourseRegs = $this->studCourseRegRepo->findBy(['classProgram' => $classProgram]);
//            dd($classProgram, $studCourseRegs);
        foreach ($studCourseRegs as $studCourseReg) $this->manager->remove($studCourseReg);
//        $this->manager->remove($classProgram);
        $this->manager->flush();
    }
}
