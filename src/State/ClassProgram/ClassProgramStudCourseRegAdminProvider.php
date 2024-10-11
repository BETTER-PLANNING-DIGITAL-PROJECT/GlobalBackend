<?php

namespace App\State\ClassProgram;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Security\Institution\InstitutionRepository;

class ClassProgramStudCourseRegAdminProvider implements ProviderInterface
{
    private ClassProgramRepository $classProgramRepository;
    private InstitutionRepository $institutionRepository;

    /**
     * @param ClassProgramRepository $classProgramRepository
     * @param InstitutionRepository $institutionRepository
     */
    public function __construct(ClassProgramRepository $classProgramRepository, InstitutionRepository $institutionRepository)
    {
        $this->classProgramRepository = $classProgramRepository;
        $this->institutionRepository = $institutionRepository;
    }


    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Retrieve the state from somewhere
        $institution = $this->institutionRepository->find(1);
        return $this->classProgramRepository->findByStudentMatricule($institution);
    }

}
