<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;

final class ClassRegistrationProvider implements ProviderInterface
{
    public function __construct(private readonly StudentRegistrationRepository $studregistrationRepository
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $studregistrations = $this->studregistrationRepository->findByStudPerClass();
        // dd($studregistrations);
        $myStudregistrations = [];
        foreach ($studregistrations as $studregistration){
            $myStudregistrations [] = [
                'id' => $studregistration->getId(),
                'speciality' => $studregistration->getSpeciality(),
                'center' => $studregistration->getCenter(),
                'pvdiplome' => $studregistration->getPvdiplome(),
                'pvselection' => $studregistration->getPvselection(),
                'ranks' => $studregistration->getRanks(),
                'repeating' => $studregistration->getRepeating(),
                'elementsprovided' => $studregistration->getElementsProvided(),
                'average' => $studregistration->getAverage(),
                'registrationdate' => $studregistration->getRegistrationDate(),
                'diploma' => $studregistration->getDiploma(),
                'school' => $studregistration->getSchool(),
                'regime' => $studregistration->getRegime(),
                'options' => $studregistration->getOptions(),
                'classe' => $studregistration->getClasse(),
                'transactions' => $studregistration->getTransactions(),
                'student' => $studregistration->getStudent(),
                'studregistration' => $studregistration->getStudregistration()->getStudent(),
                // 'year' => $studregistration->getStudent()->getYear()->getYear(),
                // 'sex' => $studregistration->getStudent()->getSex()->getName(),
                // 'country' => $studregistration->getStudent()->getCountry()->getName(),
                // 'religion' => $studregistration->getStudent()->getReligion()->getName(),
               

                // 'studregistration' => $this->serializeSearchSTR($studregistration),
            ];
        }

        return array(['studregistration' => $myStudregistrations]);
    }

    private function serializeSearchSTR(StudentRegistration $studregistration): array
    {
        $studregistrations = $this->studregistrationRepository->findBy(['studregistration' => $studregistration]);

        $myStudregistrations = [];
        foreach ($studregistrations as $studregistration)
        {
            $myStudregistrations[] = [
                'id' => $studregistration->getId(),
                'name' => $studregistration->getName(),
                //'permissions' => $this->serializeSearchPermission($menu)
            ];
        }

        return $myStudregistrations;
    }

    

}