<?php

namespace App\Controller\School\Schooling\Discipline;

use App\Entity\School\Schooling\Discipline\StudentFollowUp;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Discipline\StudentFollowUpRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetStudentFollowUpController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, StudentFollowUpRepository $studentFollowUpRepository, SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository): JsonResponse
    {

        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            $studentFollowUps = $studentFollowUpRepository->findBy([], ['id' => 'DESC']);

            foreach ($studentFollowUps as $studentFollowUp){

                $requestData [] = [
                    '@id' => "/api/get/StudentFollowUp/".$studentFollowUp->getId(),
                    '@type' => 'StudentFollowUp',
                    'id' => $studentFollowUp->getId(),
                    'startDate' => $studentFollowUp->getStartDate() ? $studentFollowUp->getStartDate()->format('Y-m-d') : '',
                    'startTime' => $studentFollowUp->getStartTime() ? $studentFollowUp->getStartTime()->format('H:i') : '',
                    'endTime' => $studentFollowUp->getEndTime() ? $studentFollowUp->getEndTime()->format('H:i') : '',
                    'school' => $studentFollowUp->getSchool() ? $studentFollowUp->getSchool()->getName() : '',
                    'class' => $studentFollowUp->getSchoolClass() ? $studentFollowUp->getSchoolClass()->getCode() : '',
                    'evaluationPeriod' => $studentFollowUp->getEvaluationPeriod() ? $studentFollowUp->getEvaluationPeriod()->getName() : '',
                    'classProgram' => $studentFollowUp->getClassProgram() ? $studentFollowUp->getClassProgram()->getNameuvc() : '',
                    'teacherCourseRegistration' => $studentFollowUp->getTeacherCourseRegistration() ? $studentFollowUp->getTeacherCourseRegistration()->getTeacher()->getName() : '',
                    'sequence' => $studentFollowUp->getSequence() ? $studentFollowUp->getSequence()->getCode() : '',
                    'motif' => $studentFollowUp->getMotif() ? $studentFollowUp->getMotif()->getName() : '',
                    'observations' => $studentFollowUp->getObservations(),
                    'studentRegistration' => $this->serializeStudentRegistration($studentFollowUp),
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
                            $studentFollowUps = $studentFollowUpRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($studentFollowUps as $studentFollowUp){

                                $requestData [] = [
                                    '@id' => "/api/get/StudentFollowUp/".$studentFollowUp->getId(),
                                    '@type' => 'StudentFollowUp',
                                    'id' => $studentFollowUp->getId(),
                                    'startDate' => $studentFollowUp->getStartDate() ? $studentFollowUp->getStartDate()->format('Y-m-d') : '',
                                    'startTime' => $studentFollowUp->getStartTime() ? $studentFollowUp->getStartTime()->format('H:i') : '',
                                    'endTime' => $studentFollowUp->getEndTime() ? $studentFollowUp->getEndTime()->format('H:i') : '',
                                    'school' => $studentFollowUp->getSchool() ? $studentFollowUp->getSchool()->getName() : '',
                                    'class' => $studentFollowUp->getSchoolClass() ? $studentFollowUp->getSchoolClass()->getCode() : '',
                                    'evaluationPeriod' => $studentFollowUp->getEvaluationPeriod() ? $studentFollowUp->getEvaluationPeriod()->getName() : '',
                                    'classProgram' => $studentFollowUp->getClassProgram() ? $studentFollowUp->getClassProgram()->getNameuvc() : '',
                                    'teacherCourseRegistration' => $studentFollowUp->getTeacherCourseRegistration() ? $studentFollowUp->getTeacherCourseRegistration()->getTeacher()->getName() : '',
                                    'sequence' => $studentFollowUp->getSequence() ? $studentFollowUp->getSequence()->getCode() : '',
                                    'motif' => $studentFollowUp->getMotif() ? $studentFollowUp->getMotif()->getName() : '',
                                    'observations' => $studentFollowUp->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($studentFollowUp),
                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $studentFollowUps = $studentFollowUpRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($studentFollowUps as $studentFollowUp){
                            if ($studentFollowUp) {
                                $requestData [] = [
                                    '@id' => "/api/get/StudentFollowUp/".$studentFollowUp->getId(),
                                    '@type' => 'StudentFollowUp',
                                    'id' => $studentFollowUp->getId(),
                                    'startDate' => $studentFollowUp->getStartDate() ? $studentFollowUp->getStartDate()->format('Y-m-d') : '',
                                    'startTime' => $studentFollowUp->getStartTime() ? $studentFollowUp->getStartTime()->format('H:i') : '',
                                    'endTime' => $studentFollowUp->getEndTime() ? $studentFollowUp->getEndTime()->format('H:i') : '',
                                    'school' => $studentFollowUp->getSchool() ? $studentFollowUp->getSchool()->getName() : '',
                                    'class' => $studentFollowUp->getSchoolClass() ? $studentFollowUp->getSchoolClass()->getCode() : '',
                                    'evaluationPeriod' => $studentFollowUp->getEvaluationPeriod() ? $studentFollowUp->getEvaluationPeriod()->getName() : '',
                                    'classProgram' => $studentFollowUp->getClassProgram() ? $studentFollowUp->getClassProgram()->getNameuvc() : '',
                                    'teacherCourseRegistration' => $studentFollowUp->getTeacherCourseRegistration() ? $studentFollowUp->getTeacherCourseRegistration()->getTeacher()->getName() : '',
                                    'sequence' => $studentFollowUp->getSequence() ? $studentFollowUp->getSequence()->getCode() : '',
                                    'motif' => $studentFollowUp->getMotif() ? $studentFollowUp->getMotif()->getName() : '',
                                    'observations' => $studentFollowUp->getObservations(),
                                    'studentRegistration' => $this->serializeStudentRegistration($studentFollowUp),
                                ];
                            }
                        }
                    }

                }
            }
        }
        return $this->json(['hydra:member' => $requestData]);

    }

    public function serializeStudentRegistration(StudentFollowUp $studentFollowUp): array
    {
        $studentRegistrations = $studentFollowUp->getStudentRegistrations();
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