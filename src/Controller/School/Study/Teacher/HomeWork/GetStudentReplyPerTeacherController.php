<?php

namespace App\Controller\School\Study\Teacher\HomeWork;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Teacher\HomeWorkRegistrationRepository;
use App\Repository\School\Study\Teacher\HomeWorkStudentReplyRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetStudentReplyPerTeacherController extends AbstractController
{

    public function __construct(private readonly TokenStorageInterface $tokenStorage, HomeWorkRegistrationRepository $homeWorkRegistrationRepo, private readonly HomeWorkStudentReplyRepository $homeWorkStudentReplyRepo,
                                TeacherRepository $teacherRepo, StudentRegistrationRepository $studentRegistrationRepo )
    {
        $this->homeWorkStudentReplyRepository = $homeWorkStudentReplyRepo;
        $this->teacherRepository = $teacherRepo;
        $this->studentRegistrationRepository = $studentRegistrationRepo;
        $this->homeWorkRegistrationRepository = $homeWorkRegistrationRepo;

    }

    public function __invoke(Request $request)
    {
        $teacher = $this->teacherRepository->findOneBy(['operator' => $this->getUser()]);

        $homeWorkStudentReplies = $this->homeWorkStudentReplyRepository->findByTeacher($teacher);

        $myHomeworkReplies = [];
        foreach ($homeWorkStudentReplies as $homeWorkStudentReply){
            $myHomeworkReplies [] = [
                '@id' => "/api/home_work_registrations/".$homeWorkStudentReply->getId(),
                'id' => $homeWorkStudentReply->getId(),
                'comment' => $homeWorkStudentReply->getComment(),
                'file' => $homeWorkStudentReply->getFile(),
                'fileName' => $homeWorkStudentReply->getFileName(),
                'fileSize' => $homeWorkStudentReply->getFileSize(),
                'fileType' => $homeWorkStudentReply->getFileType(),
                'publishAt' => $homeWorkStudentReply->getPublishAt(),
                'title' => $homeWorkStudentReply->getTitle(),
                'student' => $homeWorkStudentReply->getHomeWorkRegistration()->getStudent()->getStudent(),
            ];
        }


       return $this->json(['hydra:member' => $myHomeworkReplies]);

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



