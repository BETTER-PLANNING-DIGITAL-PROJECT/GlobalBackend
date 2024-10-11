<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\Security\Institution\InstitutionRepository;

class StudregistrationStudCourseRegProvider implements ProviderInterface
{

    public function __construct(
       private StudentRegistrationRepository $studregistrationRepository,
    private InstitutionRepository            $institutionRepository)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Retrieve the state from somewhere
        $institution = $this->institutionRepository->find(1);

        return $this->studregistrationRepository->findBy(['institution'=>$institution]);
    }
}
