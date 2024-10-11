<?php

namespace App\Controller\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\LateComing;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Discipline\LateComingRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetLateComingController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, LateComingRepository $lateComingRepository, SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository): JsonResponse
    {

        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            $lateComings = $lateComingRepository->findBy([], ['id' => 'DESC']);

            foreach ($lateComings as $lateComing){

                $requestData [] = [
                    '@id' => "/api/get/lateComing/".$lateComing->getId(),
                    '@type' => 'LateComing',
                    'id' => $lateComing->getId(),
                    'startDate' => $lateComing->getStartDate() ? $lateComing->getStartDate()->format('Y-m-d') : '',
                    'startTime' => $lateComing->getStartTime() ? $lateComing->getStartTime()->format('H:i') : '',
                    'endTime' => $lateComing->getEndTime() ? $lateComing->getEndTime()->format('H:i') : '',
                    'school' => $lateComing->getSchool() ? $lateComing->getSchool()->getName() : '',
                    'class' => $lateComing->getSchoolClass() ? $lateComing->getSchoolClass()->getCode() : '',
                    'sequence' => $lateComing->getSequence() ? $lateComing->getSequence()->getCode() : '',
                    'motif' => $lateComing->getMotif() ? $lateComing->getMotif()->getName() : '',
                    'observations' => $lateComing->getObservations(),
                    'studentRegistration' => $this->serializeStudentRegistration($lateComing),
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
                            $lateComings = $lateComingRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($lateComings as $lateComing){

                                $requestData [] = [
                                    '@id' => "/api/get/lateComing/".$lateComing->getId(),
                                    '@type' => 'LateComing',
                                    'id' => $lateComing->getId(),
                                    'startDate' => $lateComing->getStartDate() ? $lateComing->getStartDate()->format('Y-m-d') : '',
                                    'startTime' => $lateComing->getStartTime() ? $lateComing->getStartTime()->format('H:i') : '',
                                    'endTime' => $lateComing->getEndTime() ? $lateComing->getEndTime()->format('H:i') : '',
                                    'school' => $lateComing->getSchool() ? $lateComing->getSchool()->getName() : '',
                                    'class' => $lateComing->getSchoolClass() ? $lateComing->getSchoolClass()->getCode() : '',
                                    'sequence' => $lateComing->getSequence() ? $lateComing->getSequence()->getCode() : '',
                                    'motif' => $lateComing->getMotif() ? $lateComing->getMotif()->getName() : '',
                                    'observations' => $lateComing->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($lateComing),
                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $lateComings = $lateComingRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($lateComings as $lateComing){
                            if ($lateComing) {
                                $requestData [] = [
                                    '@id' => "/api/get/lateComing/".$lateComing->getId(),
                                    '@type' => 'LateComing',
                                    'id' => $lateComing->getId(),
                                    'startDate' => $lateComing->getStartDate() ? $lateComing->getStartDate()->format('Y-m-d') : '',
                                    'startTime' => $lateComing->getStartTime() ? $lateComing->getStartTime()->format('H:i') : '',
                                    'endTime' => $lateComing->getEndTime() ? $lateComing->getEndTime()->format('H:i') : '',
                                    'school' => $lateComing->getSchool() ? $lateComing->getSchool()->getName() : '',
                                    'class' => $lateComing->getSchoolClass() ? $lateComing->getSchoolClass()->getCode() : '',
                                    'sequence' => $lateComing->getSequence() ? $lateComing->getSequence()->getCode() : '',
                                    'motif' => $lateComing->getMotif() ? $lateComing->getMotif()->getName() : '',
                                    'observations' => $lateComing->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($lateComing),
                                ];
                            }
                        }
                    }

                }
            }
        }
        return $this->json(['hydra:member' => $requestData]);

    }

    public function serializeStudentRegistration(LateComing $lateComing): array
    {
        $studentRegistrations = $lateComing->getStudentRegistrations();
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