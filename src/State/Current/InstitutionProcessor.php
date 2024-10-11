<?php

namespace App\State\Current;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\Security\Institution\InstitutionRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class InstitutionProcessor implements ProcessorInterface
{
    public function __construct(private ProcessorInterface $persistProcessor, private InstitutionRepository $institutionRepository)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        // Get current institution
        $institution = $this->institutionRepository->find(1);

        // Handle the state
//        if ($data instanceof Campus or $data instanceof Building or $data instanceof Room or $data instanceof ServiceDept or $data instanceof Program or $data instanceof Speciality or $data instanceof ClassCategory  or $data instanceof SchoolClass or $data instanceof Option or $data instanceof Year) {
//            $data->setInstitution($institution);
//        }

        $data->setInstitution($institution);

        $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
