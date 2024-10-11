<?php

namespace App\Controller\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\ConsignmentHour;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Discipline\ConsignmentHourRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetConsignmentHoursController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }
    public function __invoke(Request $request, ConsignmentHourRepository $consignmentHoursRepository, SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository): JsonResponse
    {


        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            $consignmentHours = $consignmentHoursRepository->findBy([], ['id' => 'DESC']);

            foreach ($consignmentHours as $consignmentHour){

                $requestData [] = [
                    '@id' => "/api/get/consignment-hours/".$consignmentHour->getId(),
                    '@type' => 'ConsignmentHour',
                    'id' => $consignmentHour->getId(),
                    'startDate' => $consignmentHour->getStartDate() ? $consignmentHour->getStartDate()->format('Y-m-d') : '',
                    'startTime' => $consignmentHour->getStartTime() ? $consignmentHour->getStartTime()->format('H:i') : '',
                    'endTime' => $consignmentHour->getEndTime() ? $consignmentHour->getEndTime()->format('H:i') : '',
                    'school' => $consignmentHour->getSchool() ? $consignmentHour->getSchool()->getName() : '',
                    'class' => $consignmentHour->getSchoolClass() ? $consignmentHour->getSchoolClass()->getCode() : '',
                    'sequence' => $consignmentHour->getSequence() ? $consignmentHour->getSequence()->getCode() : '',
                    'motif' => $consignmentHour->getMotif() ? $consignmentHour->getMotif()->getName() : '',
                    'observations' => $consignmentHour->getObservations(),
                    'studentRegistration' => $this->serializeStudentRegistration($consignmentHour),
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
                            $consignmentHours = $consignmentHoursRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($consignmentHours as $consignmentHour){

                                $requestData [] = [
                                    '@id' => "/api/get/consignment-hours/".$consignmentHour->getId(),
                                    '@type' => 'ConsignmentHour',
                                    'id' => $consignmentHour->getId(),
                                    'startDate' => $consignmentHour->getStartDate() ? $consignmentHour->getStartDate()->format('Y-m-d') : '',
                                    'startTime' => $consignmentHour->getStartTime() ? $consignmentHour->getStartTime()->format('H:i') : '',
                                    'endTime' => $consignmentHour->getEndTime() ? $consignmentHour->getEndTime()->format('H:i') : '',
                                    'school' => $consignmentHour->getSchool() ? $consignmentHour->getSchool()->getName() : '',
                                    'class' => $consignmentHour->getSchoolClass() ? $consignmentHour->getSchoolClass()->getCode() : '',
                                    'sequence' => $consignmentHour->getSequence() ? $consignmentHour->getSequence()->getCode() : '',
                                    'motif' => $consignmentHour->getMotif() ? $consignmentHour->getMotif()->getName() : '',
                                    'observations' => $consignmentHour->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($consignmentHour),
                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $consignmentHours = $consignmentHoursRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($consignmentHours as $consignmentHour){
                            if ($consignmentHour) {
                                $requestData [] = [
                                    '@id' => "/api/get/consignment-hours/".$consignmentHour->getId(),
                                    '@type' => 'ConsignmentHour',
                                    'id' => $consignmentHour->getId(),
                                    'startDate' => $consignmentHour->getStartDate() ? $consignmentHour->getStartDate()->format('Y-m-d') : '',
                                    'startTime' => $consignmentHour->getStartTime() ? $consignmentHour->getStartTime()->format('H:i') : '',
                                    'endTime' => $consignmentHour->getEndTime() ? $consignmentHour->getEndTime()->format('H:i') : '',
                                    'school' => $consignmentHour->getSchool() ? $consignmentHour->getSchool()->getName() : '',
                                    'class' => $consignmentHour->getSchoolClass() ? $consignmentHour->getSchoolClass()->getCode() : '',
                                    'sequence' => $consignmentHour->getSequence() ? $consignmentHour->getSequence()->getCode() : '',
                                    'motif' => $consignmentHour->getMotif() ? $consignmentHour->getMotif()->getName() : '',
                                    'observations' => $consignmentHour->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($consignmentHour),
                                ];
                            }
                        }
                    }

                }
            }
        }
        return $this->json(['hydra:member' => $requestData]);

    }

    public function serializeStudentRegistration(ConsignmentHour $consignmentHour): array
    {
        $studentRegistrations = $consignmentHour->getStudentRegistrations();
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
