<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Repository\School\Schooling\Configuration\ClassCategoryRepository;
use App\Repository\School\Schooling\Configuration\DepartmentRepository;
use App\Repository\School\Schooling\Configuration\GuardianshipRepository;
use App\Repository\School\Schooling\Configuration\LevelRepository;
use App\Repository\School\Schooling\Configuration\OptionRepository;
use App\Repository\School\Schooling\Configuration\RoomRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Configuration\SpecialityRepository;
use App\Repository\School\Schooling\Configuration\TrainingTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class ImportClassController extends AbstractController
{
    public function jsondecode()
    {
        try {
            return file_get_contents('php://input') ?
                json_decode(file_get_contents('php://input'), false) :
                [];
        }catch (\Exception $ex)
        {
            return [];
        }
    }

    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(Request $request,
                             GuardianshipRepository $guardianshipRepository,
                             SchoolRepository $schoolRepository,
                             DepartmentRepository $departmentRepository,
                             ClassCategoryRepository $classCategoryRepository,
                             SpecialityRepository $specialityRepository,
                             LevelRepository $levelRepository,
                             TrainingTypeRepository $trainingTypeRepository,
                             RoomRepository $roomRepository,
                             SchoolClassRepository $schoolClassRepository,
                             OptionRepository $optionRepository): Response
    {
        $data = $this->jsondecode();

        if(!isset($data->data))
            return throw new BadRequestHttpException('file is compulsory');

        $schoolClasses = $data->data;

        if (!$schoolClasses) {
            throw new BadRequestHttpException('"file" is required');
        }

        foreach ($schoolClasses as $schoolClass)
        {
            // school code
            if(isset($schoolClass->school))
            {
                $school = $schoolRepository->findOneBy(['code' => $schoolClass->school]);

                if(!$school)
                {
                    return new JsonResponse(['hydra:title' => 'School code not found in line '. $schoolClass->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                return new JsonResponse(['hydra:title' => 'School code empty in line '. $schoolClass->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            // code
            $schoolClassCode = $schoolClassRepository->findOneBy(['code' => $schoolClass->code, 'school' => $school]);
            if ($schoolClassCode){
                return new JsonResponse(['hydra:title' => 'This code: '.$schoolClassCode->getCode(). ' in line '. $schoolClass->line .' already exist in current school'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            // description
            $description = $schoolClass->description ?? null;

            // training type code
            if(isset($schoolClass->trainingType))
            {
                $trainingType = $trainingTypeRepository->findOneBy(['code' => $schoolClass->trainingType]);
                if (!$trainingType){
                    return new JsonResponse(['hydra:title' => 'Training Type not found for code: '.$schoolClass->trainingType. ' in line '. $schoolClass->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $trainingType = null;
            }
            /*else{
                return new JsonResponse(['hydra:title' => 'Training Type empty in line '. $schoolClass->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }*/

            // speciality code
            if(isset($schoolClass->speciality))
            {
                $speciality = $specialityRepository->findOneBy(['code' => $schoolClass->speciality]);
                if (!$speciality){
                    return new JsonResponse(['hydra:title' => 'Speciality not found for code: '.$schoolClass->speciality. ' in line '. $schoolClass->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $speciality = null;
            }

            // level name
            if(isset($schoolClass->level))
            {
                $level = $levelRepository->findOneBy(['name' => $schoolClass->level]);
                if (!$level){
                    return new JsonResponse(['hydra:title' => 'Level not found for name: '.$schoolClass->level. ' in line '. $schoolClass->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $level = null;
            }

            // department code
            if(isset($schoolClass->department))
            {
                $department = $departmentRepository->findOneBy(['code' => $schoolClass->department]);
                if (!$department){
                    return new JsonResponse(['hydra:title' => 'Department not found for code: '.$schoolClass->department. ' in line '. $schoolClass->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $department = null;
            }

            // guardianship code
            if(isset($schoolClass->guardianship))
            {
                $guardianship = $guardianshipRepository->findOneBy(['code' => $schoolClass->guardianship]);
                if (!$guardianship){
                    return new JsonResponse(['hydra:title' => 'Guardianship not found for code: '.$schoolClass->guardianship. ' in line '. $schoolClass->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $guardianship = null;
            }

            // option code
            if(isset($schoolClass->option))
            {
                $option = $optionRepository->findOneBy(['code' => $schoolClass->option]);
                if (!$option){
                    return new JsonResponse(['hydra:title' => 'Option not found for code: '.$schoolClass->option. ' in line '. $schoolClass->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $option = null;
            }

            // class category code
            if(isset($schoolClass->classCategory))
            {
                $classCategory = $classCategoryRepository->findOneBy(['code' => $schoolClass->classCategory]);
                if (!$classCategory){
                    return new JsonResponse(['hydra:title' => 'Class category not found for code: '.$schoolClass->classCategory. ' in line '. $schoolClass->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $classCategory = null;
            }

            // room name
            if(isset($schoolClass->room))
            {
                $room = $roomRepository->findOneBy(['name' => $schoolClass->room]);
                if (!$room){
                    return new JsonResponse(['hydra:title' => 'Main Room not found for name: '.$schoolClass->room. ' in line '. $schoolClass->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $room = null;
            }

            $number = $schoolClass->number ?? null;

            $newSchoolClass = new SchoolClass();

            $newSchoolClass->setInstitution($this->getUser()->getInstitution());
            $newSchoolClass->setYear($this->getUser()->getCurrentYear());
            $newSchoolClass->setSchool($school);

            $newSchoolClass->setCode($schoolClass->code);
            $newSchoolClass->setDescription($description);

            $newSchoolClass->setTrainingType($trainingType);
            $newSchoolClass->setSpeciality($speciality);
            $newSchoolClass->setLevel($level);
            $newSchoolClass->setDepartment($department);
            $newSchoolClass->setGuardianship($guardianship);
            $newSchoolClass->setRegistrantOption($option);

            $newSchoolClass->setClassCategory($classCategory);
            $newSchoolClass->setMainRoom($room);
            $newSchoolClass->setMaximumStudentNumber(intval($number));

            $newSchoolClass->setClassExam('Compulsory Exam class, passing the exam is mandatory to move to a higher class');

            $newSchoolClass->setUser($this->getUser());

            $this->entityManager->persist($newSchoolClass);
        }

        $this->entityManager->flush();

        return new Response(null, Response::HTTP_OK);

        //return $this->json(['hydra:member' => $this->roleResultName($profile->getName())]);
    }

}



