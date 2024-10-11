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
class TeacherCourseRegistrationController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(TeacherRepository $teacherRepository, TeacherCourseRegistrationRepository $teacherCourseRegistrationRepository): JsonResponse
    {
        $teacher = $teacherRepository->findOneBy(['operator' => $this->getUser()]);
        if (!$teacher)
            return new JsonResponse(['hydra:title' => 'Teacher not found.'],Response::HTTP_BAD_REQUEST);

        $teacherCourseRegistrations = $teacherCourseRegistrationRepository->findBy([],['id' => 'DESC']);

        $myTeacherCourseRegistrations = [];
        foreach ($teacherCourseRegistrations as $teacherCourseRegistration){
            // $course = '('.$teacherCourseRegistration->getCourse()->getCodeuvc().') '.$teacherCourseRegistration->getCourse()->getNameuvc();
            $myTeacherCourseRegistrations [] = [
                '@id' => "/api/teacher_course_registrations/".$teacherCourseRegistration->getId(),
                '@type' => "TeacherCourseRegistration",
                'id' => $teacherCourseRegistration->getId(),
                'codeuvc' => $teacherCourseRegistration->getCourse()->getCodeuvc(),
                'nameuvc' => $teacherCourseRegistration->getCourse()->getNameuvc(),
                // 'teacher' => $teacherCourseRegistration->getTeacher(),
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
