<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\Security\Institution\InstitutionRepository;

class SchoolYearStudCourseRegProvider implements ProviderInterface
{
    public function __construct(
        private StudentRegistrationRepository $studregistrationRepository,
        private InstitutionRepository         $institutionRepository
    )
    {
    }


    /*public function getYear(array $studregistrationYear): Year
    {
        return $this->schoolYearRepository->find($studregistrationYear['1']);
    }*/

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $studentMatricule =  $uriVariables['matricule'];
        $institution = $this->institutionRepository->find(1);
        $studregistrationYears = $this->studregistrationRepository->findByStudentMatricule($institution, 'currentyear', $studentMatricule);
        return array_map('self::getYear', $studregistrationYears);
        // Retrieve the state from somewhere
    }
}
