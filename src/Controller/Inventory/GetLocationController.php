<?php

namespace App\Controller\Inventory;

use App\Entity\Security\User;
use App\Repository\Inventory\LocationRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetLocationController extends AbstractController
{
    private LocationRepository $locationRepository;
    private SystemSettingsRepository $systemSettingsRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                LocationRepository                     $locationRepository,
                                SystemSettingsRepository                     $systemSettingsRepository,
    )
    {
        $this->locationRepository = $locationRepository;
        $this->systemSettingsRepository = $systemSettingsRepository;
    }

    public function __invoke(Request $request):JsonResponse
    {
        $locationData = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $locations = $this->locationRepository->findBy([], ['id' => 'DESC']);

            foreach ($locations as $location)
            {
                $locationData[] = [
                    'id'=> $location ->getId(),
                    'name'=> $location->getName(),
                    'warehouse' => [
                        '@id' => "/api/get/warehouse/" . $location->getWarehouse()->getId(),
                        '@type' => "Warehouse",
                        'id' => $location->getWarehouse() ? $location->getWarehouse()->getId() : '',
                        'name' => $location->getWarehouse() ? $location->getWarehouse()->getName() : '',
                    ],
                    'branch' => [
                        '@id' => "/api/get/branch/" . $location->getBranch()->getId(),
                        '@type' => "Branch",
                        'id' => $location->getBranch() ? $location->getBranch()->getId() : '',
                        'name' => $location->getBranch() ? $location->getBranch()->getName() : '',
                    ],
                ];
            }
        }
        else
        {
            $systemSettings = $this->systemSettingsRepository->findOneBy([]);
            if($systemSettings)
            {
                if($systemSettings->isIsBranches())
               {
                   $userBranches = $this->getUser()->getUserBranches();
                   foreach ($userBranches as $userBranch) {

                       // get cash desk
                       $locations = $this->locationRepository->findBy(['branch' => $userBranch], ['id' => 'DESC']);
                          foreach ($locations as $location){
                              $locationData[] = [
                                  'id'=> $location ->getId(),
                                  'name'=> $location->getName(),
                                  'warehouse' => [
                                      '@id' => "/api/get/warehouse/" . $location->getWarehouse()->getId(),
                                      '@type' => "Warehouse",
                                      'id' => $location->getWarehouse() ? $location->getWarehouse()->getId() : '',
                                      'name' => $location->getWarehouse() ? $location->getWarehouse()->getName() : '',
                                  ],
                                  'branch' => [
                                      '@id' => "/api/get/branch/" . $location->getBranch()->getId(),
                                      '@type' => "Branch",
                                      'id' => $location->getBranch() ? $location->getBranch()->getId() : '',
                                      'name' => $location->getBranch() ? $location->getBranch()->getName() : '',
                                  ],
                              ];
                          }
                       }
               }
               else {
                   $locations = $this->locationRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

                   foreach ($locations as $location) {
                       if ($location) {
                           $locationData[] = [
                               'id'=> $location ->getId(),
                               'name'=> $location->getName(),
                               'warehouse' => [
                                   '@id' => "/api/get/warehouse/" . $location->getWarehouse()->getId(),
                                   '@type' => "Warehouse",
                                   'id' => $location->getWarehouse() ? $location->getWarehouse()->getId() : '',
                                   'name' => $location->getWarehouse() ? $location->getWarehouse()->getName() : '',
                               ],
                               'branch' => [
                                   '@id' => "/api/get/branch/" . $location->getBranch()->getId(),
                                   '@type' => "Branch",
                                   'id' => $location->getBranch() ? $location->getBranch()->getId() : '',
                                   'name' => $location->getBranch() ? $location->getBranch()->getName() : '',
                               ],
                           ];
                       }
                   }

               }
            }
        }


        return $this->json(['hydra:member' => $locationData]);
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
