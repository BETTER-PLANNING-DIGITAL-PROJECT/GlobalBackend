<?php

namespace App\Controller\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\Suspension;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Discipline\SuspensionRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetSuspensionController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, SuspensionRepository $suspensionRepository, SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository): JsonResponse
    {


        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            $suspensions = $suspensionRepository->findBy([], ['id' => 'DESC']);

            foreach ($suspensions as $suspension){

                $requestData [] = [
                    '@id' => "/api/get/suspension/".$suspension->getId(),
                    '@type' => 'Suspension',
                    'id' => $suspension->getId(),
                    'startDate' => $suspension->getStartDate() ? $suspension->getStartDate()->format('Y-m-d') : '',
                    'endDate' => $suspension->getEndDate() ? $suspension->getEndDate()->format('Y-m-d') : '',
                    'school' => $suspension->getSchool() ? $suspension->getSchool()->getName() : '',
                    'class' => $suspension->getSchoolClass() ? $suspension->getSchoolClass()->getCode() : '',
                    'sequence' => $suspension->getSequence() ? $suspension->getSequence()->getCode() : '',
                    'motif' => $suspension->getMotif() ? $suspension->getMotif()->getName() : '',
                    'observations' => $suspension->getObservations(),
                    'studentRegistration' => $this->serializeStudentRegistration($suspension),
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
                            $suspensions = $suspensionRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($suspensions as $suspension){

                                $requestData [] = [
                                    '@id' => "/api/get/suspension/".$suspension->getId(),
                                    '@type' => 'Suspension',
                                    'id' => $suspension->getId(),
                                    'startDate' => $suspension->getStartDate() ? $suspension->getStartDate()->format('Y-m-d') : '',
                                    'endDate' => $suspension->getEndDate() ? $suspension->getEndDate()->format('Y-m-d') : '',
                                    'school' => $suspension->getSchool() ? $suspension->getSchool()->getName() : '',
                                    'class' => $suspension->getSchoolClass() ? $suspension->getSchoolClass()->getCode() : '',
                                    'sequence' => $suspension->getSequence() ? $suspension->getSequence()->getCode() : '',
                                    'motif' => $suspension->getMotif() ? $suspension->getMotif()->getName() : '',
                                    'observations' => $suspension->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($suspension),
                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $suspensions = $suspensionRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($suspensions as $suspension){
                            if ($suspension) {
                                $requestData [] = [
                                    '@id' => "/api/get/suspension/".$suspension->getId(),
                                    '@type' => 'Suspension',
                                    'id' => $suspension->getId(),
                                    'startDate' => $suspension->getStartDate() ? $suspension->getStartDate()->format('Y-m-d') : '',
                                    'endDate' => $suspension->getEndDate() ? $suspension->getEndDate()->format('Y-m-d') : '',
                                    'school' => $suspension->getSchool() ? $suspension->getSchool()->getName() : '',
                                    'class' => $suspension->getSchoolClass() ? $suspension->getSchoolClass()->getCode() : '',
                                    'sequence' => $suspension->getSequence() ? $suspension->getSequence()->getCode() : '',
                                    'motif' => $suspension->getMotif() ? $suspension->getMotif()->getName() : '',
                                    'observations' => $suspension->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($suspension),
                                ];
                            }
                        }
                    }

                }
            }
        }
        return $this->json(['hydra:member' => $requestData]);

    }

    public function serializeStudentRegistration(Suspension $suspension): array
    {
        $studentRegistrations = $suspension->getStudentRegistrations();
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