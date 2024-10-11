<?php

namespace App\Controller\School\Study\Teacher\HomeWork;

use App\Entity\School\Study\Teacher\HomeWork;
use App\Entity\Security\User;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class EditHomeWorkController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, HomeWork $homeWork, TeacherCourseRegistrationRepository $teacherCourseRegistrationRepository, FileUploader $fileUploader): HomeWork
    {
        $uploadedFile = $request->files->get('file');

        // update an existing entity and set its values
        $homeWork->setTitle($request->get('title'));
        $homeWork->setComment($request->get('comment'));

        // $dueDate = $request->get('dueDate');
        // $date = new \DateTimeImmutable($request->get('dueDate'));
        $homeWork->setDueDate(new \DateTimeImmutable($request->get('dueDate')));


        $filterCourse = preg_replace("/[^0-9]/", '', $request->get('course'));
        $courseId = intval($filterCourse);;
        $homeWork->setCourse($teacherCourseRegistrationRepository->find($courseId));


        // upload the file and save its filename
        $oldFilename = $homeWork->getFile();

        if (!$uploadedFile){

            if ($oldFilename && !$request->get('file')){
                // nothing
            }else{
                $homeWork->setFile(null);
                $homeWork->setFileName(null);
                $homeWork->setFileType(null);
                $homeWork->setFileSize(null);
            }
        }
        else{
            // upload the file and save its filename
            $oldFilename = $homeWork->getFile();
            $homeWork->setFile($fileUploader->upload($uploadedFile, $oldFilename));
            $homeWork->setFileName($request->get('fileName'));
            $homeWork->setFileType($request->get('fileType'));
            $homeWork->setFileSize($request->get('fileSize'));
        }

        return $homeWork;

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



