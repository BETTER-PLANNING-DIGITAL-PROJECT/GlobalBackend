<?php

namespace App\Controller\Treasury;

use App\Entity\Security\User;
use App\Repository\Treasury\BankAccountRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetBankAccountController extends AbstractController
{
    private BankAccountRepository $bankAccountRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                BankAccountRepository                     $bankAccountRepository
    )
    {
        $this->bankAccountRepository = $bankAccountRepository;
    }

    public function __invoke(Request $request):JsonResponse
    {
        $bankAccountData = [];

        // get all bank account
        $bankAccounts = $this->bankAccountRepository->findBy([], ['id' => 'DESC']);

        foreach ($bankAccounts as $bankAccount)
        {
            $bankAccountData[] = [
                '@id' => "/api/get/bank-account/" . $bankAccount->getId(),
                '@type' => "BankAccount",
                'id'=> $bankAccount ->getId(),
                'code'=> $bankAccount->getCodeSwift(),
                'bank' => [
                    '@id' => "/api/get/bank/" . $bankAccount->getBank()->getId(),
                    '@type' => "Bank",
                    'id' => $bankAccount->getBank() ? $bankAccount->getBank()->getId() : '',
                    'code' => $bankAccount->getBank() ? $bankAccount->getBank()->getCode() : '',
                    'name' => $bankAccount->getBank() ? $bankAccount->getBank()->getName() : '',
                ],
                'accountNumber'=> $bankAccount->getAccountNumber(),
                'accountName'=> $bankAccount->getAccountName(),
                'balance' => $bankAccount->getBalance(),
                'codeBranch'=> $bankAccount->getCodeBranch(),
                'codeRib'=> $bankAccount->getCodeRib(),
                'codeSwift'=> $bankAccount->getCodeSwift(),
                'codeIbam'=> $bankAccount->getCodeIbam(),
                'branch' => [
                    '@id' => "/api/get/branch/" . $bankAccount->getId(),
                    '@type' => "Branch",
                    'id' => $bankAccount->getBranch() ? $bankAccount->getBranch()->getId() : '',
                    'name' => $bankAccount->getBranch() ? $bankAccount->getBranch()->getName() : '',
                ],
            ];
        }

        return $this->json(['hydra:member' => $bankAccountData]);
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
