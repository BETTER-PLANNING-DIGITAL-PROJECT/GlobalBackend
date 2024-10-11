<?php
namespace App\Controller\Security;

use App\Entity\Security\Interface\Menu;
use App\Repository\Security\Interface\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetSingleController extends AbstractController
{
    public function __construct(private readonly MenuRepository $menuRepository)
    {
    }

    public function __invoke(mixed $data, Request $request): JsonResponse|Menu
    {
        $menus = $this->menuRepository->findBy(['title' => 'single'], ['id' => 'DESC']);
        $myMenus = [];
        foreach ($menus as $menu)
        {
            $myMenus[] = [
                'id' => $menu->getId(),
                '@id' => '/api/get/menu/'.$menu->getId(),
                '@type' => 'Menu',
                'name' => $menu->getName(),
                'path' => $menu->getPath(),
                'type' => $menu->getType(),
                'title' => $menu->getTitle(),
                'icon' => $menu->getIcon(),
                'position' => $menu->getPosition(),
                'badgeType' => $menu->getBadgeType(),
                'badgeValue' => $menu->getBadgeValue(),
                'module' => [
                    'id' => $menu->getModule()->getId(),
                    '@id' => '/api/get/module/'. $menu->getModule()->getId(),
                    'name' => $menu->getModule() ? $menu->getModule()->getName() : '',
                    'color' => $menu->getModule() ? $menu->getModule()->getColor() : '',
                    'icon' => $menu->getModule() ? $menu->getModule()->getIcon() : '',
                    'position' => $menu->getModule() ? $menu->getModule()->getPosition() : '',
                    'path' => $menu->getModule() ? $menu->getModule()->getPath() : '',
                    'layout' => $menu->getModule() ? $menu->getModule()->getLayout() : '',
                ],
                'permissions' => $this->serializePermissions($menu)
            ];
        }
        return new JsonResponse(['hydra:member' => $myMenus], Response::HTTP_OK);

    }

    public function serializePermissions(Menu $menu): array
    {
        $permissions = $menu->getPermissions();
        $myPermissions = [];
        foreach ($permissions as $permission){
            $myPermissions [] = [
                '@type' => 'Permission',
                '@id' => '/api/get/permission/'.$permission->getId(),
                'id' => $permission->getId(),
                'name' => $permission->getName()
            ];
        }
        return $myPermissions;
    }

}
