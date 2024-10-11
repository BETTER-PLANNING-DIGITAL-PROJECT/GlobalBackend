<?php

namespace App\Controller\School\Study\Program;

use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class TeacherCourseRegistrationAdminController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(TeacherRepository $teacherRepository, TeacherCourseRegistrationRepository $teacherCourseRegistrationRepository): JsonResponse
    {
        $teacherCourseRegistrations = $teacherCourseRegistrationRepository->findByInstitutionYear($this->getUser()->getInstitution(), $this->getUser()->getCurrentYear());

        $myTeacherCourseRegistrations = [];
        foreach ($teacherCourseRegistrations as $teacherCourseRegistration){
            $myTeacherCourseRegistrations [] = [
                '@id' => "/api/teacher_course_registrations/".$teacherCourseRegistration->getId(),
                '@type' => "TeacherCourseRegistration",
                'id' => $teacherCourseRegistration->getId(),
                'teacher' => [
                    '@id' => "/api/teachers/".$teacherCourseRegistration->getTeacher()->getId(),
                    '@type' => "Teachers",
                    'id' => $teacherCourseRegistration->getTeacher()->getId(),
                    'name' => $teacherCourseRegistration->getTeacher()->getName(),
                ],
                'course' => [
                    '@id' => "/api/class_programs/".$teacherCourseRegistration->getCourse()->getId(),
                    '@type' => "ClassProgram",
                    'id' => $teacherCourseRegistration->getCourse() ? $teacherCourseRegistration->getCourse()->getId(): '',
                    'codeuvc' => $teacherCourseRegistration->getCourse()->getCodeuvc(),
                    'nameuvc' => $teacherCourseRegistration->getCourse()->getNameuvc(),
                ],
                'hourlyRateVolume' => $teacherCourseRegistration->getHourlyRateVolume(),
                'hourlyRateExhausted' => $teacherCourseRegistration->getHourlyRateExhausted(),
                'hourlyRateNotExhausted' => $teacherCourseRegistration->getHourlyRateNotExhausted(),
                'type' => strtoupper(substr($teacherCourseRegistration->getType(), 7)),
                'isValidated' => $teacherCourseRegistration->isIsValidated(),
                'institution' => $teacherCourseRegistration->getInstitution(),
            ];
        }

        return $this->json(['hydra:member' => $myTeacherCourseRegistrations]);
    }

}
