<?php

namespace App\State\Processor\School\Study\Program;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Configuration\SubjectRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use App\Repository\Security\SystemSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PutClassProgramProcessor implements ProcessorInterface
{
    private EntityManagerInterface $manager;
    private ClassProgramRepository $classProgramRepo;
    private TeacherRepository $teacherRepo;
    private StudentRegistrationRepository $studentRegistrationRepo;

    private SystemSettingsRepository $systemSettingsRepository;
    private SchoolRepository $schoolRepository;
    private SubjectRepository $subjectRepository;
    private EvaluationPeriodRepository $evaluationPeriodRepository;

    public function __construct(
        private readonly ProcessorInterface                  $processor,
        private readonly TokenStorageInterface               $tokenStorage,
        Request                                              $request,
        EntityManagerInterface                               $manager,
        ClassProgramRepository                               $classProgramRepo,
        TeacherRepository                                    $teacherRepo,
        SystemSettingsRepository $systemSettingsRepository,
        SchoolRepository $schoolRepository, SubjectRepository $subjectRepository, EvaluationPeriodRepository $evaluationPeriodRepository,
        StudentRegistrationRepository                        $studentRegistrationRepo,
        private readonly StudentCourseRegistrationRepository $studentCourseRegistrationRepository
    )
    {
        $this->req = $request;
        $this->manager = $manager;
        $this->classProgramRepo = $classProgramRepo;
        $this->teacherRepo = $teacherRepo;
        $this->systemSettingsRepository = $systemSettingsRepository;
        $this->schoolRepository = $schoolRepository;
        $this->subjectRepository = $subjectRepository;
        $this->evaluationPeriodRepository = $evaluationPeriodRepository;
        $this->studentRegistrationRepo = $studentRegistrationRepo;
    }

    public function getIdFromApiResourceId(string $apiId)
    {
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf + 1);
        return intval($id);
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // validation from here
        if (!$data instanceof ClassProgram) {
            return 0;
        }
        $programData = json_decode($this->req->getContent(), true);
        $school = !isset($requestData['school']) ? null : $this->schoolRepository->find($this->getIdFromApiResourceId($requestData['school']));
        $subject = !isset($requestData['subject']) ? null : $this->subjectRepository->find($this->getIdFromApiResourceId($requestData['subject']));
        $evaluationPeriod = !isset($requestData['evaluationPeriod']) ? null : $this->evaluationPeriodRepository->find($this->getIdFromApiResourceId($requestData['evaluationPeriod']));
        $existingClassProgram = $this->classProgramRepo->findAll();

        $code = $programData['codeuvc'];
        $name = $programData['nameuvc'];

        $systemSettings = $this->systemSettingsRepository->findOneBy([]);

        $schools = $this->schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $duplicateCheckCode = $this->classProgramRepo->findOneBy(['codeuvc' => $code, 'school' => $school, 'subject' => $subject, 'evaluationPeriod' => $evaluationPeriod, 'year' => $this->getUser()->getCurrentYear()]);
            } else {
                $duplicateCheckCode = $this->classProgramRepo->findOneBy(['codeuvc' => $code, 'school' => $schools, 'subject' => $subject, 'evaluationPeriod' => $evaluationPeriod, 'year' => $this->getUser()->getCurrentYear()]);
            }
            if ($duplicateCheckCode && ($duplicateCheckCode !== $data)) {
                return new JsonResponse(['hydra:description' => 'This class program code already exists.'], 400);
            }
        }
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $duplicateCheckName = $this->classProgramRepo->findOneBy(['nameuvc' => $name, 'school' => $school, 'subject' => $subject, 'evaluationPeriod' => $evaluationPeriod, 'year' => $this->getUser()->getCurrentYear()]);
            } else {
                $duplicateCheckName = $this->classProgramRepo->findOneBy(['nameuvc' => $name, 'school' => $schools, 'subject' => $subject, 'evaluationPeriod' => $evaluationPeriod, 'year' => $this->getUser()->getCurrentYear()]);
            }
            if ($duplicateCheckName && ($duplicateCheckName !== $data)) {
                return new JsonResponse(['hydra:description' => 'This class program name already exists.'], 400);
            }
        }

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($existingClassProgram as $existingProgram) {
            foreach ($daysOfWeek as $day) {
                foreach (['Cm', 'Tp', 'Td'] as $timeSlot) {
                    $time = $data->{'get' . $day . 'Start' . $timeSlot}() ? $data->{'get' . $day . 'Start' . $timeSlot}()->format('H:i:s') : '';
                    $time1 = $existingProgram->{'get' . $day . 'Start' . $timeSlot}() ? $existingProgram->{'get' . $day . 'Start' . $timeSlot}()->format('H:i:s') : '';
                    $time2 = $data->{'get' . $day . 'End' . $timeSlot}() ? $data->{'get' . $day . 'End' . $timeSlot}()->format('H:i:s') : '';
                    $time3 = $existingProgram->{'get' . $day . 'End' . $timeSlot}() ? $existingProgram->{'get' . $day . 'End' . $timeSlot}()->format('H:i:s') : '';

                    if ($data->{'is' . $day . $timeSlot}()) {
                        if (
                            $existingProgram->getClass() === $data->getClass() &&
                            $existingProgram->getId() !== $data->getId() &&
                            $time1 == $time &&
                            $time3 == $time2
                        ) {
                            if (
                                $existingProgram->getNameuvc() == $data->getNameuvc() && $existingProgram->getId() !== $data->getId() &&
                                $existingProgram->getPrincipalRoom()->getId() == $data->getPrincipalRoom()->getId()
                            ) {
                                return new JsonResponse(['hydra:description' => 'A Class Program with the same parameters already exists.'], 400);
                            } else {
                                return new JsonResponse(['hydra:description' => 'This period is already occupied.'], 400);
                            }

                        }

                    }
                }
            }
        }

        // COURSE SECTION
        $data->setUser($this->getUser());
        // COURSE SECTION END


        // STUDENT COURSE REGISTRATION SECTION
        $isSubjectObligatory = $data->isIsSubjectObligatory();

        $year = $data->getYear();
        $class = $data->getClass();
        $school = $data->getSchool();
        $evaluationPeriod = $data->getEvaluationPeriod();
        $module = $data->getModule();

        if ($isSubjectObligatory === true) {
            $studentRegistrations = $this->studentRegistrationRepo->findBy(['currentYear' => $year, 'currentClass' => $class, 'school' => $school]);
            foreach ($studentRegistrations as $studentRegistration) {
                $existingStudentCourseRegistration = $this->studentCourseRegistrationRepository->findOneBy([
                    'evaluationPeriod' => $evaluationPeriod,
                    'class' => $class,
                    'classProgram' => $data,
                    'StudRegistration' => $studentRegistration,
                ]);

                if (!$existingStudentCourseRegistration) {
                    $studentCourseRegistration = new StudentCourseRegistration();
                    $studentCourseRegistration->setClass($class);
                    $studentCourseRegistration->setClassProgram($data);
                    $studentCourseRegistration->setStudRegistration($studentRegistration);
                    $studentCourseRegistration->setSchool($school);
                    $studentCourseRegistration->setEvaluationPeriod($evaluationPeriod);
                    $studentCourseRegistration->setModule($module);

                    $studentCourseRegistration->setInstitution($this->getUser()->getInstitution());
                    $studentCourseRegistration->setUser($this->getUser());
                    $studentCourseRegistration->setYear($year);

                    $this->manager->persist($studentCourseRegistration);
                }
            }
        }
        // STUDENT COURSE REGISTRATION SECTION END

        $this->manager->flush();

        $result = $this->processor->process($data, $operation, $uriVariables, $context);

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
