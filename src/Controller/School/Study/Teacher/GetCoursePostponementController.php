<?php

namespace App\Controller\School\Study\Teacher;

use App\Repository\School\Study\Teacher\CoursePostponementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
final class GetCoursePostponementController extends AbstractController
{

    public function __invoke(Request $request, CoursePostponementRepository $coursePostponementRepository): JsonResponse
    {
        $coursePostponements = $coursePostponementRepository->findAll();

        $myCoursePostponements = [];
        foreach ($coursePostponements as $coursePostponement){

            $myCoursePostponements [] = [
                '@id' => "/api/get/course-postponement/".$coursePostponement->getId(),
                '@type' => 'CoursePostponement',
                'id' => $coursePostponement->getId(),
                'course' => $coursePostponement->getCourse()->getTimeTableModelCellSerialize(),
                'courses' => $coursePostponement->getCourse()->getCourse()->getNameuvc() . " - " . $coursePostponement->getCourse()->getDate()->format('y-m-d') . " - " . $coursePostponement->getCourse()->getStartAt()->format('H:i') . " - " . $coursePostponement->getCourse()->getEndAt()->format('H:i') . " - " . $coursePostponement->getCourse()->getTeacher()->getName(),
                'teacher' => $coursePostponement->getCourse()->getTeacher()->getName(),
                'codeuvc' => $coursePostponement->getCourse()->getCourse()->getCodeuvc(),
                'nameuvc' => $coursePostponement->getCourse()->getCourse()->getNameuvc(),
                'comment' => $coursePostponement->getComment(),
                'isValidated' => $coursePostponement->isIsValidated(),
                'date' => $coursePostponement->getDate() ? $coursePostponement->getDate()->format('Y-m-d') : '',
                'startAt' => $coursePostponement->getStartAt() ? $coursePostponement->getStartAt()->format('H:i') : '',
                'endAt' => $coursePostponement->getEndAt() ? $coursePostponement->getEndAt()->format('H:i') : '',
            ];
        }

        return $this->json(['hydra:member' => $myCoursePostponements]);

    }

}
