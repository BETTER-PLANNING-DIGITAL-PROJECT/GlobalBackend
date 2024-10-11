<?php

namespace App\Controller\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\StudentFollowUp;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Discipline\MotifRepository;
use App\Repository\School\Schooling\Discipline\StudentFollowUpRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use App\Repository\Security\SystemSettingsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PostStudentFollowUpController extends AbstractController
{
    public function __construct(EntityManagerInterface $manager,
                                private readonly TokenStorageInterface $tokenStorage
    )
    {
    }

    public function __invoke(Request $request,
                             InstitutionRepository $institutionRepository,
                             EntityManagerInterface              $manager,
                             StudentFollowUpRepository $studentFollowUpRepository,
                             StudentRegistrationRepository $studRegistrationRepo,
                             SchoolRepository $schoolRepository,
                             SchoolClassRepository $schoolClassRepository,
                             SequenceRepository $sequenceRepository,
                             MotifRepository $motifRepository,
                             EvaluationPeriodRepository $evaluationPeriodRepository,
                             ClassProgramRepository $classProgramRepository,
                             SystemSettingsRepository $systemSettingsRepository,
                             TeacherCourseRegistrationRepository $teacherCourseRegistrationRepository)
    {
        $studentFollowUpData = json_decode($request->getContent(), true);
        $studentRegistrationsSelected = $studentFollowUpData['studentRegistration'];

        $schoolId = !empty($studentFollowUpData['school']) ? $this->getIdFromApiResourceId($studentFollowUpData['school']) : null;
        $school = $schoolId ? $schoolRepository->find($schoolId) : null;

        $classId = !empty($studentFollowUpData['class']) ? $this->getIdFromApiResourceId($studentFollowUpData['class']) : null;
        $class = $classId ? $schoolClassRepository->find($classId) : null;

        $sequenceId = !empty($studentFollowUpData['sequence']) ? $this->getIdFromApiResourceId($studentFollowUpData['sequence']) : null;
        $sequence = $sequenceId ? $sequenceRepository->find($sequenceId) : null;

        $motifId = !empty($studentFollowUpData['motif']) ? $this->getIdFromApiResourceId($studentFollowUpData['motif']) : null;
        $motif = $motifId ? $motifRepository->find($motifId) : null;

        $evaluationPeriodId = !empty($studentFollowUpData['evaluationPeriod']) ? $this->getIdFromApiResourceId($studentFollowUpData['evaluationPeriod']) : null;
        $evaluationPeriod = $evaluationPeriodId ? $evaluationPeriodRepository->find($evaluationPeriodId) : null;

        $classProgramId = !empty($studentFollowUpData['classProgram']) ? $this->getIdFromApiResourceId($studentFollowUpData['classProgram']) : null;
        $classProgram = $classProgramId ? $classProgramRepository->find($classProgramId) : null;

        $teacherCourseRegistrationId = !empty($studentFollowUpData['teacherCourseRegistration']) ? $this->getIdFromApiResourceId($studentFollowUpData['teacherCourseRegistration']) : null;
        $teacherCourseRegistration = $teacherCourseRegistrationId ? $teacherCourseRegistrationRepository->find($teacherCourseRegistrationId) : null;

        $startDate = new DateTime($studentFollowUpData['startDate']);
        $startTime = new DateTime($studentFollowUpData['startTime']);
        $endTime = new DateTime($studentFollowUpData['endTime']);
        $observations = $studentFollowUpData['observations'];


        foreach ($studentRegistrationsSelected as $studentRegistrationSelected) {
            // Setting to get Id
            $filter = preg_replace("/[^0-9]/", '', $studentRegistrationSelected);
            $filterId = intval($filter);
            $studentRegistrationTaken = $studRegistrationRepo->find($filterId);

            // New Student Registration
            $studentFollowUp = new StudentFollowUp();

            $studentFollowUp->addStudentRegistration($studentRegistrationTaken);
            $studentRegistrationTaken->addStudentFollowUp($studentFollowUp);

            $studentFollowUp->setSchoolClass($class);
            $studentFollowUp->setSequence($sequence);
            $studentFollowUp->setEvaluationPeriod($evaluationPeriod);
            $studentFollowUp->setClassProgram($classProgram);
            $studentFollowUp->setTeacherCourseRegistration($teacherCourseRegistration);
            $studentFollowUp->setMotif($motif);
            $studentFollowUp->setStartDate($startDate);
            $studentFollowUp->setStartTime($startTime);
            $studentFollowUp->setEndTime($endTime);
            $studentFollowUp->setObservations($observations);

            $systemSettings = $systemSettingsRepository->findOneBy([]);
            $schools = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
            if($systemSettings) {
                if ($systemSettings->isIsBranches()) {
                    $studentFollowUp->setSchool($school);
                } else {
                    $studentFollowUp->setSchool($schools);
                }
            }

            $studentFollowUp->setInstitution($this->getUser()->getInstitution());
            $studentFollowUp->setUser($this->getUser());
            $studentFollowUp->setYear($this->getUser()->getCurrentYear());

            $manager->persist($studentFollowUp);

        }

        $manager->flush();
        return $studentFollowUp;
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