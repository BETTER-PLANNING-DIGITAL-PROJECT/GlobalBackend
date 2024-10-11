<?php

namespace App\Controller\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\LateComing;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Discipline\LateComingRepository;
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
class PostLateComingController extends AbstractController
{
    public function __construct(EntityManagerInterface $manager,
                                private readonly TokenStorageInterface $tokenStorage
    )
    {
    }

    public function __invoke(Request $request,
                             InstitutionRepository $institutionRepository,
                             EntityManagerInterface              $manager,
                             LateComingRepository $lateComingRepository,
                             StudentRegistrationRepository $studRegistrationRepo,
                             SchoolRepository $schoolRepository,
                             SchoolClassRepository $schoolClassRepository,
                             SequenceRepository $sequenceRepository,
                             SystemSettingsRepository $systemSettingsRepository,
                             MotifRepository $motifRepository)
    {
        $lateComingData = json_decode($request->getContent(), true);
        $studentRegistrationsSelected = $lateComingData['studentRegistration'];

        $schoolId = !empty($lateComingData['school']) ? $this->getIdFromApiResourceId($lateComingData['school']) : null;
        $school = $schoolId ? $schoolRepository->find($schoolId) : null;

        $classId = !empty($lateComingData['class']) ? $this->getIdFromApiResourceId($lateComingData['class']) : null;
        $class = $classId ? $schoolClassRepository->find($classId) : null;

        $sequenceId = !empty($lateComingData['sequence']) ? $this->getIdFromApiResourceId($lateComingData['sequence']) : null;
        $sequence = $sequenceId ? $sequenceRepository->find($sequenceId) : null;

        $motifId = !empty($lateComingData['motif']) ? $this->getIdFromApiResourceId($lateComingData['motif']) : null;
        $motif = $motifId ? $motifRepository->find($motifId) : null;

        $startDate = new DateTime($lateComingData['startDate']);
        $startTime = new DateTime($lateComingData['startTime']);
        $endTime = new DateTime($lateComingData['endTime']);
        $observations = $lateComingData['observations'];


        foreach ($studentRegistrationsSelected as $studentRegistrationSelected) {
            // Setting to get Id
            $filter = preg_replace("/[^0-9]/", '', $studentRegistrationSelected);
            $filterId = intval($filter);
            $studentRegistrationTaken = $studRegistrationRepo->find($filterId);

            // New Student Registration
            $lateComing = new LateComing();

            $lateComing->addStudentRegistration($studentRegistrationTaken);
            $studentRegistrationTaken->addLateComing($lateComing);

            $lateComing->setSchoolClass($class);
            $lateComing->setSequence($sequence);
            $lateComing->setMotif($motif);
            $lateComing->setStartDate($startDate);
            $lateComing->setStartTime($startTime);
            $lateComing->setEndTime($endTime);
            $lateComing->setObservations($observations);

            $systemSettings = $systemSettingsRepository->findOneBy([]);
            $schools = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
            if($systemSettings) {
                if ($systemSettings->isIsBranches()) {
                    $lateComing->setSchool($school);
                } else {
                    $lateComing->setSchool($schools);
                }
            }

            $lateComing->setInstitution($this->getUser()->getInstitution());
            $lateComing->setUser($this->getUser());
            $lateComing->setYear($this->getUser()->getCurrentYear());

            $manager->persist($lateComing);

        }

        $manager->flush();
        return $lateComing;
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