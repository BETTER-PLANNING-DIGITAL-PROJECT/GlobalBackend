<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSchoolClassController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, SchoolClassRepository $schoolClassRepository, SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository):JsonResponse
    {
        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            $schoolClasses = $schoolClassRepository->findBy([], ['id' => 'DESC']);

            foreach ($schoolClasses as $schoolClass){

                $requestData [] = [
                    '@id' => "/api/class/".$schoolClass->getId(),
                    '@type' => "Class",
                    'id' => $schoolClass->getId(),
                    'school' => $schoolClass->getSchool() ? [
                        '@id' => "/api/schools/".$schoolClass->getSchool()->getId(),
                        '@type' => "School",
                        'id' => $schoolClass->getSchool()->getId(),
                        'code' => $schoolClass->getSchool()->getCode(),
                        'name' => $schoolClass->getSchool()->getName(),
                    ] : '',
                    'year' => $schoolClass->getYear(),
                    'code' => $schoolClass->getCode(),
                    'description' => $schoolClass->getDescription(),
                    'maximumStudentNumber' => $schoolClass->getMaximumStudentNumber(),
                    'isOptional' => $schoolClass->isIsOptional(),
                    'classExam' => $schoolClass->getClassExam(),
                    'ageLimit' => $schoolClass->getAgeLimit(),
                    'simpleHourlyRate' => $schoolClass->getSimpleHourlyRate(),
                    'multipleHourlyRate' => $schoolClass->getMultipleHourlyRate(),
                    'nextClass' => $schoolClass->getNextClass(),
                    'guardianship' => $schoolClass->getGuardianship() ? [
                        '@id' => "/api/guardianship/".$schoolClass->getGuardianship()->getId(),
                        '@type' => "Guardianship",
                        'id' => $schoolClass->getGuardianship()->getId(),
                        'code' => $schoolClass->getGuardianship()->getCode(),
                        'name' => $schoolClass->getGuardianship()->getName(),
                    ] : '',
                    'department' => $schoolClass->getDepartment() ? [
                        '@id' => "/api/department/".$schoolClass->getDepartment()->getId(),
                        '@type' => "Department",
                        'id' => $schoolClass->getDepartment()->getId(),
                        'code' => $schoolClass->getDepartment()->getCode(),
                        'name' => $schoolClass->getDepartment()->getName(),
                    ] : '',
                    'classCategory' => $schoolClass->getClassCategory() ? [
                        '@id' => "/api/class-category/".$schoolClass->getClassCategory()->getId(),
                        '@type' => "ClassCategory",
                        'id' => $schoolClass->getClassCategory()->getId(),
                        'code' => $schoolClass->getClassCategory()->getCode(),
                        'name' => $schoolClass->getClassCategory()->getName(),
                    ] : '',
                    'speciality' => $schoolClass->getSpeciality() ? [
                        '@id' => "/api/speciality/".$schoolClass->getSpeciality()->getId(),
                        '@type' => "Speciality",
                        'id' => $schoolClass->getSpeciality()->getId(),
                        'code' => $schoolClass->getSpeciality()->getCode(),
                        'name' => $schoolClass->getSpeciality()->getName(),
                    ] : '',
                    'level' => $schoolClass->getLevel() ? [
                        '@id' => "/api/level/".$schoolClass->getLevel()->getId(),
                        '@type' => "Level",
                        'id' => $schoolClass->getLevel()->getId(),
                        'name' => $schoolClass->getLevel()->getName(),
                    ] : '',
                    'trainingType' => $schoolClass->getTrainingType() ? [
                        '@id' => "/api/training-type/".$schoolClass->getTrainingType()->getId(),
                        '@type' => "TrainingType",
                        'id' => $schoolClass->getTrainingType()->getId(),
                        'name' => $schoolClass->getTrainingType()->getName(),
                    ] : '',
                    'mainRoom' => $schoolClass->getMainRoom() ? [
                        '@id' => "/api/room/".$schoolClass->getMainRoom()->getId(),
                        '@type' => "Room",
                        'id' => $schoolClass->getMainRoom()->getId(),
                        'name' => $schoolClass->getMainRoom()->getName(),
                    ] : '',
                    'registrantOption' => $schoolClass->getRegistrantOption() ? [
                        '@id' => "/api/option/".$schoolClass->getRegistrantOption()->getId(),
                        '@type' => "Option",
                        'id' => $schoolClass->getRegistrantOption()->getId(),
                        'name' => $schoolClass->getRegistrantOption()->getName(),
                    ] : '',

                ];
            }
        }
        else
        {
            $systemSettings = $systemSettingsRepository->findOneBy([]);
            if($systemSettings)
            {
                if($systemSettings->isIsBranches())
                {
                    $userBranches = $this->getUser()->getUserBranches();
                    foreach ($userBranches as $userBranch) {
                        $school = $schoolRepository->findOneBy(['schoolBranch' => $userBranch]);
                        if ($school) {
                            $schoolClasses = $schoolClassRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($schoolClasses as $schoolClass){

                                $requestData [] = [
                                    '@id' => "/api/class/".$schoolClass->getId(),
                                    '@type' => "Class",
                                    'id' => $schoolClass->getId(),
                                    'school' => $schoolClass->getSchool() ? [
                                        '@id' => "/api/schools/".$schoolClass->getSchool()->getId(),
                                        '@type' => "School",
                                        'id' => $schoolClass->getSchool()->getId(),
                                        'code' => $schoolClass->getSchool()->getCode(),
                                        'name' => $schoolClass->getSchool()->getName(),
                                    ] : '',
                                    'code' => $schoolClass->getCode(),
                                    'year' => $schoolClass->getYear(),
                                    'description' => $schoolClass->getDescription(),
                                    'maximumStudentNumber' => $schoolClass->getMaximumStudentNumber(),
                                    'isOptional' => $schoolClass->isIsOptional(),
                                    'classExam' => $schoolClass->getClassExam(),
                                    'ageLimit' => $schoolClass->getAgeLimit(),
                                    'simpleHourlyRate' => $schoolClass->getSimpleHourlyRate(),
                                    'multipleHourlyRate' => $schoolClass->getMultipleHourlyRate(),
                                    'nextClass' => $schoolClass->getNextClass(),
                                    'guardianship' => $schoolClass->getGuardianship() ? [
                                        '@id' => "/api/guardianship/".$schoolClass->getGuardianship()->getId(),
                                        '@type' => "Guardianship",
                                        'id' => $schoolClass->getGuardianship()->getId(),
                                        'code' => $schoolClass->getGuardianship()->getCode(),
                                        'name' => $schoolClass->getGuardianship()->getName(),
                                    ] : '',
                                    'department' => $schoolClass->getDepartment() ? [
                                        '@id' => "/api/department/".$schoolClass->getDepartment()->getId(),
                                        '@type' => "Department",
                                        'id' => $schoolClass->getDepartment()->getId(),
                                        'code' => $schoolClass->getDepartment()->getCode(),
                                        'name' => $schoolClass->getDepartment()->getName(),
                                    ] : '',
                                    'classCategory' => $schoolClass->getClassCategory() ? [
                                        '@id' => "/api/class-category/".$schoolClass->getClassCategory()->getId(),
                                        '@type' => "ClassCategory",
                                        'id' => $schoolClass->getClassCategory()->getId(),
                                        'code' => $schoolClass->getClassCategory()->getCode(),
                                        'name' => $schoolClass->getClassCategory()->getName(),
                                    ] : '',
                                    'speciality' => $schoolClass->getSpeciality() ? [
                                        '@id' => "/api/speciality/".$schoolClass->getSpeciality()->getId(),
                                        '@type' => "Speciality",
                                        'id' => $schoolClass->getSpeciality()->getId(),
                                        'code' => $schoolClass->getSpeciality()->getCode(),
                                        'name' => $schoolClass->getSpeciality()->getName(),
                                    ] : '',
                                    'level' => $schoolClass->getLevel() ? [
                                        '@id' => "/api/level/".$schoolClass->getLevel()->getId(),
                                        '@type' => "Level",
                                        'id' => $schoolClass->getLevel()->getId(),
                                        'name' => $schoolClass->getLevel()->getName(),
                                    ] : '',
                                    'trainingType' => $schoolClass->getTrainingType() ? [
                                        '@id' => "/api/training-type/".$schoolClass->getTrainingType()->getId(),
                                        '@type' => "TrainingType",
                                        'id' => $schoolClass->getTrainingType()->getId(),
                                        'name' => $schoolClass->getTrainingType()->getName(),
                                    ] : '',
                                    'mainRoom' => $schoolClass->getMainRoom() ? [
                                        '@id' => "/api/room/".$schoolClass->getMainRoom()->getId(),
                                        '@type' => "Room",
                                        'id' => $schoolClass->getMainRoom()->getId(),
                                        'name' => $schoolClass->getMainRoom()->getName(),
                                    ] : '',
                                    'registrantOption' => $schoolClass->getRegistrantOption() ? [
                                        '@id' => "/api/option/".$schoolClass->getRegistrantOption()->getId(),
                                        '@type' => "Option",
                                        'id' => $schoolClass->getRegistrantOption()->getId(),
                                        'name' => $schoolClass->getRegistrantOption()->getName(),
                                    ] : '',

                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $schoolClasses = $schoolClassRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($schoolClasses as $schoolClass) {
                            if ($schoolClass) {
                                $requestData [] = [
                                    '@id' => "/api/class/".$schoolClass->getId(),
                                    '@type' => "Class",
                                    'id' => $schoolClass->getId(),
                                    'school' => $schoolClass->getSchool() ? [
                                        '@id' => "/api/schools/".$schoolClass->getSchool()->getId(),
                                        '@type' => "School",
                                        'id' => $schoolClass->getSchool()->getId(),
                                        'code' => $schoolClass->getSchool()->getCode(),
                                        'name' => $schoolClass->getSchool()->getName(),
                                    ] : '',
                                    'code' => $schoolClass->getCode(),
                                    'year' => $schoolClass->getYear(),
                                    'description' => $schoolClass->getDescription(),
                                    'maximumStudentNumber' => $schoolClass->getMaximumStudentNumber(),
                                    'isOptional' => $schoolClass->isIsOptional(),
                                    'classExam' => $schoolClass->getClassExam(),
                                    'ageLimit' => $schoolClass->getAgeLimit(),
                                    'simpleHourlyRate' => $schoolClass->getSimpleHourlyRate(),
                                    'multipleHourlyRate' => $schoolClass->getMultipleHourlyRate(),
                                    'nextClass' => $schoolClass->getNextClass(),
                                    'guardianship' => $schoolClass->getGuardianship() ? [
                                        '@id' => "/api/guardianship/".$schoolClass->getGuardianship()->getId(),
                                        '@type' => "Guardianship",
                                        'id' => $schoolClass->getGuardianship()->getId(),
                                        'code' => $schoolClass->getGuardianship()->getCode(),
                                        'name' => $schoolClass->getGuardianship()->getName(),
                                    ] : '',
                                    'department' => $schoolClass->getDepartment() ? [
                                        '@id' => "/api/department/".$schoolClass->getDepartment()->getId(),
                                        '@type' => "Department",
                                        'id' => $schoolClass->getDepartment()->getId(),
                                        'code' => $schoolClass->getDepartment()->getCode(),
                                        'name' => $schoolClass->getDepartment()->getName(),
                                    ] : '',
                                    'classCategory' => $schoolClass->getClassCategory() ? [
                                        '@id' => "/api/class-category/".$schoolClass->getClassCategory()->getId(),
                                        '@type' => "ClassCategory",
                                        'id' => $schoolClass->getClassCategory()->getId(),
                                        'code' => $schoolClass->getClassCategory()->getCode(),
                                        'name' => $schoolClass->getClassCategory()->getName(),
                                    ] : '',
                                    'speciality' => $schoolClass->getSpeciality() ? [
                                        '@id' => "/api/speciality/".$schoolClass->getSpeciality()->getId(),
                                        '@type' => "Speciality",
                                        'id' => $schoolClass->getSpeciality()->getId(),
                                        'code' => $schoolClass->getSpeciality()->getCode(),
                                        'name' => $schoolClass->getSpeciality()->getName(),
                                    ] : '',
                                    'level' => $schoolClass->getLevel() ? [
                                        '@id' => "/api/level/".$schoolClass->getLevel()->getId(),
                                        '@type' => "Level",
                                        'id' => $schoolClass->getLevel()->getId(),
                                        'name' => $schoolClass->getLevel()->getName(),
                                    ] : '',
                                    'trainingType' => $schoolClass->getTrainingType() ? [
                                        '@id' => "/api/training-type/".$schoolClass->getTrainingType()->getId(),
                                        '@type' => "TrainingType",
                                        'id' => $schoolClass->getTrainingType()->getId(),
                                        'name' => $schoolClass->getTrainingType()->getName(),
                                    ] : '',
                                    'mainRoom' => $schoolClass->getMainRoom() ? [
                                        '@id' => "/api/room/".$schoolClass->getMainRoom()->getId(),
                                        '@type' => "Room",
                                        'id' => $schoolClass->getMainRoom()->getId(),
                                        'name' => $schoolClass->getMainRoom()->getName(),
                                    ] : '',
                                    'registrantOption' => $schoolClass->getRegistrantOption() ? [
                                        '@id' => "/api/option/".$schoolClass->getRegistrantOption()->getId(),
                                        '@type' => "Option",
                                        'id' => $schoolClass->getRegistrantOption()->getId(),
                                        'name' => $schoolClass->getRegistrantOption()->getName(),
                                    ] : '',

                                ];
                            }
                        }
                    }

                }
            }
        }


        return $this->json(['hydra:member' => $requestData]);
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
