<?php

namespace App\Controller\Security;

use App\Entity\Security\User;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\ProfileRepository;
use App\Service\UserFileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class EditUserController extends AbstractController
{

    public function __invoke(Request $request, ProfileRepository $profileRepository, BranchRepository $branchRepository, User $user, UserFileUploader $fileUploader): User
    {
        $uploadedFile = $request->files->get('file');

        // update an existing entity and set its values
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

        // upload the file and save its filename
        $oldFilename = $user->getPicture();

        if (!$uploadedFile){

            if ($oldFilename && !$request->get('picture')){
                // nothing
            }else{
                $user->setPicture(null);
                $user->setFileName(null);
                $user->setFileType(null);
                $user->setFileSize(null);
            }
        }
        else{
            // upload the file and save its filename
            $oldFilename = $user->getPicture();
            $user->setPicture($fileUploader->upload($uploadedFile, $oldFilename));
            $user->setFileName($request->get('fileName'));
            $user->setFileType($request->get('fileType'));
            $user->setFileSize($request->get('fileSize'));
        }

        return $user;

    }
}



