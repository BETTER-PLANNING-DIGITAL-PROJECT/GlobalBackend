<?php

namespace App\Controller\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\ConsignmentDay;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Discipline\ConsignmentDayRepository;
use App\Repository\School\Schooling\Discipline\MotifRepository;
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
class PostConsignmentDaysController extends AbstractController
{
    public function __construct(EntityManagerInterface $manager,
                                private readonly TokenStorageInterface $tokenStorage
    )
    {
    }

    public function __invoke(Request                       $request,
                             InstitutionRepository         $institutionRepository,
                             EntityManagerInterface        $manager,
                             ConsignmentDayRepository      $consignmentDaysRepository,
                             StudentRegistrationRepository $studRegistrationRepo,
                             SchoolRepository              $schoolRepository,
                             SchoolClassRepository         $schoolClassRepository,
                             SequenceRepository            $sequenceRepository,
                             SystemSettingsRepository      $systemSettingsRepository,
                             MotifRepository               $motifRepository)
    {
        $consignmentDaysData = json_decode($request->getContent(), true);
        $studentRegistrationsSelected = $consignmentDaysData['studentRegistration'];

        $schoolId = !empty($consignmentDaysData['school']) ? $this->getIdFromApiResourceId($consignmentDaysData['school']) : null;
        $school = $schoolId ? $schoolRepository->find($schoolId) : null;

        $classId = !empty($consignmentDaysData['class']) ? $this->getIdFromApiResourceId($consignmentDaysData['class']) : null;
        $class = $classId ? $schoolClassRepository->find($classId) : null;

        $sequenceId = !empty($consignmentDaysData['sequence']) ? $this->getIdFromApiResourceId($consignmentDaysData['sequence']) : null;
        $sequence = $sequenceId ? $sequenceRepository->find($sequenceId) : null;

        $motifId = !empty($consignmentDaysData['motif']) ? $this->getIdFromApiResourceId($consignmentDaysData['motif']) : null;
        $motif = $motifId ? $motifRepository->find($motifId) : null;

        $startDate = new DateTime($consignmentDaysData['startDate']);
        $endDate = new DateTime($consignmentDaysData['endDate']);
        $observations = $consignmentDaysData['observations'];

        foreach ($studentRegistrationsSelected as $studentRegistrationSelected) {
            // Setting to get Id
            $filter = preg_replace("/[^0-9]/", '', $studentRegistrationSelected);
            $filterId = intval($filter);
            $studentRegistrationTaken = $studRegistrationRepo->find($filterId);

            // New Student Registration
            $consignmentDays = new ConsignmentDay();

            $consignmentDays->addStudentRegistration($studentRegistrationTaken);
            $studentRegistrationTaken->addConsignmentDay($consignmentDays);

            $consignmentDays->setSchoolClass($class);
            $consignmentDays->setSequence($sequence);
            $consignmentDays->setMotif($motif);
            $consignmentDays->setStartDate($startDate);
            $consignmentDays->setEndDate($endDate);
            $consignmentDays->setObservations($observations);

            $systemSettings = $systemSettingsRepository->findOneBy([]);
            $schools = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
            if($systemSettings) {
                if ($systemSettings->isIsBranches()) {
                    $consignmentDays->setSchool($school);
                } else {
                    $consignmentDays->setSchool($schools);
                }
            }

            $consignmentDays->setInstitution($this->getUser()->getInstitution());
            $consignmentDays->setUser($this->getUser());
            $consignmentDays->setYear($this->getUser()->getCurrentYear());

            $manager->persist($consignmentDays);

        }

        $manager->flush();
        return $consignmentDays;
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