<?php

namespace App\Controller\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\SummonParent;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Discipline\ReasonRepository;
use App\Repository\School\Schooling\Discipline\SummonParentRepository;
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
class PostSummonParentsController extends AbstractController
{
    public function __construct(EntityManagerInterface $manager,
                                private readonly TokenStorageInterface $tokenStorage
    )
    {
    }

    public function __invoke(Request                       $request,
                             InstitutionRepository         $institutionRepository,
                             EntityManagerInterface        $manager,
                             SummonParentRepository        $summonParentsRepository,
                             StudentRegistrationRepository $studRegistrationRepo,
                             SchoolRepository              $schoolRepository,
                             SchoolClassRepository         $schoolClassRepository,
                             SequenceRepository            $sequenceRepository,
                             SystemSettingsRepository            $systemSettingsRepository,
                             ReasonRepository              $reasonRepository)
    {
        $summonParentsData = json_decode($request->getContent(), true);
        $studentRegistrationsSelected = $summonParentsData['studentRegistration'];

        $schoolId = !empty($summonParentsData['school']) ? $this->getIdFromApiResourceId($summonParentsData['school']) : null;
        $school = $schoolId ? $schoolRepository->find($schoolId) : null;

        $classId = !empty($summonParentsData['class']) ? $this->getIdFromApiResourceId($summonParentsData['class']) : null;
        $class = $classId ? $schoolClassRepository->find($classId) : null;

        $sequenceId = !empty($summonParentsData['sequence']) ? $this->getIdFromApiResourceId($summonParentsData['sequence']) : null;
        $sequence = $sequenceId ? $sequenceRepository->find($sequenceId) : null;

        $reasonId = !empty($summonParentsData['reason']) ? $this->getIdFromApiResourceId($summonParentsData['reason']) : null;
        $reason = $reasonId ? $reasonRepository->find($reasonId) : null;

        $startDate = new DateTime($summonParentsData['startDate']);
        $startTime = new DateTime($summonParentsData['startTime']);
        $endTime = new DateTime($summonParentsData['endTime']);
        $observations = $summonParentsData['observations'];

        foreach ($studentRegistrationsSelected as $studentRegistrationSelected) {
            // Setting to get Id
            $filter = preg_replace("/[^0-9]/", '', $studentRegistrationSelected);
            $filterId = intval($filter);
            $studentRegistrationTaken = $studRegistrationRepo->find($filterId);

            // New Student Registration
            $summonParents = new SummonParent();

            $summonParents->addStudentRegistration($studentRegistrationTaken);
            $studentRegistrationTaken->addSummonParent($summonParents);

            $summonParents->setSchoolClass($class);
            $summonParents->setSequence($sequence);
            $summonParents->setReason($reason);
            $summonParents->setStartDate($startDate);
            $summonParents->setStartTime($startTime);
            $summonParents->setEndTime($endTime);
            $summonParents->setObservations($observations);

            $systemSettings = $systemSettingsRepository->findOneBy([]);
            $schools = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
            if($systemSettings) {
                if ($systemSettings->isIsBranches()) {
                    $summonParents->setSchool($school);
                } else {
                    $summonParents->setSchool($schools);
                }
            }

            $summonParents->setInstitution($this->getUser()->getInstitution());
            $summonParents->setUser($this->getUser());
            $summonParents->setYear($this->getUser()->getCurrentYear());

            $manager->persist($summonParents);

        }

        $manager->flush();
        return $summonParents;
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