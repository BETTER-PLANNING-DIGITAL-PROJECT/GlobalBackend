<?php

namespace App\Controller\Security;

use App\Entity\Security\Institution\Institution;
use App\Repository\Security\Institution\InstitutionRepository;
use App\Repository\Security\RoleRepository;
use App\Repository\Setting\Institution\ManagerTypeRepository;
use App\Repository\Setting\Location\CountryRepository;
use App\Service\InstitutionFileUploader;
use App\Service\UserFileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class EditInstitutionController extends AbstractController
{

    public function __invoke(Request $request,
                             CountryRepository $countryRepository,
                             ManagerTypeRepository $managerTypeRepository,
                             RoleRepository $roleRepository,
                             InstitutionRepository $institutionRepository,
                             Institution $institution,
                             InstitutionFileUploader $institutionFileUploader): Institution
    {
        $uploadedFile = $request->files->get('file');

        // update an existing entity and set its values
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

        // dd($request->get('managerType'));
        $filterManagerType = preg_replace("/[^0-9]/", '', $request->get('managerType'));
        $managerTypeId = intval($filterManagerType);

        if ($managerTypeId){
            $institution->setManagerType($managerTypeRepository->find($managerTypeId));
        }

        $filterCountry = preg_replace("/[^0-9]/", '', $request->get('country'));
        $countryId = intval($filterCountry);


        if ($countryId){
            $institution->setCountry($countryRepository->find($countryId));
        }


        // upload the file and save its filename
        $oldFilename = $institution->getPicture();

        if (!$uploadedFile){

            if ($oldFilename && !$request->get('picture')){
                // nothing
            }else{
                $institution->setPicture(null);
                $institution->setFileName(null);
                $institution->setFileType(null);
                $institution->setFileSize(null);
            }
        }
        else{
            // upload the file and save its filename
            $oldFilename = $institution->getPicture();
            $institution->setPicture($institutionFileUploader->upload($uploadedFile, $oldFilename));
            $institution->setFileName($request->get('fileName'));
            $institution->setFileType($request->get('fileType'));
            $institution->setFileSize($request->get('fileSize'));
        }

        return $institution;

    }
}



