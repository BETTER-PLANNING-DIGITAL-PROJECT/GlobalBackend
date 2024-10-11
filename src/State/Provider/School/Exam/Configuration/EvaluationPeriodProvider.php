<?php

namespace App\State\Provider\School\Exam\Configuration;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EvaluationPeriodProvider implements ProviderInterface
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EvaluationPeriodRepository $evaluationPeriodRepository)
    {

    }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Retrieve the state from somewhere
        $year = $this->getUser()->getCurrentYear();
        $institution = $this->getUser()->getInstitution();
        return $this->evaluationPeriodRepository->findBy(['year' => $year, 'institution' => $institution]);
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
