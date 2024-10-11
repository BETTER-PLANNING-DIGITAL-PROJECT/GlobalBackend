<?php

namespace App\Controller\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\AbsencePermit;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Discipline\AbsencePermitRepository;
use App\Repository\School\Schooling\Discipline\ReasonRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PostAbsencePermitController extends AbstractController
{
    public function __construct(EntityManagerInterface $manager,
                                private readonly TokenStorageInterface $tokenStorage
    )
    {
    }

    public function __invoke(Request $request,
                             InstitutionRepository $institutionRepository,
                             EntityManagerInterface              $manager,
                             AbsencePermitRepository $absencePermitRepository,
                             StudentRegistrationRepository $studRegistrationRepo,
                             SchoolRepository $schoolRepository,
                             SchoolClassRepository $schoolClassRepository,
                             SequenceRepository $sequenceRepository,
                             ReasonRepository $reasonRepository)
    {
        $absencePermitData = json_decode($request->getContent(), true);
        $studentRegistrationsSelected = $absencePermitData['studentRegistration'];

        $schoolId = !empty($absencePermitData['school']) ? $this->getIdFromApiResourceId($absencePermitData['school']) : null;
        $school = $schoolId ? $schoolRepository->find($schoolId) : null;

        $classId = !empty($absencePermitData['class']) ? $this->getIdFromApiResourceId($absencePermitData['class']) : null;
        $class = $classId ? $schoolClassRepository->find($classId) : null;

        $sequenceId = !empty($absencePermitData['sequence']) ? $this->getIdFromApiResourceId($absencePermitData['sequence']) : null;
        $sequence = $sequenceId ? $sequenceRepository->find($sequenceId) : null;

        $reasonId = !empty($absencePermitData['reason']) ? $this->getIdFromApiResourceId($absencePermitData['reason']) : null;
        $reason = $reasonId ? $reasonRepository->find($reasonId) : null;


        $startDate = new DateTime($absencePermitData['startDate']);
        $endDate = new DateTime($absencePermitData['endDate']);
        $startTime = new DateTime($absencePermitData['startTime']);
        $endTime = new DateTime($absencePermitData['endTime']);
        $observations = $absencePermitData['observations'];


        foreach ($studentRegistrationsSelected as $studentRegistrationSelected) {
            // Setting to get Id
            $filter = preg_replace("/[^0-9]/", '', $studentRegistrationSelected);
            $filterId = intval($filter);
            $studentRegistrationTaken = $studRegistrationRepo->find($filterId);

            // New Student Registration
            $absencePermit = new AbsencePermit();

            $absencePermit->addStudentRegistration($studentRegistrationTaken);
            $studentRegistrationTaken->addAbsencePermit($absencePermit);

            $absencePermit->setSchool($school);
            $absencePermit->setSchoolClass($class);
            $absencePermit->setSequence($sequence);
            $absencePermit->setReason($reason);
            $absencePermit->setStartDate($startDate);
            $absencePermit->setEndDate($endDate);
            $absencePermit->setStartTime($startTime);
            $absencePermit->setEndTime($endTime);
            $absencePermit->setObservations($observations);

            $absencePermit->setInstitution($this->getUser()->getInstitution());
            $absencePermit->setUser($this->getUser());
            $absencePermit->setYear($this->getUser()->getCurrentYear());

            $manager->persist($absencePermit);

        }

        $manager->flush();
        return $absencePermit;
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
