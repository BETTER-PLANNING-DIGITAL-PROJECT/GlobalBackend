<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\School\Schooling\Configuration\School;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\Security\Institution\InstitutionRepository;

class SchoolStudCourseRegProvider implements ProviderInterface
{

    private StudentRegistrationRepository $studregistrationRepository;
    private SchoolRepository $schoolRepository;
    private InstitutionRepository $institutionRepository;

    /**
     * @param StudentRegistrationRepository $studregistrationRepository
     * @param SchoolRepository $schoolRepository
     * @param InstitutionRepository $institutionRepository
     */
    public function __construct(StudentRegistrationRepository $studregistrationRepository, SchoolRepository $schoolRepository, InstitutionRepository $institutionRepository)
    {
        $this->studregistrationRepository = $studregistrationRepository;
        $this->schoolRepository = $schoolRepository;
        $this->institutionRepository = $institutionRepository;
    }


    public function getSchool(array $studregistrationSchool): School{
        return $this->schoolRepository->find($studregistrationSchool['1']);
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $institution = $this->institutionRepository->find(1);
        $studregistrationSchools = $this->studregistrationRepository->findByStudentMatricule($institution,'school');
        return array_map('self::getSchool',$studregistrationSchools);
        // Retrieve the state from somewhere
    }
}
