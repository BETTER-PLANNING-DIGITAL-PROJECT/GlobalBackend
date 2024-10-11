<?php

namespace App\Controller\School\Study\Teacher;

use App\Entity\School\Study\Teacher\Teacher;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SpecialityRepository;
use App\Repository\School\Study\Configuration\SubjectRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use App\Repository\Security\Session\YearRepository;
use App\Repository\Setting\Location\CountryRepository;
use App\Repository\Setting\Person\CivilityRepository;
use App\Repository\Setting\Person\MaritalStatusRepository;
use App\Repository\Setting\Person\ReligionRepository;
use App\Repository\Setting\Person\SexRepository;
use App\Repository\Setting\School\DiplomaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class ImportTeacherController extends AbstractController
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
                             TeacherRepository $teacherRepository,
                             YearRepository $schoolYearRepository,
                             SexRepository $sexRepository,
                             CivilityRepository $civilityRepository,
                             MaritalStatusRepository $maritalStatusRepository,
                             CountryRepository $countryRepository,
                             ReligionRepository $religionRepository,
                             DiplomaRepository $diplomaRepository,
                             SpecialityRepository $specialityRepository,
                             SubjectRepository $subjectRepository): Response
    {
        $data = $this->jsondecode();

        if(!isset($data->data))
            return throw new BadRequestHttpException('file is compulsory');

        $teachers = $data->data;
        if (!$teachers) {
            throw new BadRequestHttpException('"file" is required');
        }

        foreach ($teachers as $teacher)
        {
            $yearName = $schoolYearRepository->findOneBy(['year' => $teacher->year]);
            if(!$yearName){
                return new JsonResponse(['hydra:title' => 'Year: '.$teacher->year. ' in line '. $teacher->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $teacherRegistrationNumber = $teacherRepository->findOneBy(['registrationNumber' => $teacher->registrationNumber]);
            if ($teacherRegistrationNumber){
                return new JsonResponse(['hydra:title' => 'Registration number: '.$teacherRegistrationNumber->getRegistrationNumber(). ' in line '. $teacher->line .' already exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            if(!$teacher->name){
                return new JsonResponse(['hydra:title' => 'Name is missing in line '. $teacher->line],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $sexName = $sexRepository->findOneBy(['name' => $teacher->sex]);
            if(!$sexName){
                return new JsonResponse(['hydra:title' => 'Sex: '.$teacher->sex. ' in line '. $teacher->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            /*// date of birth
            if(!$teacher->dob){
                return new JsonResponse(['hydra:title' => 'Birth Date is missing in line '. $teacher->line],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            // place of birth
            if(!$teacher->pob){
                return new JsonResponse(['hydra:title' => 'Place of Birth is missing in line '. $teacher->line],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }*/

            $civilityName = $civilityRepository->findOneBy(['code' => $teacher->civility]);
            if(!$civilityName){
                return new JsonResponse(['hydra:title' => 'Civility: '.$teacher->civility. ' in line '. $teacher->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            $countryName = $countryRepository->findOneBy(['name' => $teacher->country]);
            if(!$countryName){
                return new JsonResponse(['hydra:title' => 'Country: '.$teacher->country. ' in line '. $teacher->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            if(isset($teacher->religion))
            {
                $religionName = $religionRepository->findOneBy(['name' => $teacher->religion]);
                if(!$religionName){
                    return new JsonResponse(['hydra:title' => 'Religion: '.$teacher->religion. ' in line '. $teacher->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $religionName = null;
            }

            if(!$teacher->phone){
                return new JsonResponse(['hydra:title' => 'Phone is missing in line '. $teacher->line],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            if(!$teacher->email){
                return new JsonResponse(['hydra:title' => 'Email is missing in line '. $teacher->line],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }

            if(isset($teacher->maritalStatus))
            {
                $maritalStatusName = $maritalStatusRepository->findOneBy(['name' => $teacher->maritalStatus]);
                if(!$maritalStatusName){
                    return new JsonResponse(['hydra:title' => 'Marital Status: '.$teacher->maritalStatus. ' in line '. $teacher->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $maritalStatusName = null;
            }

            if(isset($teacher->subject))
            {
                $subjectName = $subjectRepository->findOneBy(['name' => $teacher->subject]);
                if(!$subjectName){
                    return new JsonResponse(['hydra:title' => 'Subject: '.$teacher->subject. ' in line '. $teacher->line .' does not exist'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
            else{
                $subjectName = null;
            }

            $newTeacher = new Teacher();

            $newTeacher->setYear($yearName);
            $newTeacher->setRegistrationNumber($teacher->registrationNumber);
            $newTeacher->setName($teacher->name);
            $new = new \DateTimeImmutable($teacher->dob);
            $newTeacher->setDob($new);
            $newTeacher->setPob($teacher->pob);
            $newTeacher->setSex($sexName);
            $newTeacher->setCivility($civilityName);
            $newTeacher->setCountry($countryName);
            $newTeacher->setReligion($religionName);
            $newTeacher->setPhone($teacher->phone);
            $newTeacher->setEmail($teacher->email->text);
            $newTeacher->setAddress($teacher->address);
            $newTeacher->setMaritalStatus($maritalStatusName);
            $newTeacher->setSpeciality($teacher->speciality);
            $newTeacher->setBaseSalary($teacher->baseSalary);
            $newTeacher->addSubject($subjectName);
            $newTeacher->setInstitution($this->getUser()->getInstitution());
            $this->entityManager->persist($newTeacher);
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



