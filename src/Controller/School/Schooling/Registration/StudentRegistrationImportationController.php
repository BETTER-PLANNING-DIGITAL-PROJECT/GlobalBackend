<?php

namespace App\Controller\School\Schooling\Registration;

use App\Entity\Partner\Customer;
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

    public function __invoke(Request           $request, StudentRegistrationRepository $studregistrationRepository, StudentRepository $studentRepository, YearRepository $schoolYearRepository, SexRepository $sexRepository, CountryRepository $countryRepository, ReligionRepository $religionRepository,
                             SchoolRepository  $schoolRepository, SchoolClassRepository $schoolClassRepository, CycleRepository $cycleRepository, LevelRepository $levelRepository, SpecialityRepository $specialityRepository, OptionRepository $optionRepository,
                             DiplomaRepository $diplomaRepository, RegimeRepository $regimeRepository, RepeatingRepository $repeatingRepository, BloodGroupRepository $bloodGroupRepository, RhesusRepository $rhesusRepository, CivilityRepository $civilityRepository): Response
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
            $StudentMatricule = $studentRepository->findOneBy(['matricule' => $student->matricule]);
            if ($StudentMatricule){
                return new JsonResponse(['hydra:title' => 'The matricule: '.$StudentMatricule->getMatricule(). ' in line '. $student->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            $yearName = $schoolYearRepository->findOneBy(['year' => $student->year]);
            if(!$yearName){
                return new JsonResponse(['hydra:title' => 'The year: '.$student->year. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            $sexName = $sexRepository->findOneBy(['name' => $student->sex]);
            if(!$sexName){
                return new JsonResponse(['hydra:title' => 'The name: '.$student->sex. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            $countryName = $countryRepository->findOneBy(['name' => $student->country]);
            if(!$countryName){
                return new JsonResponse(['hydra:title' => 'The name: '.$student->country. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            $religionName = $religionRepository->findOneBy(['name' => $student->religion]);
            if(!$religionName){
                return new JsonResponse(['hydra:title' => 'The name: '.$student->religion. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            $schoolName = $schoolRepository->findOneBy(['name' => $student->school]);
            if(!$schoolName){
                return new JsonResponse(['hydra:title' => 'The name: '.$student->school. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            $className = $schoolClassRepository->findOneBy(['code' => $student->class]);
            if(!$className){
                return new JsonResponse(['hydra:title' => 'The code: '.$student->class. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            $cycleName = $cycleRepository->findOneBy(['name' => $student->cycle]);
            if(!$cycleName){
                return new JsonResponse(['hydra:title' => 'The name: '.$student->cycle. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            $levelName = $levelRepository->findOneBy(['name' => $student->level]);
            if(!$levelName){
                return new JsonResponse(['hydra:title' => 'The name: '.$student->level. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            $specialityName = $specialityRepository->findOneBy(['name' => $student->speciality]);
            if(!$specialityName){
                return new JsonResponse(['hydra:title' => 'The name: '.$student->speciality. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            $optionName = $optionRepository->findOneBy(['name' => $student->option]);
            if(!$optionName){
                return new JsonResponse(['hydra:title' => 'The name: '.$student->option. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            $diplomaName = $diplomaRepository->findOneBy(['name' => $student->diploma]);
            if(!$diplomaName){
                return new JsonResponse(['hydra:title' => 'The name: '.$student->diploma. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
            $regimeName = $regimeRepository->findOneBy(['regime' => $student->regime]);
            if(!$regimeName){
                return new JsonResponse(['hydra:title' => 'The name: '.$student->regime. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
//            $repeatingName = $repeatingRepository->findOneBy(['name' => $student->repeating]);
//            if(!$repeatingName){
//                return new JsonResponse(['hydra:title' => 'The name: '.$student->repeating. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
//            }
//            $bloodGroupName = $bloodGroupRepository->findOneBy(['name' => $student->bloodGroup]);
//            if(!$bloodGroupName){
//                return new JsonResponse(['hydra:title' => 'The name: '.$student->bloodGroup. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
//            }
//            $rhesusName = $rhesusRepository->findOneBy(['name' => $student->rhesus]);
//            if(!$rhesusName){
//                return new JsonResponse(['hydra:title' => 'The name: '.$student->rhesus. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
//            }
//            $civilityName = $civilityRepository->findOneBy(['name' => $student->civility]);
//            if(!$civilityName){
//                return new JsonResponse(['hydra:title' => 'The name: '.$student->civility. ' in line '. $student->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
//            }



            $newStudent = new Student();
            $newStudent->setYear($yearName);
            $newStudent->setSex($sexName);
            $newStudent->setCountry($countryName);
            $newStudent->setReligion($religionName);
//            $newStudent->setBloodGroup($bloodGroupName);
//            $newStudent->setRhesus($rhesusName);
//            $newStudent->setCivility($civilityName);
            $newStudent->setMatricule($student->matricule);
//            $newStudent->setOthermatricule($student->othermatricule);
//            $newStudent->setInternalmatricule($student->internalmatricule);
            $newStudent->setName($student->name);
            $newStudent->setFirstName($student->firstName);
            $new = new \DateTimeImmutable($student->dob);
            $newStudent->setDob($new);
            $new = new \DateTimeImmutable($student->bornAround);
            $newStudent->setBornAround($new);
            $newStudent->setPob($student->pob);
            $newStudent->setRegion($student->region);
            $newStudent->setStudentphone($student->studentphone);
            $newStudent->setStudentemail($student->studentemail->text);
//            $newStudent->setStudentProfession($student->studentProfession);
//            $newStudent->setStudentDistrict($student->studentDistrict);
//            $newStudent->setStudentAddress($student->studentAddress);
//            $newStudent->setStudentTown($student->studentTown);
//            $newStudent->setFathername($student->fathername);
//            $newStudent->setFatherphone($student->fatherphone);
//            $newStudent->setFatheremail($student->fatheremail);
//            $newStudent->setFatherprofession($student->fatherprofession);
//            $newStudent->setMothername($student->mothername);
//            $newStudent->setMotherphone($student->motherphone);
//            $newStudent->setMotheremail($student->motheremail);
//            $newStudent->setMotherprofession($student->motherprofession);
//            $newStudent->setGuardianname($student->guardianname);
//            $newStudent->setGuardianphone($student->guardianphone);
//            $newStudent->setGuardianemail($student->guardianemail);
//            $newStudent->setGuardianprofession($student->guardianprofession);
//            $newStudent->setPartnerName($student->partnerName);
//            $newStudent->setPartnerPhone($student->partnerPhone);
//            $newStudent->setPartnerEmail($student->partnerEmail);
//            $newStudent->setPartnerProfession($student->partnerProfession);



//            $newStudent->setStutterer($student->stutterer);
//            $newStudent->setLeftHanded($student->leftHanded);
//            $newStudent->setHearingProblem($student->hearingProblem);
//            $newStudent->setEyeProblem($student->eyeProblem);
//            $newStudent->setVaccine($student->vaccine);
//            $newStudent->setVaccineProhibited($student->vaccineProhibited);
//            $newStudent->setMedicalHistory($student->medicalHistory);
            $newStudent->setStatus('registered');
//            $newStudent->setStatus('registered');

//            $newStudent->setNumberOfChildren($student->numberOfChildren);
            $newStudent->setInstitution($this->getUser()->getInstitution());
            $this->entityManager->persist($newStudent);



            $newRegistration = new StudentRegistration();
            $newRegistration->setDiploma($diplomaName);
//            $newRegistration->setCenter($student->center);
//            $newRegistration->setPvdiplome($student->pvdiplome);
//            $newRegistration->setPvselection($student->pvselection);
//            $newRegistration->setAverage($student->average);
//            $newRegistration->setRanks($student->ranks);
            $newRegistration->setSchool($schoolName);
            $newRegistration->setCycle($cycleName);
            $newRegistration->setClasse($className);
            $newRegistration->setRegime($regimeName);
            $newRegistration->setSpeciality($specialityName);
            $newRegistration->setLevel($levelName);
            $newRegistration->setOptions($optionName);
            $newRegistration->setStatus('registered');
//            $newRegistration->getStudent()->setStatus('registered');
//            $newRegistration->setRepeating($repeatingName);-
//            $newRegistration->setRegistrationdate($student->registrationdate);
            $newRegistration->setStudent($newStudent);
            $newRegistration->setInstitution($this->getUser()->getInstitution());
            $this->entityManager->persist($newRegistration);

            $customer = new Customer();
            $customer->setName($newStudent->getFirstName() .' '. $student->getName());
            $customer->setInstitution($this->getUser()->getInstitution());
            $customer->setUser($this->getUser());
            $customer->setYear($this->getUser()->getCurrentYear());
            $customer->setCode($newStudent->getFirstName());
            $customer->setPhone($newStudent->getStudentphone());
            $customer->setEmail($newStudent->getStudentemail());
            $customer->setIsEnable(true);
            $customer->setWebsite('');
            $customer->setCreatedAt(new \DateTimeImmutable());
            $customer->setUpdatedAt(new \DateTimeImmutable());
            $customer->setStudentRegistration($newRegistration);
            $this->entityManager->persist($customer);

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



