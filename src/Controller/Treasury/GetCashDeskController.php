<?php

namespace App\Controller\Treasury;

use App\Entity\Security\User;
use App\Repository\Security\SystemSettingsRepository;
use App\Repository\Treasury\CashDeskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetCashDeskController extends AbstractController
{
    private CashDeskRepository $cashDeskRepository;
    private SystemSettingsRepository $systemSettingsRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                CashDeskRepository                     $cashDeskRepository
    )
    {
        $this->cashDeskRepository = $cashDeskRepository;
    }

    public function __invoke(Request $request):JsonResponse
    {
        $cashDeskData = [];

        if($this->getUser()->isIsBranchManager()){
            // get cash desk
            $cashDesks = $this->cashDeskRepository->findBy([], ['id' => 'DESC']);

            foreach ($cashDesks as $cashDesk)
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
        else
        {
            // get current user cash desk
            $cashDesk = $this->cashDeskRepository->findOneBy(['operator' => $this->getUser(), 'institution' => $this->getUser()->getInstitution(), 'branch' => $this->getUser()->getBranch()]);

            // check if current user is a cashier
            if($cashDesk)
            {
                // check if current user is a vault
                if($cashDesk->isIsMain())
                {
                    // get cash desk
                    $cashDesks = $this->cashDeskRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
                    foreach ($cashDesks as $cashDesk)
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
                else{

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
