<?php

namespace App\State\Provider\School\Schooling\Registration;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Security\Session\Year;
use App\Entity\Setting\Location\Country;
use App\Entity\Setting\Person\Religion;
use App\Entity\Setting\Person\Sex;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;

final class StudentFromStudentRegistrationProvider implements ProviderInterface
{
   
    public function __construct(private readonly StudentRegistrationRepository $studentRegistrationRepository
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array
    {
        $studentRegistration = $this->studentRegistrationRepository->find($uriVariables["id"]);
        $student = $studentRegistration->getStudent();

        $myStudent = [
            'id' => $student->getId(),
            'name' => $student->getName(),
            'year' => $this->serializeYear($student->getYear()),
            'sex' => $this->serializeSex($student->getSex()),
            'country' => $this->serializeCountry($student->getCountry()),
            'region' => $student->getRegion(),
            'religion' => $this->serializeReligion($student->getReligion()),
            'matricule' => $student->getMatricule(),
            'othermatricule' => $student->getOtherMatricule(),
            'dob' => $student->getDob() ? $student->getDob()->format("Y-m-d") : "",
            'pob' => $student->getPob(),
            'studentphone' => $student->getStudentPhone(),
            'studentemail' => $student->getStudentEmail(),
            'fathername' => $student->getFatherName(),
            'fatherphone' => $student->getFatherPhone(),
            'fatheremail' => $student->getFatherEmail(),
            'fatherprofession' => $student->getFatherProfession(),
            'mothername' => $student->getMotherName(),
            'motherphone' => $student->getMotherPhone(),
            'motheremail' => $student->getMotherEmail(),
            'motherprofession' => $student->getMotherProfession(),
            'guardianname' => $student->getGuardianName(),
            'guardianphone' => $student->getGuardianPhone(),
            'guardianemail' => $student->getGuardianEmail(),
            'guardianprofession' => $student->getGuardianProfession(),
        ];
       

        return array(['student' => $myStudent]);
    }

    private function serializeCountry(?Country $country): array
    {
        $myStudent = [
                'id' => $country ? $country->getId() : "",
                'name' => $country ? $country->getName() : "",
            ];

        return $myStudent;
    }

    private function serializeYear(?Year $year): array
    {
        $myStudent = [
                'id' => $year ? $year->getId() : "",
                'year' => $year ? $year->getYear() : "",
            ];

        return $myStudent;
    }

    private function serializeSex(?Sex $sex): array
    {
        $myStudent = [
                'id' => $sex ? $sex->getId() : "",
                'name' => $sex ? $sex->getName() : "",
            ];

        return $myStudent;
    }

    private function serializeReligion(?Religion $religion): array
    {
        $myStudent = [
                'id' => $religion ? $religion->getId() : "",
                'name' => $religion ? $religion->getName() : "",
            ];

        return $myStudent;
    }

}