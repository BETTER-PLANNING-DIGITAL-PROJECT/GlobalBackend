<?php

namespace App\Controller\ImportationControllers;

use App\Entity\School\Schooling\Registration\Student;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\CycleRepository;
use App\Repository\School\Schooling\Configuration\LevelRepository;
use App\Repository\School\Schooling\Configuration\OptionRepository;
use App\Repository\School\Schooling\Configuration\RegimeRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Configuration\SpecialityRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\Security\Session\YearRepository;
use App\Repository\Setting\Location\CountryRepository;
use App\Repository\Setting\Person\BloodGroupRepository;
use App\Repository\Setting\Person\CivilityRepository;
use App\Repository\Setting\Person\ReligionRepository;
use App\Repository\Setting\Person\RhesusRepository;
use App\Repository\Setting\Person\SexRepository;
use App\Repository\Setting\School\DiplomaRepository;
use App\Repository\Setting\School\RepeatingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class StudentRegistrationImportationController extends AbstractController
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

    public function __invoke(Request           $request,
                             YearRepository $yearRepository,
                             SchoolRepository  $schoolRepository,
                             SchoolClassRepository $schoolClassRepository,
                             StudentRepository $studentRepository,
                             SexRepository $sexRepository,
                             StudentRegistrationRepository $studentRegistrationRepository,
                             CountryRepository $countryRepository,
                             ReligionRepository $religionRepository,
                             CycleRepository $cycleRepository,
                             LevelRepository $levelRepository,
                             SpecialityRepository $specialityRepository,
                             OptionRepository $optionRepository,
                             DiplomaRepository $diplomaRepository,
                             RegimeRepository $regimeRepository,
                             RepeatingRepository $repeatingRepository,
                             BloodGroupRepository $bloodGroupRepository,
                             RhesusRepository $rhesusRepository,
                             CivilityRepository $civilityRepository): Response
    {
        $data = $this->jsondecode();

        if(!isset($data->data))
            return throw new BadRequestHttpException('file is compulsory');

        $students = $data->data;

        if (!$students) {
            throw new BadRequestHttpException('"file" is required');
        }

        foreach ($students as $student)
        {
            // year
            if(isset($student->year))
            {
                $year = $yearRepository->findOneBy(['year' => $student->year]);
                if(!$year)
                {
                    return new JsonResponse(['hydra:title' => 'Year not found in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                return new JsonResponse(['hydra:title' => 'Year empty in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            // school code
            if(isset($student->school))
            {
                $school = $schoolRepository->findOneBy(['code' => $student->school]);
                if(!$school)
                {
                    return new JsonResponse(['hydra:title' => 'School code not found in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                return new JsonResponse(['hydra:title' => 'School code empty in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            // class code
            if(isset($student->class))
            {
                $class = $schoolClassRepository->findOneBy(['code' => $student->class]);
                if(!$class)
                {
                    return new JsonResponse(['hydra:title' => 'Class code not found in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                return new JsonResponse(['hydra:title' => 'Class code empty in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            // student matricule
            if(isset($student->matricule))
            {
                $StudentMatricule = $studentRepository->findOneBy(['matricule' => $student->matricule]);
                if($StudentMatricule)
                {
                    return new JsonResponse(['hydra:title' => 'The matricule: '.$StudentMatricule->getMatricule(). ' in line '. $student->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                return new JsonResponse(['hydra:title' => 'Matricule empty in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            if(!isset($student->name))
            {
                return new JsonResponse(['hydra:title' => 'Name empty in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            if(!isset($student->firstName))
            {
                return new JsonResponse(['hydra:title' => 'First Name empty in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            // dob
            // pob

            // gender
            if(isset($student->sex))
            {
                $sex = $sexRepository->findOneBy(['name' => $student->sex]);
                if(!$sex)
                {
                    return new JsonResponse(['hydra:title' => 'Sex name not found in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $sex = null;
            }

            // country
            if(isset($student->country))
            {
                $country = $countryRepository->findOneBy(['name' => $student->country]);
                if(!$country)
                {
                    return new JsonResponse(['hydra:title' => 'Country name not found in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $country = null;
            }

            // phone
            // email

            // region

            // religion
            if(isset($student->religion))
            {
                $religion = $religionRepository->findOneBy(['code' => $student->religion]);
                if(!$religion)
                {
                    return new JsonResponse(['hydra:title' => 'Religion code not found in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $religion = null;
            }

            // cycle
            if(isset($student->cycle))
            {
                $cycle = $cycleRepository->findOneBy(['code' => $student->cycle]);
                if (!$cycle){
                    return new JsonResponse(['hydra:title' => 'Cycle not found for code: '.$student->cycle. ' in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $cycle = null;
            }

            // level
            if(isset($student->level))
            {
                $level = $levelRepository->findOneBy(['name' => $student->level]);
                if (!$level){
                    return new JsonResponse(['hydra:title' => 'Level not found for name: '.$student->level. ' in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $level = null;
            }

            // speciality
            if(isset($student->speciality))
            {
                $speciality = $specialityRepository->findOneBy(['code' => $student->speciality]);
                if (!$speciality){
                    return new JsonResponse(['hydra:title' => 'Speciality not found for code: '.$student->speciality. ' in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $speciality = null;
            }

            // option
            if(isset($student->option))
            {
                $option = $optionRepository->findOneBy(['code' => $student->option]);
                if (!$option){
                    return new JsonResponse(['hydra:title' => 'Option not found for code: '.$student->option. ' in line '. $student->line .' '],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $option = null;
            }


            $newStudent = new Student();

            $newStudent->setYear($year);
            $newStudent->setMatricule($student->matricule);
            // other matricule
            // internal matricule
            $newStudent->setName($student->name);
            $newStudent->setFirstName($student->firstName);
            $newStudent->setSex($sex);
            $newStudent->setReligion($religion);

            // dob
            $new = new \DateTimeImmutable($student->dob);
            $newStudent->setDob($new);

            // born around
            // $new = new \DateTimeImmutable($student->bornAround);
            // $newStudent->setBornAround($new);

            // pob
            $newStudent->setPob($student->pob);

            $newStudent->setCountry($country);
            $newStudent->setRegion($student->region);
            $newStudent->setStudentphone($student->studentphone);
            $newStudent->setStudentemail($student->studentemail->text);

            //
            //
            //

            $newStudent->setStatus('registered');

            $newStudent->setInstitution($this->getUser()->getInstitution());
            $newStudent->setUser($this->getUser());

            $this->entityManager->persist($newStudent);


            $newRegistration = new StudentRegistration();

            $newRegistration->setYear($year);
            $newRegistration->setCurrentYear($year);
            $newRegistration->setStudent($newStudent);

            //
            //
            //

            $newRegistration->setRegion($student->region);
            $newRegistration->setRegistrationdate(new \DateTimeImmutable());
            $newRegistration->setSchool($school);
            $newRegistration->setOptions($option);

            $newRegistration->setClasse($class);
            $newRegistration->setCurrentClass($class);

            $newRegistration->setCycle($cycle);
            $newRegistration->setLevel($level);
            $newRegistration->setSpeciality($speciality);

            $newRegistration->setStatus('registered');

            $newRegistration->setInstitution($this->getUser()->getInstitution());
            $newRegistration->setUser($this->getUser());

            $this->entityManager->persist($newRegistration);
        }

        $this->entityManager->flush();

        return new Response(null, Response::HTTP_OK);

        //return $this->json(['hydra:member' => $this->roleResultName($profile->getName())]);

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