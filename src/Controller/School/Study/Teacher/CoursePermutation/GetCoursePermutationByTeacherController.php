<?php

namespace App\Controller\School\Study\Teacher\CoursePermutation;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Teacher\CoursePermutationRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetCoursePermutationByTeacherController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface $tokenStorage, CoursePermutationRepository $coursePermutationRepo,
                                 TeacherRepository $teacherRepo,  StudentRegistrationRepository $studentRegistrationRepo )
    {
        $this->teacherRepository = $teacherRepo;
        $this->studentRegistrationRepository = $studentRegistrationRepo;
        $this->coursePermutationRepo = $coursePermutationRepo;

    }

    public function __invoke(Request $request, CoursePermutationRepository $coursePermutationRepo)
    {
        $teacher = $this->teacherRepository->findOneBy(['operator' => $this->getUser()]);

        $coursePermutations = $this->coursePermutationRepo->findByTeacher($teacher);
//        dd($homeWorks);

        $myCoursePermutations = [];
        foreach ($coursePermutations as $coursePermutation){
            $myCoursePermutations [] = [
                'id' => $coursePermutation->getId(),
                'course' => $coursePermutation->getCourse()->getCourse()->getNameuvc(),
                'otherCourse' => $coursePermutation->getOtherCourse()->getCourse()->getNameuvc(),
                'teacher' => $coursePermutation->getOtherCourse()->getTeacher()->getName(),
                'comment' => $coursePermutation->getComment(),
                'isValidated' => $coursePermutation->isIsValidated(),
            ];
        }


        return $this->json(['hydra:member' => $myCoursePermutations]);

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



