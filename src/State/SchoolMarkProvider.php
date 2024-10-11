<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\Security\Institution\InstitutionRepository;

class SchoolMarkProvider implements ProviderInterface
{
    public function __construct(
        private MarkRepository        $schoolMarkRepository,
        private InstitutionRepository $institutionRepository
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Retrieve the state from somewhere
        $studentMatricule = $uriVariables['matricule'];
        $institution = $this->institutionRepository->find(1);
        return $this->schoolMarkRepository->findSchoolMarks($institution,$studentMatricule);
    }
}
