<?php

namespace App\State\Processor\Security;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Security\Interface\Menu;
use App\Entity\Security\User;
use App\Repository\Security\Interface\MenuRepository;
use App\Repository\Security\Interface\ModuleRepository;
use App\Repository\Security\Interface\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PostSubMenuProcessor implements ProcessorInterface
{
    public function jsondecode()
    {
        try {
            return file_get_contents('php://input') ?
                json_decode(file_get_contents('php://input'), false) :
                [];
        }catch (\Exception $ex)
        {
            return [];
        }

    }
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly ModuleRepository $moduleRepository,
                                private readonly MenuRepository $menuRepository,
                                private readonly PermissionRepository $permissionRepository,
                                private readonly TokenStorageInterface $tokenStorage,
    EntityManagerInterface $manager) {
    }


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        $donnee = $this->jsondecode();

        if ($data instanceof Menu){
            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $donnee->module);
            $filterId = intval($filter);
            $module = $this->moduleRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $position = $donnee->position;
            if ($position){
                $menu = $this->menuRepository->findOneBy(['module' => $module,'position' => $position]);
                if ($menu !== null){
                    return new JsonResponse(
                        [
                            'hydra:title' => 'This position already exist for `'.$menu->getName().'` on this module  ',
                        ],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }


            if (isset($donnee->permission) && $donnee->permission){
                foreach ($donnee->permission as $permissionId){

                    // START: No need filter the uri, am already pass array of id to our repository
                    $permission = $this->permissionRepository->find($permissionId);
                    // END: No need filter the uri, am already pass array of id to our repository

                    $data->addPermission($permission);
                }
            }
        }
    
        return $this->processor->process($data,  $operation, $uriVariables, $context);
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
