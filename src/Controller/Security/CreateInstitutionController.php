<?php

namespace App\Controller\Security;

use App\Entity\Security\Institution\Institution;
use App\Repository\Security\RoleRepository;
use App\Repository\Setting\Institution\ManagerTypeRepository;
use App\Service\InstitutionFileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
final class CreateInstitutionController extends AbstractController
{

    public function __invoke(Request $request,
                             RoleRepository $roleRepository,
                             ManagerTypeRepository $managerTypeRepository,
                             InstitutionFileUploader $institutionFileUploader): Institution
    {
        $uploadedFile = $request->files->get('file');

        // create a new entity and set its values
        $institution = new Institution();
        $institution->setCode($request->get('code'));
        $institution->setName($request->get('name'));
        $institution->setEmail($request->get('email'));
        $institution->setPhone($request->get('phone'));
        $institution->setAddress($request->get('address'));
        $institution->setCity($request->get('city'));
        $institution->setPostalCode($request->get('postalCode'));
        $institution->setRegion($request->get('region'));
        $institution->setWebsite($request->get('website'));
        $institution->setManager($request->get('manager'));

        $filterManagerType = preg_replace("/[^0-9]/", '', $request->get('managerType'));
        $managerTypeId = intval($filterManagerType);

        if ($managerTypeId){
            $institution->setManagerType($managerTypeRepository->find($managerTypeId));
        }

        // upload the file and save its filename
        if (!$uploadedFile){
            $institution->setPicture('7.jpg');
            $institution->setFileName('7.jpg');
            $institution->setFileType('image/jpg');
            $institution->setFileSize(null);
        }
        else{
            $institution->setPicture($institutionFileUploader->upload($uploadedFile));
            $institution->setFileName($request->get('fileName'));
            $institution->setFileType($request->get('fileType'));
            $institution->setFileSize($request->get('fileSize'));
        }

        return $institution;

    }

}



