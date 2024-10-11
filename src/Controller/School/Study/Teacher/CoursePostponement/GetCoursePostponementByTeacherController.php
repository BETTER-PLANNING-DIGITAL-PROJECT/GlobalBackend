<?php

namespace App\Controller\School\Study\Teacher\CoursePostponement;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Teacher\CoursePostponementRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetCoursePostponementByTeacherController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface $tokenStorage, CoursePostponementRepository $coursePostponementRepo,
                                 TeacherRepository $teacherRepo,  StudentRegistrationRepository $studentRegistrationRepo )
    {
        $this->teacherRepository = $teacherRepo;
        $this->studentRegistrationRepository = $studentRegistrationRepo;
        $this->coursePostponementRepo = $coursePostponementRepo;

    }

    public function __invoke(Request $request, CoursePostponementRepository $coursePostponementRepo)
    {
        $teacher = $this->teacherRepository->findOneBy(['operator' => $this->getUser()]);

        $coursePostponements = $this->coursePostponementRepo->findByTeacher($teacher);
//        dd($homeWorks);

        $myCoursePostponement = [];
        foreach ($coursePostponements as $coursePostponement){
            $myCoursePostponement [] = [
                'id' => $coursePostponement->getId(),
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


        return $this->json(['hydra:member' => $myCoursePostponement]);

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



