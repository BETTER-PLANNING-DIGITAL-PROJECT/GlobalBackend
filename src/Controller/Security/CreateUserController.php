<?php

namespace App\Controller\Security;

use App\Entity\Security\User;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\ProfileRepository;
use App\Service\UserFileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class CreateUserController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request,
                             ProfileRepository $profileRepository,
                             BranchRepository $branchRepository,
                             UserFileUploader $fileUploader,
                             UserPasswordHasherInterface $passwordHasher): User
    {
        $uploadedFile = $request->files->get('file');

        // create a new entity and set its values
        $user = new User();
        $user->setFirstname($request->get('firstname'));
        $user->setLastname($request->get('lastname'));
        $user->setEmail($request->get('email'));
        $user->setPhone($request->get('phone'));
        $user->setUsername($request->get('username'));
        $user->setIsBranchManager($request->get('isBranchManager'));

        $filterBranch = preg_replace("/[^0-9]/", '', $request->get('branch'));
        $filterProfile = preg_replace("/[^0-9]/", '', $request->get('profile'));

        $branchId = intval($filterBranch);
        $profileId = intval($filterProfile);

        $user->setProfile($profileRepository->find($profileId));
        $user->setBranch($branchRepository->find($branchId));
        $user->setInstitution($branchRepository->find($branchId)->getInstitution());

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $request->get('password')
        );
        $user->setPassword($hashedPassword);

        // upload the file and save its filename
        if (!$uploadedFile){
            $user->setPicture('7.jpg');
            $user->setFileName('7.jpg');
            $user->setFileType('image/jpg');
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



