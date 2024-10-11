<?php

namespace App\Controller\School\Study\Teacher\HomeWork;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Teacher\HomeWorkRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetHomeWorkByTeacherController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface $tokenStorage, HomeWorkRepository $homeWorkRepo,
                                 TeacherRepository $teacherRepo,  StudentRegistrationRepository $studentRegistrationRepo )
    {
        $this->teacherRepository = $teacherRepo;
        $this->studentRegistrationRepository = $studentRegistrationRepo;
        $this->homeWorkRepo = $homeWorkRepo;

    }

    public function __invoke(Request $request, HomeWorkRepository $homeWorkRepo)
    {
        $teacher = $this->teacherRepository->findOneBy(['operator' => $this->getUser()]);

        $homeworks = $this->homeWorkRepo->findByTeacher($teacher);
//        dd($homeWorks);

        $myHomeworks = [];
        foreach ($homeworks as $homework){
            $myHomeworks [] = [
                'id' => $homework->getId(),
                'course' => $homework->getCourse()->getCourse(),
                'comment' => $homework->getComment(),
                'dueDate' => $homework->getDueDate(),
                'file' => $homework->getFile(),
                'fileName' => $homework->getFileName(),
                'fileSize' => $homework->getFileSize(),
                'fileType' => $homework->getFileType(),
                'publishAt' => $homework->getPublishAt(),
                'isPublish' => $homework->isIsPublish(),
                'title' => $homework->getTitle(),
            ];
        }


        return $this->json(['hydra:member' => $myHomeworks]);

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



