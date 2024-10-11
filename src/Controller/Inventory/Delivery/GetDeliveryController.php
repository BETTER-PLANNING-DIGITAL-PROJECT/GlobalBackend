<?php

namespace App\Controller\Inventory\Delivery;

use App\Entity\Security\User;
use App\Repository\Inventory\DeliveryItemRepository;
use App\Repository\Inventory\DeliveryRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetDeliveryController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                )
    {
    }

    public function __invoke(DeliveryItemRepository $deliveryItemRepository,
                             DeliveryRepository $deliveryRepository,
                             SystemSettingsRepository $systemSettingsRepository,
                             BranchRepository $branchRepository,
                             Request $request): JsonResponse
    {

        $deliveryList = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $deliveries = $deliveryRepository->findBy([], ['id' => 'DESC']);

            foreach ($deliveries as $delivery){
                $deliveryList[] = [
                    'id' => $delivery->getId(),
                    '@id' => '/api/get/delivery/'.$delivery->getId(),
                    'type' => 'Delivery',
                    'reference' =>$delivery->getReference(),

                    'contact' => [
                        'id' => $delivery->getContact() ? $delivery->getContact()->getId() : '',
                        'type' => 'Contact',
                        'name' => $delivery->getContact() ? $delivery->getContact()->getName() : '',
                    ],
                    'operationType' => [
                        'id' => $delivery->getOperationType() ? $delivery->getOperationType()->getId() : '',
                        'type' => 'OperationType',
                        'name' => $delivery->getOperationType() ? $delivery->getOperationType()->getName() : '',
                    ],
                    'branch' => [
                        '@id' => "/api/get/branch/" . $delivery->getBranch()->getId(),
                        '@type' => "Branch",
                        'id' => $delivery->getBranch() ? $delivery->getBranch()->getId() : '',
                        'name' => $delivery->getBranch() ? $delivery->getBranch()->getName() : '',
                    ],

                    'deliveryAt' => $delivery->getDeliveryAt() ? $delivery->getDeliveryAt()->format('Y-m-d') : '',
                    'validateAt' => $delivery->getValidateAt() ? $delivery->getValidateAt()->format('Y-m-d') : '',
                    'description' => $delivery->getDescription(),
                    'originalDocument' => $delivery->getOriginalDocument(),
                    'status' => $delivery->getStatus(),

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
                        $deliveries = $deliveryRepository->findBy(['branch' => $userBranch], ['id' => 'DESC']);
                        foreach ($deliveries as $delivery){
                            $deliveryList[] = [
                                'id' => $delivery->getId(),
                                '@id' => '/api/get/delivery/'.$delivery->getId(),
                                'type' => 'Delivery',
                                'reference' =>$delivery->getReference(),

                                'contact' => [
                                    'id' => $delivery->getContact() ? $delivery->getContact()->getId() : '',
                                    'type' => 'Contact',
                                    'name' => $delivery->getContact() ? $delivery->getContact()->getName() : '',
                                ],
                                'operationType' => [
                                    'id' => $delivery->getOperationType() ? $delivery->getOperationType()->getId() : '',
                                    'type' => 'OperationType',
                                    'name' => $delivery->getOperationType() ? $delivery->getOperationType()->getName() : '',
                                ],
                                'branch' => [
                                    '@id' => "/api/get/branch/" . $delivery->getBranch()->getId(),
                                    '@type' => "Branch",
                                    'id' => $delivery->getBranch() ? $delivery->getBranch()->getId() : '',
                                    'name' => $delivery->getBranch() ? $delivery->getBranch()->getName() : '',
                                ],

                                'deliveryAt' => $delivery->getDeliveryAt() ? $delivery->getDeliveryAt()->format('Y-m-d') : '',
                                'validateAt' => $delivery->getValidateAt() ? $delivery->getValidateAt()->format('Y-m-d') : '',
                                'description' => $delivery->getDescription(),
                                'originalDocument' => $delivery->getOriginalDocument(),
                                'status' => $delivery->getStatus(),

                            ];
                        }
                    }
                }
                else {
                    $deliveries = $deliveryRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

                    foreach ($deliveries as $delivery){
                        $deliveryList[] = [
                            'id' => $delivery->getId(),
                            '@id' => '/api/get/delivery/'.$delivery->getId(),
                            'type' => 'Delivery',
                            'reference' =>$delivery->getReference(),

                            'contact' => [
                                'id' => $delivery->getContact() ? $delivery->getContact()->getId() : '',
                                'type' => 'Contact',
                                'name' => $delivery->getContact() ? $delivery->getContact()->getName() : '',
                            ],
                            'operationType' => [
                                'id' => $delivery->getOperationType() ? $delivery->getOperationType()->getId() : '',
                                'type' => 'OperationType',
                                'name' => $delivery->getOperationType() ? $delivery->getOperationType()->getName() : '',
                            ],
                            'branch' => [
                                '@id' => "/api/get/branch/" . $delivery->getBranch()->getId(),
                                '@type' => "Branch",
                                'id' => $delivery->getBranch() ? $delivery->getBranch()->getId() : '',
                                'name' => $delivery->getBranch() ? $delivery->getBranch()->getName() : '',
                            ],

                            'deliveryAt' => $delivery->getDeliveryAt() ? $delivery->getDeliveryAt()->format('Y-m-d') : '',
                            'validateAt' => $delivery->getValidateAt() ? $delivery->getValidateAt()->format('Y-m-d') : '',
                            'description' => $delivery->getDescription(),
                            'originalDocument' => $delivery->getOriginalDocument(),
                            'status' => $delivery->getStatus(),

                        ];
                    }

                }
            }
        }

        return $this->json(['hydra:member' => $deliveryList]);
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
