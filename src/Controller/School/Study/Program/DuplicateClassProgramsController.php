<?php

namespace App\Controller\School\Study\Program;

use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Security\Session\YearRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class DuplicateClassProgramsController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface         $tokenStorage,
                                private readonly YearRepository                $yearRepository,
                                private readonly SchoolClassRepository         $classRepository,
                                private readonly EvaluationPeriodRepository    $evaluationPeriodRepository,
                                private readonly StudentRegistrationRepository $studentRegistrationRepository,
                                private readonly ClassProgramRepository        $classProgramRepository,
                                private readonly Request                       $request,
                                private readonly EntityManagerInterface        $manager,
                                private readonly StudentCourseRegistrationRepository $studentCourseRegistrationRepository)
    {
    }

    public function __invoke(mixed $data, Request $request): JsonResponse|ClassProgram
    {
        $requestedData = json_decode($request->getContent(), true);

        $year = $this->yearRepository->find($this->getIdFromApiResourceId($requestedData['year']));
        $newYear = $this->yearRepository->find($this->getIdFromApiResourceId($requestedData['newYear']));

        $class = $this->classRepository->find($this->getIdFromApiResourceId($requestedData['class']));
        $newClass = $this->classRepository->find($this->getIdFromApiResourceId($requestedData['newClass']));

        $evaluationPeriod = $this->evaluationPeriodRepository->find($this->getIdFromApiResourceId($requestedData['evaluationPeriod']));
        $newEvaluationPeriod = $this->evaluationPeriodRepository->find($this->getIdFromApiResourceId($requestedData['newEvaluationPeriod']));

        $existingDuplicate = $this->classProgramRepository->findOneBy([
            'year' => $newYear,
            'class' => $newClass,
            'evaluationPeriod' => $newEvaluationPeriod,
        ]);

        if ($existingDuplicate) {
            return new JsonResponse(['hydra:description' => 'Duplicate ClassProgram already exists'], 409);
        }

        $classPrograms = $requestedData['classProgram'] ?? [];

        foreach ($classPrograms ?: $this->classProgramRepository->findBy([
            'year' => $year,
            'class' => $class,
            'evaluationPeriod' => $evaluationPeriod,
        ]) as $existingClassProgram) {
            $newClassProgram = new ClassProgram();
            $newClassProgram->setYear($newYear);
            $newClassProgram->setSchool($existingClassProgram->getSchool());
            $newClassProgram->setClass($existingClassProgram->getClass());
            $newClassProgram->setClass($newClass);
            $newClassProgram->setSubject($existingClassProgram->getSubject());
            $newClassProgram->setModule($existingClassProgram->getModule());
            $newClassProgram->setEvaluationPeriod($newEvaluationPeriod);
            $newClassProgram->setCodeuvc($existingClassProgram->getCodeuvc());
            $newClassProgram->setNameuvc($existingClassProgram->getNameuvc());
            $newClassProgram->setPosition($existingClassProgram->getPosition());
            $newClassProgram->setCoeff($existingClassProgram->getCoeff());
            $newClassProgram->setValidationBase($existingClassProgram->getValidationBase());
            $newClassProgram->setPrincipalRoom($existingClassProgram->getPrincipalRoom());
//                $newClassProgram->setNature($existingClassProgram->getNature());
            $newClassProgram->setIsSubjectObligatory($existingClassProgram->isIsSubjectObligatory());
            $newClassProgram->setInstitution($existingClassProgram->getInstitution());
            $newClassProgram->setUser($this->getUser());

            $this->manager->persist($newClassProgram);

            if ($newClassProgram->isIsSubjectObligatory()) {
                $studentRegistrations = $this->studentRegistrationRepository->findBy(['currentYear' => $newYear, 'currentClass' => $newClass, 'school' => $newClassProgram->getSchool()->getId()]);

                if ($studentRegistrations) {
                    foreach ($studentRegistrations as $studentRegistration) {

                        $existingStudentCourseRegistration = $this->studentCourseRegistrationRepository->findOneBy([
                            'evaluationPeriod' => $newEvaluationPeriod,
                            'class' => $newClass,
                            'classProgram' => $newClassProgram,
                            'StudRegistration' => $studentRegistration,
                        ]);

                        if (!$existingStudentCourseRegistration) {
                            $studentCourseRegistration = new StudentCourseRegistration();
                            $studentCourseRegistration->setClass($newClass);
                            $studentCourseRegistration->setClassProgram($newClassProgram);
                            $studentCourseRegistration->setStudRegistration($studentRegistration);
                            $studentCourseRegistration->setSchool($newClassProgram->getSchool());
                            $studentCourseRegistration->setEvaluationPeriod($newEvaluationPeriod);
                            $studentCourseRegistration->setModule($newClassProgram->getModule()); // Assuming you have a module property

                            $studentCourseRegistration->setInstitution($this->getUser()->getInstitution());
                            $studentCourseRegistration->setUser($this->getUser());
                            $studentCourseRegistration->setYear($year);

                            $this->manager->persist($studentCourseRegistration);
                        }

                    }
                }
            }
        }

        $this->manager->flush();

        return $newClassProgram; 
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