<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Security\Session\Year;
use App\Entity\Setting\Location\Country;
use App\Entity\Setting\Person\Religion;
use App\Entity\Setting\Person\Sex;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;

final class Registration3Provider implements ProviderInterface
{
   
    public function __construct(private readonly StudentRegistrationRepository $studregistrationRepository
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array
    {
        $studregistrations = $this->studregistrationRepository->find($uriVariables["id"]);
        $student = $studregistrations->getStudent();
        //$myStudregistrations [] = ["stud" => $student];
    

        $myStudregistrations = [
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
            // 'studregistration' => $this->serializeSearchSTR($studregistration),
        ];
       

        return array(['student' => $myStudregistrations]);
    }

    private function serializeCountry(?Country $country): array
    {  
            $myStudregistrations = [
                'id' => $country ? $country->getId() : "",
                'name' => $country ? $country->getName() : "",
                //'permissions' => $this->serializeSearchPermission($menu)
            ];

        return $myStudregistrations;
    }

    private function serializeYear(?Year $year): array
    {  
            $myStudregistrations = [
                'id' => $year ? $year->getId() : "",
                'year' => $year ? $year->getYear() : "",
                //'permissions' => $this->serializeSearchPermission($menu)
            ];

        return $myStudregistrations;
    }

    private function serializeSex(?Sex $sex): array
    {  
            $myStudregistrations = [
                'id' => $sex ? $sex->getId() : "",
                'name' => $sex ? $sex->getName() : "",
                //'permissions' => $this->serializeSearchPermission($menu)
            ];

        return $myStudregistrations;
    }

    private function serializeReligion(?Religion $religion): array
    {  
            $myStudregistrations = [
                'id' => $religion ? $religion->getId() : "",
                'name' => $religion ? $religion->getName() : "",
                //'permissions' => $this->serializeSearchPermission($menu)
            ];

        return $myStudregistrations;
    }

    

}