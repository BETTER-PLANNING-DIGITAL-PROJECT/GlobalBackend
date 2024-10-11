<?php

namespace App\Controller\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\SummonParent;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Discipline\SummonParentRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetSummonParentsController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, SummonParentRepository $summonParentsRepository, SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository): JsonResponse
    {
        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            $summonParents = $summonParentsRepository->findBy([], ['id' => 'DESC']);

            foreach ($summonParents as $summonParent){

                $requestData [] = [
                    '@id' => "/api/get/summonParent/".$summonParent->getId(),
                    '@type' => 'SummonParent',
                    'id' => $summonParent->getId(),
                    'startDate' => $summonParent->getStartDate() ? $summonParent->getStartDate()->format('Y-m-d') : '',
                    'startTime' => $summonParent->getStartTime() ? $summonParent->getStartTime()->format('H:i') : '',
                    'endTime' => $summonParent->getEndTime() ? $summonParent->getEndTime()->format('H:i') : '',
                    'school' => $summonParent->getSchool() ? $summonParent->getSchool()->getName() : '',
                    'class' => $summonParent->getSchoolClass() ? $summonParent->getSchoolClass()->getCode() : '',
                    'sequence' => $summonParent->getSequence() ? $summonParent->getSequence()->getCode() : '',
                    'motif' => $summonParent->getReason() ? $summonParent->getReason()->getName() : '',
                    'observations' => $summonParent->getObservations(),
                    'studentRegistration' => $this->serializeStudentRegistration($summonParent),
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
                            $summonParents = $summonParentsRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($summonParents as $summonParent){

                                $requestData [] = [
                                    '@id' => "/api/get/summonParent/".$summonParent->getId(),
                                    '@type' => 'SummonParent',
                                    'id' => $summonParent->getId(),
                                    'startDate' => $summonParent->getStartDate() ? $summonParent->getStartDate()->format('Y-m-d') : '',
                                    'startTime' => $summonParent->getStartTime() ? $summonParent->getStartTime()->format('H:i') : '',
                                    'endTime' => $summonParent->getEndTime() ? $summonParent->getEndTime()->format('H:i') : '',
                                    'school' => $summonParent->getSchool() ? $summonParent->getSchool()->getName() : '',
                                    'class' => $summonParent->getSchoolClass() ? $summonParent->getSchoolClass()->getCode() : '',
                                    'sequence' => $summonParent->getSequence() ? $summonParent->getSequence()->getCode() : '',
                                    'motif' => $summonParent->getReason() ? $summonParent->getReason()->getName() : '',
                                    'observations' => $summonParent->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($summonParent),
                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $summonParents = $summonParentsRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($summonParents as $summonParent){
                            if ($summonParent) {
                                $requestData [] = [
                                    '@id' => "/api/get/summonParent/".$summonParent->getId(),
                                    '@type' => 'SummonParent',
                                    'id' => $summonParent->getId(),
                                    'startDate' => $summonParent->getStartDate() ? $summonParent->getStartDate()->format('Y-m-d') : '',
                                    'startTime' => $summonParent->getStartTime() ? $summonParent->getStartTime()->format('H:i') : '',
                                    'endTime' => $summonParent->getEndTime() ? $summonParent->getEndTime()->format('H:i') : '',
                                    'school' => $summonParent->getSchool() ? $summonParent->getSchool()->getName() : '',
                                    'class' => $summonParent->getSchoolClass() ? $summonParent->getSchoolClass()->getCode() : '',
                                    'sequence' => $summonParent->getSequence() ? $summonParent->getSequence()->getCode() : '',
                                    'motif' => $summonParent->getReason() ? $summonParent->getReason()->getName() : '',
                                    'observations' => $summonParent->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($summonParent),
                                ];
                            }
                        }
                    }

                }
            }
        }
        return $this->json(['hydra:member' => $requestData]);

    }

    public function serializeStudentRegistration(SummonParent $summonParent): array
    {
        $studentRegistrations = $summonParent->getStudentRegistrations();
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