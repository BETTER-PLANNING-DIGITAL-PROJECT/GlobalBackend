<?php

namespace App\Controller\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\Suspension;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Discipline\MotifRepository;
use App\Repository\School\Schooling\Discipline\SuspensionRepository;
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
class PostSuspensionController extends AbstractController
{
    public function __construct(EntityManagerInterface $manager,
                                private readonly TokenStorageInterface $tokenStorage
    )
    {
    }

    public function __invoke(Request $request,
                             InstitutionRepository $institutionRepository,
                             EntityManagerInterface              $manager,
                             SuspensionRepository $suspensionRepository,
                             StudentRegistrationRepository $studRegistrationRepo,
                             SchoolRepository $schoolRepository,
                             SchoolClassRepository $schoolClassRepository,
                             SystemSettingsRepository $systemSettingsRepository,
                             SequenceRepository $sequenceRepository,
                             MotifRepository $motifRepository)
    {
        $suspensionData = json_decode($request->getContent(), true);
        $studentRegistrationsSelected = $suspensionData['studentRegistration'];

        $schoolId = !empty($suspensionData['school']) ? $this->getIdFromApiResourceId($suspensionData['school']) : null;
        $school = $schoolId ? $schoolRepository->find($schoolId) : null;

        $classId = !empty($suspensionData['class']) ? $this->getIdFromApiResourceId($suspensionData['class']) : null;
        $class = $classId ? $schoolClassRepository->find($classId) : null;

        $sequenceId = !empty($suspensionData['sequence']) ? $this->getIdFromApiResourceId($suspensionData['sequence']) : null;
        $sequence = $sequenceId ? $sequenceRepository->find($sequenceId) : null;

        $motifId = !empty($suspensionData['motif']) ? $this->getIdFromApiResourceId($suspensionData['motif']) : null;
        $motif = $motifId ? $motifRepository->find($motifId) : null;

        $startDate = new DateTime($suspensionData['startDate']);
        $endDate = new DateTime($suspensionData['endDate']);
        $observations = $suspensionData['observations'];

        foreach ($studentRegistrationsSelected as $studentRegistrationSelected) {
            // Setting to get Id
            $filter = preg_replace("/[^0-9]/", '', $studentRegistrationSelected);
            $filterId = intval($filter);
            $studentRegistrationTaken = $studRegistrationRepo->find($filterId);

            // New Student Registration
            $suspension = new Suspension();

            $suspension->addStudentRegistration($studentRegistrationTaken);
            $studentRegistrationTaken->addSuspension($suspension);

            $suspension->setSchoolClass($class);
            $suspension->setSequence($sequence);
            $suspension->setMotif($motif);
            $suspension->setStartDate($startDate);
            $suspension->setEndDate($endDate);
            $suspension->setObservations($observations);

            $systemSettings = $systemSettingsRepository->findOneBy([]);
            $schools = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
            if($systemSettings) {
                if ($systemSettings->isIsBranches()) {
                    $suspension->setSchool($school);
                } else {
                    $suspension->setSchool($schools);
                }
            }

            $suspension->setInstitution($this->getUser()->getInstitution());
            $suspension->setUser($this->getUser());
            $suspension->setYear($this->getUser()->getCurrentYear());

            $manager->persist($suspension);

        }

        $manager->flush();
        return $suspension;
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
