<?php

namespace App\Controller\Treasury;

use App\Entity\Security\User;
use App\Repository\Treasury\CashDeskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetCurrentUserCashDeskController extends AbstractController
{
    private CashDeskRepository $cashDeskRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                CashDeskRepository                     $cashDeskRepository
    )
    {
        $this->cashDeskRepository = $cashDeskRepository;
    }

    public function __invoke(Request $request):JsonResponse
    {
        $cashDeskData = [];

        // get cash desk
        $cashDesks = $this->cashDeskRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        foreach ($cashDesks as $cashDesk)
        {
            if($cashDesk->getOperator() !== $this->getUser())
            {
                $cashDeskData[] = [
                    '@id' => "/api/get/cash-desk/" . $cashDesk->getId(),
                    '@type' => "CashDesk",
                    'id'=> $cashDesk ->getId(),
                    'code'=> $cashDesk->getCode(),
                    'operator' => [
                        '@type' => "User",
                        'id' => $cashDesk->getOperator() ? $cashDesk->getOperator()->getId() : '',
                        'firstname' => $cashDesk->getOperator() ? $cashDesk->getOperator()->getFirstname() : '',
                        'lastname' => $cashDesk->getOperator() ? $cashDesk->getOperator()->getLastname() : '',
                    ],
                    'dailyDeposit'=> $cashDesk->getDailyDeposit(),
                    'dailyWithdrawal'=> $cashDesk->getDailyWithdrawal(),
                    'balance' => $cashDesk->getBalance(),
                    'isOpen'=> $cashDesk->isIsOpen(),
                    'isMain'=> $cashDesk->isIsMain(),
                    'branch' => [
                        '@id' => "/api/get/branch/" . $cashDesk->getId(),
                        '@type' => "Branch",
                        'id' => $cashDesk->getBranch() ? $cashDesk->getBranch()->getId() : '',
                        'code' => $cashDesk->getBranch() ? $cashDesk->getBranch()->getCode() : '',
                        'name' => $cashDesk->getBranch() ? $cashDesk->getBranch()->getName() : '',
                    ],
                ];
            }
        }

        return $this->json(['hydra:member' => $cashDeskData]);
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
