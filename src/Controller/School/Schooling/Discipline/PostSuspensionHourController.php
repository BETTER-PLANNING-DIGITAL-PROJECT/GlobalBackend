<?php

namespace App\Controller\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\SuspensionHour;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Discipline\MotifRepository;
use App\Repository\School\Schooling\Discipline\SuspensionHourRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use App\Repository\Security\SystemSettingsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PostSuspensionHourController extends AbstractController
{
    public function __construct(EntityManagerInterface $manager,
                                private readonly TokenStorageInterface $tokenStorage
    )
    {
    }

    public function __invoke(Request                       $request,
                             InstitutionRepository         $institutionRepository,
                             EntityManagerInterface        $manager,
                             SuspensionHourRepository      $suspensionHoursRepository,
                             StudentRegistrationRepository $studRegistrationRepo,
                             SchoolRepository              $schoolRepository,
                             SchoolClassRepository         $schoolClassRepository,
                             SystemSettingsRepository         $systemSettingsRepository,
                             SequenceRepository            $sequenceRepository,
                             MotifRepository               $motifRepository)
    {
        $suspensionHoursData = json_decode($request->getContent(), true);
        $studentRegistrationsSelected = $suspensionHoursData['studentRegistration'];

        $schoolId = !empty($suspensionHoursData['school']) ? $this->getIdFromApiResourceId($suspensionHoursData['school']) : null;
        $school = $schoolId ? $schoolRepository->find($schoolId) : null;

        $classId = !empty($suspensionHoursData['class']) ? $this->getIdFromApiResourceId($suspensionHoursData['class']) : null;
        $class = $classId ? $schoolClassRepository->find($classId) : null;

        $sequenceId = !empty($suspensionHoursData['sequence']) ? $this->getIdFromApiResourceId($suspensionHoursData['sequence']) : null;
        $sequence = $sequenceId ? $sequenceRepository->find($sequenceId) : null;

        $motifId = !empty($suspensionData['motif']) ? $this->getIdFromApiResourceId($suspensionData['motif']) : null;
        $motif = $motifId ? $motifRepository->find($motifId) : null;

        $startDate = new DateTime($suspensionHoursData['startDate']);
        $startTime = new DateTime($suspensionHoursData['startTime']);
        $endTime = new DateTime($suspensionHoursData['endTime']);
        $observations = $suspensionHoursData['observations'];

        foreach ($studentRegistrationsSelected as $studentRegistrationSelected) {
            // Setting to get Id
            $filter = preg_replace("/[^0-9]/", '', $studentRegistrationSelected);
            $filterId = intval($filter);
            $studentRegistrationTaken = $studRegistrationRepo->find($filterId);

            // New Student Registration
            $suspensionHours = new SuspensionHour();

            $suspensionHours->addStudentRegistration($studentRegistrationTaken);
            $studentRegistrationTaken->addSuspensionHour($suspensionHours);

            $suspensionHours->setSchoolClass($class);
            $suspensionHours->setSequence($sequence);
            $suspensionHours->setMotif($motif);
            $suspensionHours->setStartDate($startDate);
            $suspensionHours->setStartTime($startTime);
            $suspensionHours->setEndTime($endTime);
            $suspensionHours->setObservations($observations);

            $systemSettings = $systemSettingsRepository->findOneBy([]);
            $schools = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
            if($systemSettings) {
                if ($systemSettings->isIsBranches()) {
                    $suspensionHours->setSchool($school);
                } else {
                    $suspensionHours->setSchool($schools);
                }
            }

            $suspensionHours->setInstitution($this->getUser()->getInstitution());
            $suspensionHours->setUser($this->getUser());
            $suspensionHours->setYear($this->getUser()->getCurrentYear());

            $manager->persist($suspensionHours);

        }

        $manager->flush();
        return $suspensionHours;
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