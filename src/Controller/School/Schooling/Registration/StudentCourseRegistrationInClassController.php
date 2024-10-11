<?php

namespace App\Controller\School\Schooling\Registration;

use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\Security\Session\YearRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class StudentCourseRegistrationInClassController extends AbstractController
{
    public function __construct(private readonly StudentCourseRegistrationRepository $studCourseRegRepo,
                                private readonly SchoolClassRepository $schoolClassRepo,
                                private readonly YearRepository $yearRepository)
    {
    }

    public function __invoke(Request $request)
    {
        $id = $request->get('id');
        if (!$id){
            return new JsonResponse(['hydra:title' => 'Invalid request body, object not found'],Response::HTTP_BAD_REQUEST);
        }

        $studentClass = $this->schoolClassRepo->findOneBy(['id' => $id]);
        if (!$studentClass){
            return new JsonResponse(['hydra:title' => 'Class not found'],Response::HTTP_NOT_FOUND);
        }

        $currentYear = $this->yearRepository->findOneBy(['isCurrent' => true]);
        if (!$currentYear){
            return new JsonResponse(['hydra:title' => 'Current year is not configure'],Response::HTTP_NOT_FOUND);
        }

        $studentRegistrations = $this->studCourseRegRepo->findByClassForAttendance($studentClass, $currentYear);

        $studentsInCourse = [];
        foreach ($studentRegistrations as $registration) {
            $studentsInCourse[] = [
                'id' => $registration['studentId'],
                'name' => $registration['name'],
                'firstname' => $registration['firstName'],
                'matricule' => $registration['matricule'],
            ];
        }

        return $this->json([
            'hydra:member' => $studentsInCourse,
        ]);
    }
}
