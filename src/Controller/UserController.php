<?php

namespace App\Controller;

use App\Entity\Security\User;
use App\Repository\Security\Institution\InstitutionRepository;
use App\Repository\Security\RoleRepository;
use App\Service\UserFileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class UserController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, RoleRepository $roleRepository, InstitutionRepository $institutionRepository, UserFileUploader $fileUploader, UserPasswordHasherInterface $passwordHasher): User
    {
        $uploadedFile = $request->files->get('file');

//        if (!$uploadedFile) {
//            throw new BadRequestHttpException('"file" is required');
//        }

        // create a new entity and set its values
        $user = new User();
        $user->setFirstname($request->get('firstname'));
        $user->setLastname($request->get('lastname'));
        $user->setEmail($request->get('email'));
        $user->setPhone($request->get('phone'));
        $user->setUsername($request->get('username'));
        $filterInstitution = preg_replace("/[^0-9]/", '', $request->get('institution'));
        $filterRole = preg_replace("/[^0-9]/", '', $request->get('role'));
        $institutionId = intval($filterInstitution);
        $roleId = intval($filterRole);
        // $user->setInstitution($request->get('institution'));
        $user->setInstitution($institutionRepository->find($institutionId));
        $user->setProfile($roleRepository->find($roleId));
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $request->get('password')
        );
        $user->setPassword($hashedPassword);

        // upload the file and save its filename
        if (!$uploadedFile){
            //$file = new UploadedFile('C:\Users\lione\OneDrive\Documents\Nouveau dossier\gerp-api\public\uploads\7.jpg', '7.jpg');
            $user->setPicture('7.jpg');
            //$user->setPicture(null);
            $user->setFileName('7.jpg');
            //$user->setFileName(null);
            $user->setFileType('image/jpg');
            //$user->setFileType(null);
            $user->setFileSize(null);

        }
        else{
            $user->setPicture($fileUploader->upload($uploadedFile));
            $user->setFileName($request->get('fileName'));
            $user->setFileType($request->get('fileType'));
            $user->setFileSize($request->get('fileSize'));
        }

        return $user;

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



