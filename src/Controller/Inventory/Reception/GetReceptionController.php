<?php

namespace App\Controller\Inventory\Reception;

use App\Entity\Security\User;
use App\Repository\Inventory\ReceptionItemRepository;
use App\Repository\Inventory\ReceptionRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetReceptionController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                )
    {
    }

    public function __invoke(ReceptionItemRepository $receptionItemRepository,
                             ReceptionRepository $receptionRepository,
                             SystemSettingsRepository $systemSettingsRepository,
                             BranchRepository $branchRepository,
                             Request $request): JsonResponse
    {
        $receptionList = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $receptions = $receptionRepository->findBy([], ['id' => 'DESC']);

            foreach ($receptions as $reception){
                $receptionList[] = [
                    'id' => $reception->getId(),
                    '@id' => '/api/get/receptions/'.$reception->getId(),
                    'type' => 'Reception',
                    'reference' =>$reception->getReference(),
                    'contact' => [
                        'id' => $reception->getContact() ? $reception->getContact()->getId() : '',
                        'type' => 'Contact',
                        'name' => $reception->getContact() ? $reception->getContact()->getName() : '',
                    ],
                    'operationType' => [
                        'id' => $reception->getOperationType() ? $reception->getOperationType()->getId() : '',
                        'type' => 'OperationType',
                        'name' => $reception->getOperationType() ? $reception->getOperationType()->getName() : '',
                    ],
                    'branch' => [
                        '@id' => "/api/get/branch/" . $reception->getBranch()->getId(),
                        '@type' => "Branch",
                        'id' => $reception->getBranch() ? $reception->getBranch()->getId() : '',
                        'name' => $reception->getBranch() ? $reception->getBranch()->getName() : '',
                    ],
                    'receiveAt' => $reception->getReceiveAt() ? $reception->getReceiveAt()->format('Y-m-d') : '',
                    'validateAt' => $reception->getValidateAt() ? $reception->getValidateAt()->format('Y-m-d') : '',
                    'description' => $reception->getDescription(),
                    'originalDocument' => $reception->getOriginalDocument(),
                    'status' => $reception->getStatus(),

                ];
            }
        }
        else
        {
            $systemSettings = $systemSettingsRepository->findOneBy([]);
            if($systemSettings)
            {
                if($systemSettings->isIsBranches())
                {
                    $userBranches = $this->getUser()->getUserBranches();
                    foreach ($userBranches as $userBranch) {

                        // get cash desk
                        $receptions = $receptionRepository->findBy(['branch' => $userBranch], ['id' => 'DESC']);
                        foreach ($receptions as $reception){
                            $receptionList[] = [
                                'id' => $reception->getId(),
                                '@id' => '/api/get/receptions/'.$reception->getId(),
                                'type' => 'Reception',
                                'contact' => [
                                    'id' => $reception->getContact() ? $reception->getContact()->getId() : '',
                                    'type' => 'Contact',
                                    'name' => $reception->getContact() ? $reception->getContact()->getName() : '',
                                ],
                                'operationType' => [
                                    'id' => $reception->getOperationType() ? $reception->getOperationType()->getId() : '',
                                    'type' => 'OperationType',
                                    'name' => $reception->getOperationType() ? $reception->getOperationType()->getName() : '',
                                ],
                                'branch' => [
                                    '@id' => "/api/get/branch/" . $reception->getBranch()->getId(),
                                    '@type' => "Branch",
                                    'id' => $reception->getBranch() ? $reception->getBranch()->getId() : '',
                                    'name' => $reception->getBranch() ? $reception->getBranch()->getName() : '',
                                ],

                                'receiveAt' => $reception->getReceiveAt() ? $reception->getReceiveAt()->format('Y-m-d') : '',
                                'validateAt' => $reception->getValidateAt() ? $reception->getValidateAt()->format('Y-m-d') : '',
                                'description' => $reception->getDescription(),
                                'originalDocument' => $reception->getOriginalDocument(),
                                'status' => $reception->getStatus(),

                            ];
                        }
                    }
                }
                else {
                    $receptions = $receptionRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

                    foreach ($receptions as $reception){
                        $receptionList[] = [
                            'id' => $reception->getId(),
                            '@id' => '/api/get/receptions/'.$reception->getId(),
                            'type' => 'Reception',
                            'contact' => [
                                'id' => $reception->getContact() ? $reception->getContact()->getId() : '',
                                'type' => 'Contact',
                                'name' => $reception->getContact() ? $reception->getContact()->getName() : '',
                            ],
                            'operationType' => [
                                'id' => $reception->getOperationType() ? $reception->getOperationType()->getId() : '',
                                'type' => 'OperationType',
                                'name' => $reception->getOperationType() ? $reception->getOperationType()->getName() : '',
                            ],
                            'branch' => [
                                '@id' => "/api/get/branch/" . $reception->getBranch()->getId(),
                                '@type' => "Branch",
                                'id' => $reception->getBranch() ? $reception->getBranch()->getId() : '',
                                'name' => $reception->getBranch() ? $reception->getBranch()->getName() : '',
                            ],

                            'receiveAt' => $reception->getReceiveAt() ? $reception->getReceiveAt()->format('Y-m-d') : '',
                            'validateAt' => $reception->getValidateAt() ? $reception->getValidateAt()->format('Y-m-d') : '',
                            'description' => $reception->getDescription(),
                            'originalDocument' => $reception->getOriginalDocument(),
                            'status' => $reception->getStatus(),

                        ];
                    }

                }
            }
        }

        return $this->json(['hydra:member' => $receptionList]);
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
