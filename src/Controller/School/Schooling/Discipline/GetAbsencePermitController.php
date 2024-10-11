<?php

namespace App\Controller\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\AbsencePermit;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Discipline\AbsencePermitRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetAbsencePermitController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, AbsencePermitRepository $absencePermitRepository, SystemSettingsRepository $systemSettingsRepository,
    SchoolRepository $schoolRepository): JsonResponse
    {
        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            $absencePermits = $absencePermitRepository->findBy([], ['id' => 'DESC']);

            foreach ($absencePermits as $absencePermit){

                $requestData [] = [
                    '@id' => "/api/get/absencePermit/".$absencePermit->getId(),
                    '@type' => 'AbsencePermit',
                    'id' => $absencePermit->getId(),
                    'startDate' => $absencePermit->getStartDate() ? $absencePermit->getStartDate()->format('Y-m-d') : '',
                    'endDate' => $absencePermit->getEndDate() ? $absencePermit->getEndDate()->format('Y-m-d') : '',
                    'startTime' => $absencePermit->getStartTime() ? $absencePermit->getStartTime()->format('H:i') : '',
                    'endTime' => $absencePermit->getEndTime() ? $absencePermit->getEndTime()->format('H:i') : '',
                    'school' => $absencePermit->getSchool() ? $absencePermit->getSchool()->getName() : '',
                    'class' => $absencePermit->getSchoolClass() ? $absencePermit->getSchoolClass()->getCode() : '',
                    'sequence' => $absencePermit->getSequence() ? $absencePermit->getSequence()->getCode() : '',
                    'motif' => $absencePermit->getReason() ? $absencePermit->getReason()->getName() : '',
                    'observations' => $absencePermit->getObservations(),
                    'studentRegistration' => $this->serializeStudentRegistration($absencePermit),
                ];
            }
        }
        else
        {
            $systemSettings = $systemSettingsRepository->findOneBy([]);
            if($systemSettings)
            {
                if($systemSettings->isIsBranches())
                {
                    $userBranches = $this->getUser()->getUserBranches();
                    foreach ($userBranches as $userBranch) {
                        $school = $schoolRepository->findOneBy(['schoolBranch' => $userBranch]);
                        if ($school) {
                            $absencePermits = $absencePermitRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($absencePermits as $absencePermit){

                                $requestData [] = [
                                    '@id' => "/api/get/absencePermit/".$absencePermit->getId(),
                                    '@type' => 'AbsencePermit',
                                    'id' => $absencePermit->getId(),
                                    'startDate' => $absencePermit->getStartDate() ? $absencePermit->getStartDate()->format('Y-m-d') : '',
                                    'endDate' => $absencePermit->getEndDate() ? $absencePermit->getEndDate()->format('Y-m-d') : '',
                                    'startTime' => $absencePermit->getStartTime() ? $absencePermit->getStartTime()->format('H:i') : '',
                                    'endTime' => $absencePermit->getEndTime() ? $absencePermit->getEndTime()->format('H:i') : '',
                                    'school' => $absencePermit->getSchool() ? $absencePermit->getSchool()->getName() : '',
                                    'class' => $absencePermit->getSchoolClass() ? $absencePermit->getSchoolClass()->getCode() : '',
                                    'sequence' => $absencePermit->getSequence() ? $absencePermit->getSequence()->getCode() : '',
                                    'motif' => $absencePermit->getReason() ? $absencePermit->getReason()->getName() : '',
                                    'observations' => $absencePermit->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($absencePermit),
                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $absencePermits = $absencePermitRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($absencePermits as $absencePermit){
                            if ($absencePermit) {
                                $requestData [] = [
                                    '@id' => "/api/get/absencePermit/".$absencePermit->getId(),
                                    '@type' => 'AbsencePermit',
                                    'id' => $absencePermit->getId(),
                                    'startDate' => $absencePermit->getStartDate() ? $absencePermit->getStartDate()->format('Y-m-d') : '',
                                    'endDate' => $absencePermit->getEndDate() ? $absencePermit->getEndDate()->format('Y-m-d') : '',
                                    'startTime' => $absencePermit->getStartTime() ? $absencePermit->getStartTime()->format('H:i') : '',
                                    'endTime' => $absencePermit->getEndTime() ? $absencePermit->getEndTime()->format('H:i') : '',
                                    'school' => $absencePermit->getSchool() ? $absencePermit->getSchool()->getName() : '',
                                    'class' => $absencePermit->getSchoolClass() ? $absencePermit->getSchoolClass()->getCode() : '',
                                    'sequence' => $absencePermit->getSequence() ? $absencePermit->getSequence()->getCode() : '',
                                    'motif' => $absencePermit->getReason() ? $absencePermit->getReason()->getName() : '',
                                    'observations' => $absencePermit->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($absencePermit),
                                ];
                            }
                        }
                    }

                }
            }
        }
        return $this->json(['hydra:member' => $requestData]);
    }

    public function serializeStudentRegistration(AbsencePermit $absencePermit): array
    {
        $studentRegistrations = $absencePermit->getStudentRegistrations();
        $myStudentRegistrations = [];
        foreach ($studentRegistrations as $studentRegistration){
            $myStudentRegistrations[] = [
                '@type' => 'StudentRegistration',
                '@id' => '/api/get/student-registration/'.$studentRegistration->getId(),
                'id' => $studentRegistration->getId(),
                'name' => $studentRegistration->getStudent()->getName(),
                'firstName' => $studentRegistration->getStudent()->getFirstName(),
            ];
        }
        return $myStudentRegistrations;
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