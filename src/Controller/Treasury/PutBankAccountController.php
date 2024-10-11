<?php

namespace App\Controller\Treasury;

use App\Entity\Security\User;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\BankRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PutBankAccountController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data, Request $request, BankAccountRepository $bankAccountRepository, BranchRepository $branchRepository,
                             SystemSettingsRepository $systemSettingsRepository, BankRepository $bankRepository)
    {
        $bankAccountData = json_decode($request->getContent(), true);

        // check bank
        if (!isset($bankAccountData['bank'])) {
            return new JsonResponse(['hydra:title' => 'Bank not found!.'], 400);
        }
        $bank = $bankRepository->find($this->getIdFromApiResourceId($bankAccountData['bank']));

        // check account number
        if (!isset($bankAccountData['accountNumber'])) {
            return new JsonResponse(['hydra:title' => 'Account Number not found!.'], 400);
        }

        //
        $checkBankAccount = $bankAccountRepository->findOneBy(['bank' => $bank, 'accountNumber' => $bankAccountData['accountNumber']]);
        if ($checkBankAccount && ($checkBankAccount !== $data)) {
            return new JsonResponse(['hydra:title' => 'This Account Number already exist!.'], 400);
        }

        $bank = !isset($bankAccountData['bank']) ? null : $bankRepository->find($this->getIdFromApiResourceId($bankAccountData['bank']));
        $data->setBank($bank);
        $data->setAccountNumber($bankAccountData['accountNumber']);
        $data->setAccountName($bankAccountData['accountName']);
        $data->setCodeSwift($bankAccountData['codeSwift']);
        $data->setCodeIbam($bankAccountData['codeIbam']);
        $data->setCodeRib($bankAccountData['codeRib']);
        $data->setCodeBranch($bankAccountData['codeBranch']);
        $data->setIsDefault($bankAccountData['isDefault']);

        $bankAccountRepository->save($data);

        return $data;
    }
    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
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
