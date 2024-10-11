<?php

namespace App\Controller\School\Study\Teacher\HomeWork;

use App\Entity\School\Study\Teacher\HomeWorkStudentReply;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Study\Teacher\HomeWorkRegistrationRepository;
use App\Repository\School\Study\Teacher\HomeWorkRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class StudentReplyHomeWorkCreateController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, StudentRepository $studentRepository,
                                StudentRegistrationRepository $studentRegistrationRepository, HomeWorkRegistrationRepository $homeWorkRegistrationRepository,
    HomeWorkRepository $homeWorkRepository)
    {
        $this->homeWorkRegistrationRepository = $homeWorkRegistrationRepository;
        $this->studentRepository = $studentRepository;
        $this->studentRegistrationRepository = $studentRegistrationRepository;
        $this->homeWorkRepository = $homeWorkRepository;
    }

    public function __invoke(Request $request, FileUploader $fileUploader, HomeWorkRepository $homeWorkRepository): HomeWorkStudentReply
    {
        $student = $this->studentRepository->findOneBy(['operator' => $this->getUser()]);
        $studentRegistration = $this->studentRegistrationRepository->findOneBy(['student' => $student]);
        $studentId = $studentRegistration->getId();
        $uploadedFile = $request->files->get('file');

        // create a new entity and set its values
        $homeWorkStudentReply = new HomeWorkStudentReply();
        $homeWorkStudentReply->setTitle($request->get('title'));
        $id = intval($request->get('id'));
        $homeWorkStudentReply->setHomeWorkRegistration($this->homeWorkRegistrationRepository->findOneBy(['student' => $studentId, 'homeWork' => $id]));
        $homeWorkStudentReply->setComment($request->get('comment'));
        $homeWorkStudentReply->setPublishAt((new \DateTimeImmutable())->setTime(0, 0, 0));
        $homeWorkStudentReply->setUser($this->getUser());
        $homeWorkStudentReply->setInstitution($this->getUser()->getInstitution());


        // upload the file and save its filename
        if ($uploadedFile){
            $homeWorkStudentReply->setFile($fileUploader->upload($uploadedFile));
            $homeWorkStudentReply->setFileName($request->get('fileName'));
            $homeWorkStudentReply->setFileType($request->get('fileType'));
            $homeWorkStudentReply->setFileSize($request->get('fileSize'));
        }

        return $homeWorkStudentReply;

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



