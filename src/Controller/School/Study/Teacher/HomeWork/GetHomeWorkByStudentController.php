<?php

namespace App\Controller\School\Study\Teacher\HomeWork;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Study\Teacher\HomeWorkRegistrationRepository;
use App\Repository\School\Study\Teacher\HomeWorkRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetHomeWorkByStudentController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface $tokenStorage, HomeWorkRegistrationRepository $homeWorkRegistrationRepo,
                                StudentRepository $studentRepo,  StudentRegistrationRepository $studentRegistrationRepo )
    {
        $this->studentRepository = $studentRepo;
        $this->studentRegistrationRepository = $studentRegistrationRepo;
        $this->homeWorkRegistrationRepo = $homeWorkRegistrationRepo;

    }

    public function __invoke(Request $request, HomeWorkRepository $homeWorkRepo)
    {
//        $teacher = $this->teacherRepository->findOneBy(['operator' => $this->getUser()]);

        $student = $this->studentRepository->findOneBy(['operator' => $this->getUser()]);
        $studentRegistration = $this->studentRegistrationRepository->findOneBy(['student' => $student]);
        $studentId = $studentRegistration->getId();

        $homeworkRegistrations = $this->homeWorkRegistrationRepo->findByStudent($studentRegistration);
//        dd($homeWorks);

        $myHomeworks = [];
        foreach ($homeworkRegistrations as $homeworkRegistration){
            $myHomeworks [] = [
                'id' => $homeworkRegistration->getHomeWork()->getId(),
                'course' => $homeworkRegistration->getHomeWork()->getCourse()->getCourse(),
                'comment' => $homeworkRegistration->getHomeWork()->getComment(),
                'dueDate' => $homeworkRegistration->getHomeWork()->getDueDate(),
                'file' => $homeworkRegistration->getHomeWork()->getFile(),
                'fileName' => $homeworkRegistration->getHomeWork()->getFileName(),
                'fileSize' => $homeworkRegistration->getHomeWork()->getFileSize(),
                'fileType' => $homeworkRegistration->getHomeWork()->getFileType(),
                'publishAt' => $homeworkRegistration->getHomeWork()->getPublishAt(),
                'isPublish' => $homeworkRegistration->getHomeWork()->isIsPublish(),
                'title' => $homeworkRegistration->getHomeWork()->getTitle(),
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