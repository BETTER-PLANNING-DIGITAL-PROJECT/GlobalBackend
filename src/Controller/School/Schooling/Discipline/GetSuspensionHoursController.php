<?php

namespace App\Controller\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\SuspensionHour;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Discipline\SuspensionHourRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetSuspensionHoursController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, SuspensionHourRepository $suspensionHoursRepository, SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository): JsonResponse
    {

        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            $suspensionHours = $suspensionHoursRepository->findBy([], ['id' => 'DESC']);

            foreach ($suspensionHours as $suspensionHour){

                $requestData [] = [
                    '@id' => "/api/get/suspension-hours/".$suspensionHour->getId(),
                    '@type' => 'Suspension',
                    'id' => $suspensionHour->getId(),
                    'startDate' => $suspensionHour->getStartDate() ? $suspensionHour->getStartDate()->format('Y-m-d') : '',
                    'startTime' => $suspensionHour->getStartTime() ? $suspensionHour->getStartTime()->format('H:i') : '',
                    'endTime' => $suspensionHour->getEndTime() ? $suspensionHour->getEndTime()->format('H:i') : '',
                    'school' => $suspensionHour->getSchool() ? $suspensionHour->getSchool()->getName() : '',
                    'class' => $suspensionHour->getSchoolClass() ? $suspensionHour->getSchoolClass()->getCode() : '',
                    'sequence' => $suspensionHour->getSequence() ? $suspensionHour->getSequence()->getCode() : '',
                    'motif' => $suspensionHour->getMotif() ? $suspensionHour->getMotif()->getName() : '',
                    'observations' => $suspensionHour->getObservations(),
                    'studentRegistration' => $this->serializeStudentRegistration($suspensionHour),
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
                            $suspensionHours = $suspensionHoursRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($suspensionHours as $suspensionHour){

                                $requestData [] = [
                                    '@id' => "/api/get/suspension-hours/".$suspensionHour->getId(),
                                    '@type' => 'Suspension',
                                    'id' => $suspensionHour->getId(),
                                    'startDate' => $suspensionHour->getStartDate() ? $suspensionHour->getStartDate()->format('Y-m-d') : '',
                                    'startTime' => $suspensionHour->getStartTime() ? $suspensionHour->getStartTime()->format('H:i') : '',
                                    'endTime' => $suspensionHour->getEndTime() ? $suspensionHour->getEndTime()->format('H:i') : '',
                                    'school' => $suspensionHour->getSchool() ? $suspensionHour->getSchool()->getName() : '',
                                    'class' => $suspensionHour->getSchoolClass() ? $suspensionHour->getSchoolClass()->getCode() : '',
                                    'sequence' => $suspensionHour->getSequence() ? $suspensionHour->getSequence()->getCode() : '',
                                    'motif' => $suspensionHour->getMotif() ? $suspensionHour->getMotif()->getName() : '',
                                    'observations' => $suspensionHour->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($suspensionHour),
                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $suspensionHours = $suspensionHoursRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($suspensionHours as $suspensionHour){
                            if ($suspensionHour) {
                                $requestData [] = [
                                    '@id' => "/api/get/suspension-hours/".$suspensionHour->getId(),
                                    '@type' => 'Suspension',
                                    'id' => $suspensionHour->getId(),
                                    'startDate' => $suspensionHour->getStartDate() ? $suspensionHour->getStartDate()->format('Y-m-d') : '',
                                    'startTime' => $suspensionHour->getStartTime() ? $suspensionHour->getStartTime()->format('H:i') : '',
                                    'endTime' => $suspensionHour->getEndTime() ? $suspensionHour->getEndTime()->format('H:i') : '',
                                    'school' => $suspensionHour->getSchool() ? $suspensionHour->getSchool()->getName() : '',
                                    'class' => $suspensionHour->getSchoolClass() ? $suspensionHour->getSchoolClass()->getCode() : '',
                                    'sequence' => $suspensionHour->getSequence() ? $suspensionHour->getSequence()->getCode() : '',
                                    'motif' => $suspensionHour->getMotif() ? $suspensionHour->getMotif()->getName() : '',
                                    'observations' => $suspensionHour->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($suspensionHour),
                                ];
                            }
                        }
                    }

                }
            }
        }
        return $this->json(['hydra:member' => $requestData]);

    }

    public function serializeStudentRegistration(SuspensionHour $suspensionHour): array
    {
        $studentRegistrations = $suspensionHour->getStudentRegistrations();
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
