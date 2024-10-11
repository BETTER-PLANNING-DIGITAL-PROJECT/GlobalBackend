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
final class CreateHomeWorkController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, TeacherCourseRegistrationRepository $teacherCourseRegistrationRepository, FileUploader $fileUploader): HomeWork
    {
        $uploadedFile = $request->files->get('file');

        // create a new entity and set its values
        $homeWork = new HomeWork();

        $homeWork->setTitle($request->get('title'));
        $homeWork->setComment($request->get('comment'));
        $dueDate = $request->get('dueDate');
        $date = new \DateTimeImmutable($dueDate);
        $homeWork->setDueDate($date);

        $filterCourse = preg_replace("/[^0-9]/", '', $request->get('course'));
        $courseId = intval($filterCourse);;
        $homeWork->setCourse($teacherCourseRegistrationRepository->find($courseId));

        $homeWork->setInstitution($this->getUser()->getInstitution());
        $homeWork->setUser($this->getUser());
        $homeWork->setYear($this->getUser()->getCurrentYear());

        // upload the file and save its filename
        if ($uploadedFile){
            $homeWork->setFile($fileUploader->upload($uploadedFile));
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



