<?php

namespace App\Controller\Security;

use App\Entity\Security\Interface\Menu;
use App\Entity\Security\User;;

use App\Repository\Security\Interface\MenuRepository;
use App\Repository\Security\RoleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class GetUserPermissionsByMenuIdController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage){}

    public function __invoke(Request $request, MenuRepository $menuRepository, RoleRepository $roleRepository): JsonResponse
    {
        $id = $request->get('id');

        if (!$id) {
            return $this->json(['hydra:title' => 'No correct parameter or does not exist'], Response::HTTP_BAD_REQUEST);
        }

        $menu = $menuRepository->find($id);

        if (!$menu){
            return $this->json(['hydra:title' => 'No configuration for this menu'], Response::HTTP_BAD_REQUEST);
        }

        if (!$menu instanceof Menu){
            return $this->json(['hydra:title' => 'Invalid data type'], Response::HTTP_BAD_REQUEST);
        }

        $permissions = [];
        $roles = $roleRepository->findBy(['menu' => $menu, 'profile' => $this->getUser()->getProfile(), 'module' => $menu->getModule()]);
        foreach ($roles as $role){
            $permissions[] = [
                'id' => $role->getPrivilege() ? $role->getPrivilege()->getId() : '',
                'name' => $role->getPrivilege() ? $role->getPrivilege()->getName() : ''
            ];
        }

        return $this->json(['hydra:member' => $permissions], Response::HTTP_OK);

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



