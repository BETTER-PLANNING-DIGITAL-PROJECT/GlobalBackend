<?php

namespace App\State\Provider\Treasury;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Budget\Budget;
use App\Entity\Security\User;
use App\Repository\Budget\BudgetExerciseRepository;
use App\Repository\Budget\BudgetRepository;
use App\Repository\Budget\NeedsRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ValidatedBudgetTransactionProvider implements ProviderInterface
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly NeedsRepository $needsRepo)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $needs = $this->needsRepo->findAll();

        $validatedBudgetTransactions = [];
        foreach ($needs as $need){
            if($need->isIsValidated() === true){
                $validatedBudgetTransactions[] = [
                    '@id' => "/api/get/budget-needs/".$need->getId(),
                    '@type' => 'Needs',
                    'id'=> $need ->getId(),
                    'reference'=> $need->getReference(),
                    'requestedAmount'=> $need ->getRequestAmount(),
                    'validatedAmount'=> $need ->getValidatedAmount(),
                    'vat'=> $need->getVat(),
                    'settled_At'=> $need->getSettledAt(),
                    'settledBy'=> $need->getSettledBy(),
                    'applicant'=> $need->getApplicant(),
                    'reason'=> $need->getReason(),
                    'isCash'=> $need->isIsCash(),
                    'isValidated'=> $need->isIsValidated(),
                    'isOpen'=> $need->isIsOpen(),
                    'isEdited'=> $need->isIsEdited(),
                    'budgetExercise' => $need->getExercise() ? [
                        '@id' => "/api/exercise/".$need->getExercise()->getId(),
                        '@type' => "Exercise",
                        'id' => $need->getExercise()->getId(),
                        'code' => $need->getExercise()->getCode(),
                        'startAt' => $need->getExercise()->getStartAt(),
                        'endAt' => $need->getExercise()->getEndAt(),
                    ] : '',

                    'budget' => $need->getBudget() ? [
                        '@id' => "/api/budget/".$need->getBudget()->getId(),
                        '@type' => "Budget",
                        'id' => $need->getBudget()->getId(),
                        'amount' => $need->getBudget()->getAmount(),
                        'validatedAmount' => $need->getBudget()->getValidatedAmount(),
                        'allocatedAmount' => $need->getBudget()->getAllocatedAmount(),
                        'spentAmount' => $need->getBudget()->getSpentAmount(),
                        'leftAmount' => $need->getBudget()->getLeftAmount(),
                        'line' => $need->getBudget()->getLine() ? [
                            '@id' => "/api/line/".$need->getBudget()->getLine()->getId(),
                            '@type' => "Line",
                            'id' => $need->getBudget()->getLine()->getId(),
                            'code' => $need->getBudget()->getLine()->getCode(),
                            'name' => $need->getBudget()->getLine()->getName(),
                        ] : '',
                    ] : '',
                ];
            }
        }
        return new JsonResponse(['hydra:member' => $validatedBudgetTransactions]);
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
