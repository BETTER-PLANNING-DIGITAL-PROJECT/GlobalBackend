<?php

namespace App\State\Provider\Security;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Security\Interface\Menu;
use App\Entity\Security\Interface\Module;
use App\Repository\Security\Interface\MenuRepository;
use App\Repository\Security\Interface\ModuleRepository;

final class TreeViewWhenCreateProfileProvider implements ProviderInterface
{
    public function __construct(private readonly ModuleRepository $moduleRepository,
                                private readonly MenuRepository $menuRepository
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $modules = $this->moduleRepository->findModuleWithMenu();

        $myModules = [];
        foreach ($modules as $module){
            $myModules [] = [
                'id' => $module->getId(),
                'name' => $module->getName(),
                'menus' => $this->serializeSearchMenu($module),
            ];
        }

        return array(['module' => $myModules]);
    }

    private function serializeSearchMenu(Module $module): array
    {
        $menus = $this->menuRepository->findByMenuWherePermissions($module);

        $myMenus = [];
        foreach ($menus as $menu)
        {

            if ($menu->getType() === 'link'){
                if (!$menu->getChildren()){
                    $myMenus[] = [
                        'id' => $menu->getId(),
                        'name' => $menu->getName(),
                        'status' => true,
                        'permissions' => $this->serializeSearchPermission($menu)
                    ];
                }
            }
            else{
                if (!$menu->getChildren()){
                    $myMenus [] = [
                        'id' => $menu->getId(),
                        'name' => $menu->getName(),
                        'status' => true,
                        'submenu' => $this->serializeSubmenu($module, $menu)
                    ];
                }
                else{
                    $myMenus [] = [
                        'id' => $menu->getId(),
                        'name' => $menu->getName(),
                        'status' => true,
                    ];
                }
            }

        }

        return $myMenus;
    }


    private function serializeSubmenu($module, $children): array
    {
        $allSubMenus = $this->menuRepository->findByModuleMenuWherePermissions($module, $children);

        $allMySubMenus = [];
        foreach ($allSubMenus as $menu){

            if ($menu->getType() == 'link'){
                $allMySubMenus [] = [
                    'id' => $menu->getId(),
                    'name' => $menu->getName(),
                    'status' => false,
                    'permissions' => $this->serializeSearchPermission($menu)
                ];
            }
            else{
                $allMySubMenus [] = [
                    'id' => $menu->getId(),
                    'name' => $menu->getName(),
                    'status' => false,
                    'permissions' => $this->serializeSearchPermission($menu)
                ];

            }


        }


        return $allMySubMenus;
    }


    private function serializeSearchPermission(Menu $menu): array
    {
        $permissions = $menu->getPermissions();

        $myPermissions = [];
        foreach ($permissions as $permission)
        {
            $myPermissions[] = [
                'id' => $permission->getId(),
                'name' => $permission->getName(),
            ];
        }

        return $myPermissions;
    }

}
