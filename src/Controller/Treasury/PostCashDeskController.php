<?php

namespace App\Controller\Treasury;

use App\Entity\Security\User;
use App\Entity\Treasury\CashDesk;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\UserRepository;
use App\Repository\Setting\Finance\CurrencyRepository;
use App\Repository\Treasury\CashDeskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PostCashDeskController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data, Request $request, CashDeskRepository $cashDeskRepository, BranchRepository $branchRepository, UserRepository $userRepository, CurrencyRepository $currencyRepository)
    {
        $cashDeskData = json_decode($request->getContent(), true);

        // check if user is a branch manager
        if(!$this->getUser()->isIsBranchManager())
        {
            return new JsonResponse(['hydra:title' => 'Sorry you are not a branch manager!.'], 400);
        }

        // operator
        if (!isset($cashDeskData['operator'])) {
            return new JsonResponse(['hydra:title' => 'User not found!.'], 400);
        }
        $operator = $userRepository->find($this->getIdFromApiResourceId($cashDeskData['operator']));

        $duplicateCheckOperator = $cashDeskRepository->findOneBy(['operator' => $operator]);
        if ($duplicateCheckOperator) {
            return new JsonResponse(['hydra:title' => 'This user already has a cash desk.'], 400);
        }

        // operator branch
        $branch = $operator->getBranch();

        $code = $cashDeskData['code'];
        // check code
        $duplicateCheckCode = $cashDeskRepository->findOneBy(['code' => $code, 'branch' => $branch]);
        if ($duplicateCheckCode) {
            return new JsonResponse(['hydra:title' => 'This code already exists in this branch.'], 400);
        }

        $currency = !isset($cashDeskData['currency']) ? null : $currencyRepository->find($this->getIdFromApiResourceId($cashDeskData['currency']));

        // Check if it is main cash desk
        if (isset($cashDeskData['isMain']) && $cashDeskData['isMain']) {
            // check if there is already a main cash desk in that branch
            $isMainCashDesk = $cashDeskRepository->findOneBy(['isMain' => true, 'branch' => $branch]);
            if ($isMainCashDesk) {
                return new JsonResponse(['hydra:title' => 'This branch already has a main cashier.'], 400);
            }
        }

        $newCashDesk = new CashDesk();
        $newCashDesk->setCode($cashDeskData['code']);
        $newCashDesk->setOperator($operator);
        $newCashDesk->setCurrency($currency);
        $newCashDesk->setBalance(0);
        $newCashDesk->setIsMain($cashDeskData['isMain']);
        $newCashDesk->setIsOpen($cashDeskData['isOpen']);
        $newCashDesk->setBranch($branch);
        $newCashDesk->setInstitution($this->getUser()->getInstitution());
        $newCashDesk->setUser($this->getUser());
        $newCashDesk->setYear($this->getUser()->getCurrentYear());

        $cashDeskRepository->save($newCashDesk);

        return $newCashDesk;
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
