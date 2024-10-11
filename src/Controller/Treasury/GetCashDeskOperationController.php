<?php

namespace App\Controller\Treasury;

use App\Entity\Security\User;
use App\Repository\Treasury\CashDeskOperationRepository;
use App\Repository\Treasury\CashDeskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetCashDeskOperationController extends AbstractController
{
    private CashDeskRepository $cashDeskRepository;
    private CashDeskOperationRepository $cashDeskOperationRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                CashDeskRepository                     $cashDeskRepository,
                                CashDeskOperationRepository            $cashDeskOperationRepository
    )
    {
        $this->cashDeskRepository = $cashDeskRepository;
        $this->cashDeskOperationRepository = $cashDeskOperationRepository;
    }

    public function __invoke(Request $request):JsonResponse
    {
        $cashDeskOperationsData = [];

        if($this->getUser()->isIsBranchManager()){
            // get cash desk operations
            $cashDeskOperations = $this->cashDeskOperationRepository->findBy([], ['id' => 'DESC']);

            foreach ($cashDeskOperations as $cashDeskOperation)
            {
                $cashDeskOperationsData[] = [
                    'id'=> $cashDeskOperation ->getId(),
                    'reference'=> $cashDeskOperation->getReference(),
                    'description'=> $cashDeskOperation->getDescription(),
                    'amount'=> $cashDeskOperation->getAmount(),
                    'isValidate'=> $cashDeskOperation->isIsValidate(),
                    'createdAt'=> $cashDeskOperation->getCreatedAt()?->format('Y-m-d'),
                    'cashDesk' => [
                        '@type' => "CashDesks",
                        'id' => $cashDeskOperation->getCashDesk() ? $cashDeskOperation->getCashDesk()->getId() : '',
                        'code' => $cashDeskOperation->getCashDesk()->getCode(),
                        'balance' => $cashDeskOperation->getCashDesk()->getBalance(),
                    ],
                    'operationCategory' => [
                        '@id' => "/api/operationCategories/".$cashDeskOperation->getOperationCategory()->getId(),
                        '@type' => "operationCategories",
                        'id' => $cashDeskOperation->getOperationCategory() ? $cashDeskOperation->getOperationCategory()->getId() : '',
                        'code' => $cashDeskOperation->getOperationCategory()->getCode(),
                        'name' => $cashDeskOperation->getOperationCategory()->getName(),
                    ],
                    'validatedBy' => [
                        '@type' => "User",
                        'id' => $cashDeskOperation->getValidateBy() ? $cashDeskOperation->getValidateBy()->getId() : '',
                        'userName' => $cashDeskOperation->getValidateBy() ? $cashDeskOperation->getValidateBy()->getUsername() : '',
                    ],
                    'branch' => [
                        '@id' => "/api/get/branch/" . $cashDeskOperation->getId(),
                        '@type' => "Branch",
                        'id' => $cashDeskOperation->getBranch() ? $cashDeskOperation->getBranch()->getId() : '',
                        'code' => $cashDeskOperation->getBranch() ? $cashDeskOperation->getBranch()->getCode() : '',
                        'name' => $cashDeskOperation->getBranch() ? $cashDeskOperation->getBranch()->getName() : '',
                    ],
                    'vault'=> true,
                ];
            }
        }
        else{
            // get current user cash desk
            $cashDesk = $this->cashDeskRepository->findOneBy(['operator' => $this->getUser(), 'institution' => $this->getUser()->getInstitution()]);

            // check if current user is a cashier
            if($cashDesk)
            {
                // check if current user is a vault
                if($cashDesk->isIsMain())
                {
                    // last five cash desk operations
                    $cashDeskOperations = $this->cashDeskOperationRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

                    foreach ($cashDeskOperations as $cashDeskOperation)
                    {
                        $cashDeskOperationsData[] = [
                            'id'=> $cashDeskOperation ->getId(),
                            'reference'=> $cashDeskOperation->getReference(),
                            'description'=> $cashDeskOperation->getDescription(),
                            'amount'=> $cashDeskOperation->getAmount(),
                            'isValidate'=> $cashDeskOperation->isIsValidate(),
                            'createdAt'=> $cashDeskOperation->getCreatedAt()?->format('Y-m-d'),
                            'cashDesk' => [
                                '@type' => "CashDesks",
                                'id' => $cashDeskOperation->getCashDesk() ? $cashDeskOperation->getCashDesk()->getId() : '',
                                'code' => $cashDeskOperation->getCashDesk()->getCode(),
                                'balance' => $cashDeskOperation->getCashDesk()->getBalance(),
                            ],
                            'operationCategory' => [
                                '@id' => "/api/operationCategories/".$cashDeskOperation->getOperationCategory()->getId(),
                                '@type' => "operationCategories",
                                'id' => $cashDeskOperation->getOperationCategory() ? $cashDeskOperation->getOperationCategory()->getId() : '',
                                'code' => $cashDeskOperation->getOperationCategory()->getCode(),
                                'name' => $cashDeskOperation->getOperationCategory()->getName(),
                            ],
                            'validatedBy' => [
                                '@type' => "User",
                                'id' => $cashDeskOperation->getValidateBy() ? $cashDeskOperation->getValidateBy()->getId() : '',
                                'userName' => $cashDeskOperation->getValidateBy() ? $cashDeskOperation->getValidateBy()->getUsername() : '',
                            ],
                            'branch' => [
                                '@id' => "/api/get/branch/" . $cashDeskOperation->getId(),
                                '@type' => "Branch",
                                'id' => $cashDeskOperation->getBranch() ? $cashDeskOperation->getBranch()->getId() : '',
                                'code' => $cashDeskOperation->getBranch() ? $cashDeskOperation->getBranch()->getCode() : '',
                                'name' => $cashDeskOperation->getBranch() ? $cashDeskOperation->getBranch()->getName() : '',
                            ],
                            'vault'=> true,
                        ];
                    }
                }
                else{
                    // last five cash desk operations
                    $cashDeskOperations = $this->cashDeskOperationRepository->findBy(['cashDesk' => $cashDesk], ['id' => 'DESC']);

                    foreach ($cashDeskOperations as $cashDeskOperation)
                    {
                        $cashDeskOperationsData[] = [
                            'id'=> $cashDeskOperation ->getId(),
                            'reference'=> $cashDeskOperation->getReference(),
                            'description'=> $cashDeskOperation->getDescription(),
                            'amount'=> $cashDeskOperation->getAmount(),
                            'isValidate'=> $cashDeskOperation->isIsValidate(),
                            'createdAt'=> $cashDeskOperation->getCreatedAt()?->format('Y-m-d'),
                            'cashDesk' => [
                                '@type' => "CashDesks",
                                'id' => $cashDeskOperation->getCashDesk() ? $cashDeskOperation->getCashDesk()->getId() : '',
                                'code' => $cashDeskOperation->getCashDesk()->getCode(),
                                'balance' => $cashDeskOperation->getCashDesk()->getBalance(),
                            ],
                            'operationCategory' => [
                                '@id' => "/api/operationCategories/".$cashDeskOperation->getOperationCategory()->getId(),
                                '@type' => "operationCategories",
                                'id' => $cashDeskOperation->getOperationCategory() ? $cashDeskOperation->getOperationCategory()->getId() : '',
                                'code' => $cashDeskOperation->getOperationCategory()->getCode(),
                                'name' => $cashDeskOperation->getOperationCategory()->getName(),
                            ],
                            'validatedBy' => [
                                '@type' => "User",
                                'id' => $cashDeskOperation->getValidateBy() ? $cashDeskOperation->getValidateBy()->getId() : '',
                                'userName' => $cashDeskOperation->getValidateBy() ? $cashDeskOperation->getValidateBy()->getUsername() : '',
                            ],
                            'branch' => [
                                '@id' => "/api/get/branch/" . $cashDeskOperation->getId(),
                                '@type' => "Branch",
                                'id' => $cashDeskOperation->getBranch() ? $cashDeskOperation->getBranch()->getId() : '',
                                'code' => $cashDeskOperation->getBranch() ? $cashDeskOperation->getBranch()->getCode() : '',
                                'name' => $cashDeskOperation->getBranch() ? $cashDeskOperation->getBranch()->getName() : '',
                            ],
                            'vault'=> false,
                        ];
                    }
                }
            }
        }

        return $this->json(['hydra:member' => $cashDeskOperationsData]);
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
