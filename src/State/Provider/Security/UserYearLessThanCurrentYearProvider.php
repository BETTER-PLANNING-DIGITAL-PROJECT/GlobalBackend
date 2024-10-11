<?php

namespace App\State\Provider\Security;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Security\User;
use App\Repository\Security\Session\YearRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserYearLessThanCurrentYearProvider implements ProviderInterface
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly YearRepository $yearRepository)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $years = $this->yearRepository->findAll();
        $currentYear = $this->getUser()->getCurrentYear()->getYear();

        $lesserYears = [];
        foreach ($years as $year){
            if($currentYear > $year->getYear()){
                $lesserYears[] = [
                    'id'=> $year ->getId(),
                    'startAt'=> $year ->getStartAt(),
                    'endAt'=> $year->getEndAt(),
                    'year'=> $year->getYear(),
                    'objective'=> $year->getObjective(),
                ];
             }
        }
//        dd($lesserYears);
        return new JsonResponse(['hydra:member' => $lesserYears]);
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
