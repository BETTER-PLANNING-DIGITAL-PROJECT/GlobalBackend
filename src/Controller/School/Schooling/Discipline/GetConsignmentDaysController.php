<?php

namespace App\Controller\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\ConsignmentDay;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Discipline\ConsignmentDayRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetConsignmentDaysController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, ConsignmentDayRepository $consignmentDaysRepository, SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository): JsonResponse
    {

        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            $consignmentDays = $consignmentDaysRepository->findBy([], ['id' => 'DESC']);

            foreach ($consignmentDays as $consignmentDay){

                $requestData [] = [
                    '@id' => "/api/get/consignmentDays/".$consignmentDay->getId(),
                    '@type' => 'ConsignmentDay',
                    'id' => $consignmentDay->getId(),
                    'startDate' => $consignmentDay->getStartDate() ? $consignmentDay->getStartDate()->format('Y-m-d') : '',
                    'endDate' => $consignmentDay->getEndDate() ? $consignmentDay->getEndDate()->format('Y-m-d') : '',
                    'school' => $consignmentDay->getSchool() ? $consignmentDay->getSchool()->getName() : '',
                    'class' => $consignmentDay->getSchoolClass() ? $consignmentDay->getSchoolClass()->getCode() : '',
                    'sequence' => $consignmentDay->getSequence() ? $consignmentDay->getSequence()->getCode() : '',
                    'motif' => $consignmentDay->getMotif() ? $consignmentDay->getMotif()->getName() : '',
                    'observations' => $consignmentDay->getObservations(),
                    'studentRegistration' => $this->serializeStudentRegistration($consignmentDay),
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
                            $consignmentDays = $consignmentDaysRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($consignmentDays as $consignmentDay){

                                $requestData [] = [
                                    '@id' => "/api/get/consignmentDays/".$consignmentDay->getId(),
                                    '@type' => 'ConsignmentDay',
                                    'id' => $consignmentDay->getId(),
                                    'startDate' => $consignmentDay->getStartDate() ? $consignmentDay->getStartDate()->format('Y-m-d') : '',
                                    'endDate' => $consignmentDay->getEndDate() ? $consignmentDay->getEndDate()->format('Y-m-d') : '',
                                    'school' => $consignmentDay->getSchool() ? $consignmentDay->getSchool()->getName() : '',
                                    'class' => $consignmentDay->getSchoolClass() ? $consignmentDay->getSchoolClass()->getCode() : '',
                                    'sequence' => $consignmentDay->getSequence() ? $consignmentDay->getSequence()->getCode() : '',
                                    'motif' => $consignmentDay->getMotif() ? $consignmentDay->getMotif()->getName() : '',
                                    'observations' => $consignmentDay->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($consignmentDay),
                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $consignmentDays = $consignmentDaysRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($consignmentDays as $consignmentDay){
                            if ($consignmentDay) {
                                $requestData [] = [
                                    '@id' => "/api/get/consignmentDays/".$consignmentDay->getId(),
                                    '@type' => 'ConsignmentDay',
                                    'id' => $consignmentDay->getId(),
                                    'startDate' => $consignmentDay->getStartDate() ? $consignmentDay->getStartDate()->format('Y-m-d') : '',
                                    'endDate' => $consignmentDay->getEndDate() ? $consignmentDay->getEndDate()->format('Y-m-d') : '',
                                    'school' => $consignmentDay->getSchool() ? $consignmentDay->getSchool()->getName() : '',
                                    'class' => $consignmentDay->getSchoolClass() ? $consignmentDay->getSchoolClass()->getCode() : '',
                                    'sequence' => $consignmentDay->getSequence() ? $consignmentDay->getSequence()->getCode() : '',
                                    'motif' => $consignmentDay->getMotif() ? $consignmentDay->getMotif()->getName() : '',
                                    'observations' => $consignmentDay->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($consignmentDay),
                                ];
                            }
                        }
                    }

                }
            }
        }
        return $this->json(['hydra:member' => $requestData]);

    }

    public function serializeStudentRegistration(ConsignmentDay $consignmentDay): array
    {
        $studentRegistrations = $consignmentDay->getStudentRegistrations();
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
