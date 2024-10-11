<?php

namespace App\Controller\Treasury;

use App\Entity\Security\User;
use App\Repository\Treasury\BankOperationRepository;
use App\Repository\Treasury\CashDeskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetBankOperationController extends AbstractController
{
    private CashDeskRepository $cashDeskRepository;
    private BankOperationRepository $bankOperationRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                CashDeskRepository                     $cashDeskRepository,
                                BankOperationRepository            $bankOperationRepository
    )
    {
        $this->cashDeskRepository = $cashDeskRepository;
        $this->bankOperationRepository = $bankOperationRepository;
    }

    public function __invoke(Request $request):JsonResponse
    {
        $bankAccountOperationsData = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account operations
            $bankAccountOperations = $this->bankOperationRepository->findBy([], ['id' => 'DESC']);

            foreach ($bankAccountOperations as $bankAccountOperation)
            {
                $bankAccountOperationsData[] = [
                    'id'=> $bankAccountOperation ->getId(),
                    'accountNumber'=> $bankAccountOperation->getBankAccount()->getAccountNumber(),
                    'reference'=> $bankAccountOperation->getReference(),
                    'description'=> $bankAccountOperation->getDescription(),
                    'amount'=> $bankAccountOperation->getAmount(),
                    'isValidate'=> $bankAccountOperation->isIsValidate(),
                    'createdAt'=> $bankAccountOperation->getCreatedAt()?->format('Y-m-d'),
                    'vault' => [
                        '@type' => "CashDesks",
                        'id' => $bankAccountOperation->getVault() ? $bankAccountOperation->getVault()->getId() : '',
                        'code' => $bankAccountOperation->getVault()->getCode(),
                        'balance' => $bankAccountOperation->getVault()->getBalance(),
                    ],
                    'operationCategory' => [
                        '@id' => "/api/operationCategories/".$bankAccountOperation->getOperationCategory()->getId(),
                        '@type' => "operationCategories",
                        'id' => $bankAccountOperation->getOperationCategory() ? $bankAccountOperation->getOperationCategory()->getId() : '',
                        'code' => $bankAccountOperation->getOperationCategory()->getCode(),
                        'name' => $bankAccountOperation->getOperationCategory()->getName(),
                    ],
                    'validatedBy' => [
                        '@type' => "User",
                        'id' => $bankAccountOperation->getValidateBy() ? $bankAccountOperation->getValidateBy()->getId() : '',
                        'userName' => $bankAccountOperation->getValidateBy() ? $bankAccountOperation->getValidateBy()->getUsername() : '',
                    ],
                    'branch' => [
                        '@id' => "/api/get/branch/" . $bankAccountOperation->getId(),
                        '@type' => "Branch",
                        'id' => $bankAccountOperation->getBranch() ? $bankAccountOperation->getBranch()->getId() : '',
                        'code' => $bankAccountOperation->getBranch() ? $bankAccountOperation->getBranch()->getCode() : '',
                        'name' => $bankAccountOperation->getBranch() ? $bankAccountOperation->getBranch()->getName() : '',
                    ],
                ];
            }
        }
        else{

            // get bank account operations for your branch
            $bankAccountOperations = $this->bankOperationRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

            foreach ($bankAccountOperations as $bankAccountOperation)
            {
                $bankAccountOperationsData[] = [
                    'id'=> $bankAccountOperation ->getId(),
                    'accountNumber'=> $bankAccountOperation->getBankAccount()->getAccountNumber(),
                    'reference'=> $bankAccountOperation->getReference(),
                    'description'=> $bankAccountOperation->getDescription(),
                    'amount'=> $bankAccountOperation->getAmount(),
                    'isValidate'=> $bankAccountOperation->isIsValidate(),
                    'createdAt'=> $bankAccountOperation->getCreatedAt()?->format('Y-m-d'),
                    'vault' => [
                        '@type' => "CashDesks",
                        'id' => $bankAccountOperation->getVault() ? $bankAccountOperation->getVault()->getId() : '',
                        'code' => $bankAccountOperation->getVault()->getCode(),
                        'balance' => $bankAccountOperation->getVault()->getBalance(),
                    ],
                    'operationCategory' => [
                        '@id' => "/api/operationCategories/".$bankAccountOperation->getOperationCategory()->getId(),
                        '@type' => "operationCategories",
                        'id' => $bankAccountOperation->getOperationCategory() ? $bankAccountOperation->getOperationCategory()->getId() : '',
                        'code' => $bankAccountOperation->getOperationCategory()->getCode(),
                        'name' => $bankAccountOperation->getOperationCategory()->getName(),
                    ],
                    'validatedBy' => [
                        '@type' => "User",
                        'id' => $bankAccountOperation->getValidateBy() ? $bankAccountOperation->getValidateBy()->getId() : '',
                        'userName' => $bankAccountOperation->getValidateBy() ? $bankAccountOperation->getValidateBy()->getUsername() : '',
                    ],
                    'branch' => [
                        '@id' => "/api/get/branch/" . $bankAccountOperation->getId(),
                        '@type' => "Branch",
                        'id' => $bankAccountOperation->getBranch() ? $bankAccountOperation->getBranch()->getId() : '',
                        'code' => $bankAccountOperation->getBranch() ? $bankAccountOperation->getBranch()->getCode() : '',
                        'name' => $bankAccountOperation->getBranch() ? $bankAccountOperation->getBranch()->getName() : '',
                    ],
                ];
            }
        }

        return $this->json(['hydra:member' => $bankAccountOperationsData]);
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
