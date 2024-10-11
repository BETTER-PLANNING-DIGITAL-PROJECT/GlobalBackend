<?php

namespace App\Controller\School\Schooling\Registration;

use App\Entity\School\Schooling\Registration\Student;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentPreRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class ValidateStudentOnlineController extends AbstractController
{
    private EntityManagerInterface $manager;
    public function __construct(EntityManagerInterface $manager, private readonly TokenStorageInterface $tokenStorage)
    {
        $this->manager = $manager;
    }

    public function __invoke(Request                          $request,
                             InstitutionRepository            $institutionRepository,
                             StudentRepository                $studentRepository,
                             StudentRegistrationRepository    $studentRegistrationRepository,
                             StudentPreRegistrationRepository $studentOnlineRepository): JsonResponse|Response
    {
        $id = $request->get('id');
        $onlineStudent = $studentOnlineRepository->findOneBy(['id' => $id]);

        if($onlineStudent){

            $existingStudent = $studentRepository->findOneBy([
                'name' => $onlineStudent->getName(),
                'firstName' => $onlineStudent->getFirstName(),
            ]);

          if($existingStudent){
              return new JsonResponse(['hydra:description' => 'This Student already exists.'], 400);
          } else {
              $student = new Student();
              $studentRegistration = new StudentRegistration();

              // Set the data for the Student entity
              $student->setYear($onlineStudent->getYear());
              $student->setName($onlineStudent->getName());
              $student->setFirstName($onlineStudent->getFirstName());
              $student->setSex($onlineStudent->getSex());
              $student->setReligion($onlineStudent->getReligion());
              $student->setDob($onlineStudent->getDob());
              $student->setPob($onlineStudent->getPob());
              $student->setCountry($onlineStudent->getCountry());
              $student->setRegion($onlineStudent->getRegion());
              $student->setStudentphone($onlineStudent->getStudentphone());
              $student->setStudentemail($onlineStudent->getStudentemail());
              $student->setStudentProfession($onlineStudent->getStudentProfession());
              $student->setStudentDistrict($onlineStudent->getStudentDistrict());
              $student->setStudentAddress($onlineStudent->getStudentAddress());
              $student->setStudentTown($onlineStudent->getStudentTown());
              $student->setBloodGroup($onlineStudent->getBloodGroup());
              $student->setRhesus($onlineStudent->getRhesus());
              $student->setStutterer($onlineStudent->isStutterer());
              $student->setLeftHanded($onlineStudent->isLeftHanded());
              $student->setHearingProblem($onlineStudent->isHearingProblem());
              $student->setEyeProblem($onlineStudent->isEyeProblem());
              $student->setMedicalHistory($onlineStudent->getMedicalHistory());
              $student->setFathername($onlineStudent->getFathername());
              $student->setFatheremail($onlineStudent->getFatheremail());
              $student->setFatherphone($onlineStudent->getFatherphone());
              $student->setFatherprofession($onlineStudent->getFatherprofession());
              $student->setMothername($onlineStudent->getMothername());
              $student->setMotheremail($onlineStudent->getMotheremail());
              $student->setMotherphone($onlineStudent->getMotherphone());
              $student->setMotherprofession($onlineStudent->getMotherprofession());
              $student->setGuardianname($onlineStudent->getGuardianname());
              $student->setGuardianemail($onlineStudent->getGuardianemail());
              $student->setGuardianphone($onlineStudent->getGuardianphone());
              $student->setGuardianprofession($onlineStudent->getGuardianprofession());
              $student->setPartnerName($onlineStudent->getPartnerName());
              $student->setPartnerEmail($onlineStudent->getPartnerEmail());
              $student->setPartnerPhone($onlineStudent->getPartnerPhone());
              $student->setPartnerProfession($onlineStudent->getPartnerProfession());
              $student->setNumberOfChildren($onlineStudent->getNumberOfChildren());

              $student->setMatricule('MAT');
              $student->setStatus('registered');
              $student->setInstitution($this->getUser()->getInstitution());
              $student->setUser($this->getUser());

              // Set the data for the StudentRegistration entity
              $studentRegistration->setCenter($onlineStudent->getCenter());
              $studentRegistration->setPvdiplome($onlineStudent->getPvdiplome());
              $studentRegistration->setPvselection($onlineStudent->getPvselection());
              $studentRegistration->setRepeating($onlineStudent->getRepeating());
              $studentRegistration->setAverage($onlineStudent->getAverage());
              $studentRegistration->setSchool($onlineStudent->getSchool());
              $studentRegistration->setOptions($onlineStudent->getOptions());
              $studentRegistration->setDiploma($onlineStudent->getDiploma());
              $studentRegistration->setRegime($onlineStudent->getRegime());
              $studentRegistration->setClasse($onlineStudent->getClasse());
              $studentRegistration->setRanks($onlineStudent->getRanks());
              $studentRegistration->setCycle($onlineStudent->getCycle());
              $studentRegistration->setLevel($onlineStudent->getLevel());
              $studentRegistration->setSpeciality($onlineStudent->getSpeciality());
              $studentRegistration->setLevel($onlineStudent->getLevel());

              $studentRegistration->setInstitution($this->getUser()->getInstitution());
              $studentRegistration->setUser($this->getUser());
              $studentRegistration->setStatus('registered');
              $studentRegistration->setStudent($student);

              $studentRepository->save($student);
              $studentRegistrationRepository->save($studentRegistration);
          }


        }

        $this->manager->flush();

        return new Response(null, Response::HTTP_OK);
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
