<?php
namespace App\Controller\School\Schooling\Student;
 
use App\Entity\School\Schooling\Registration\Student;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class AttachStudentPictureController extends AbstractController
{
    public function __invoke(Request $request, Student $student, FileUploader $fileUploader): Student
    {
        // $uploadedFile = $request->files->get('picture');
        // dd($uploadedFile);
        $uploadedFile = $request->files->get('file');
        // if (!$uploadedFile) {
        //     throw new BadRequestHttpException('"file" is required');
        // }

        $student->setImageName($request->get('imageName'));
        $student->setImageType($request->get('imageType'));
        $student->setImageSize($request->get('imageSize'));

        // upload the file and save its filename

        $existingPictureName = $student->getPicture();
        $student->setPicture($fileUploader->upload($uploadedFile, $existingPictureName));

        return $student;
    }
}
