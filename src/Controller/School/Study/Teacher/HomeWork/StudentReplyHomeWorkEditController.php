<?php

namespace App\Controller\School\Study\Teacher\HomeWork;

use App\Entity\School\Study\Teacher\HomeWorkStudentReply;
use App\Entity\Security\User;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class StudentReplyHomeWorkEditController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, HomeWorkStudentReply $homeWorkStudentReply, TeacherCourseRegistrationRepository $teacherCourseRegistrationRepository, FileUploader $fileUploader): HomeWorkStudentReply
    {
        $uploadedFile = $request->files->get('file');

        // update an existing entity and set its values

        $homeWorkStudentReply = new HomeWorkStudentReply();
        $homeWorkStudentReply->setTitle($request->get('title'));
        $homeWorkStudentReply->setComment($request->get('comment'));
        $homeWorkStudentReply->setPublishAt((new \DateTimeImmutable())->setTime(0, 0, 0));;
        $homeWorkStudentReply->setUser($this->getUser());
        $homeWorkStudentReply->setInstitution($this->getUser()->getInstitution());


        // upload the file and save its filename

        $oldFilename = $homeWorkStudentReply->getFile();


        if (!$uploadedFile){

            if ($oldFilename && !$request->get('file')){
                // nothing
            }else{
                $homeWorkStudentReply->setFile(null);
                $homeWorkStudentReply->setFileName(null);
                $homeWorkStudentReply->setFileType(null);
                $homeWorkStudentReply->setFileSize(null);
            }
        }
        else{
            // upload the file and save its filename
            $oldFilename = $homeWorkStudentReply->getFile();
            $homeWorkStudentReply->setFile($fileUploader->upload($uploadedFile, $oldFilename));
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



