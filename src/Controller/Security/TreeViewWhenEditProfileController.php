<?php

namespace App\Controller\Security;

use App\Repository\Security\Interface\MenuRepository;
use App\Repository\Security\Interface\ModuleRepository;
use App\Repository\Security\Interface\PermissionRepository;
use App\Repository\Security\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class TreeViewWhenEditProfileController extends AbstractController
{
    public function jsondecode()
    {
        try {
            return file_get_contents('php://input') ?
                json_decode(file_get_contents('php://input'), false) :
                [];
        } catch (\Exception $ex) {
            return [];
        }

    }

    public function __construct(private readonly ModuleRepository     $moduleRepository,
                                private readonly MenuRepository       $menuRepository,
                                private readonly PermissionRepository $permissionRepository)
    {
    }

    public function __invoke(Request $request, ProfileRepository $profileRepository): array
    {
        $profileId = $request->get('id');
        $profileObject = $profileRepository->find($profileId);

        $myModules = [
            'id' => $profileObject->getId(),
            'name' => $profileObject->getName(),
            'module' => $this->serializeModules($profileObject),
            'category' => 'profile'
        ];

        //dd($myModules);

        return ['role' => $myModules];

    }

    public function serializeModules($profileObject): array
    {
        $modules = $this->moduleRepository->findOneByProfile($profileObject);

        $myModules = [];
        foreach ($modules as $module) {
            $myModules[] = [
                'id' => $module['id'],
                'name' => $module['name'],
                'category' => 'module'
            ];

        }

        $allModules = $this->moduleRepository->findByMenu();

        $allMyModules = [];
        foreach ($allModules as $module) {
            $exist = in_array($module->getId(), array_column($myModules, 'id'));
            if ($exist !== false) {
                $allMyModules [] = [
                    'id' => $module->getId(),
                    'name' => $module->getName(),
                    'status' => true,
                    'category' => 'module',
                    'role' => $profileObject->getName(),
                    'menus' => $this->serializeSearchMenu($profileObject, $module),
                ];
            } else {
                $allMyModules [] = [
                    'id' => $module->getId(),
                    'name' => $module->getName(),
                    'status' => false,
                    'category' => 'module',
                    'menus' => $this->serializeSearchMenu($profileObject, $module),
                ];
            }

        }

        return $allMyModules;

    }

    private function serializeSearchMenu($profile, $module): array
    {
        $menus = $this->menuRepository->findByModuleAndProfile($module, $profile);

        $myMenus = [];
        foreach ($menus as $menu) {
            $myMenus[] = [
                'id' => $menu->getId(). '|'. $menu->getTitle(),
                'data' => $menu->getId(),
                'name' => $menu->getName(),
                'category' => $menu->getTitle()
            ];
        }

        // J'ai customise l'id donc ce qui ne va plus etre efficace pour faire des verifs
        // Voila pourquoi j'ai ajoute data, qui sera l'id.

        $allMenus = $this->menuRepository->findByModule($module);

        $allMyMenus = [];
        foreach ($allMenus as $menu) {

            //dd(in_array(6, array_column($myMenus, 'data')));
            $exist = in_array($menu->getId(), array_column($myMenus, 'data'));
            if ($exist !== false) {
                if ($menu->getType() === 'link') {
                    if (!$menu->getParent()) {
                        $allMyMenus [] = [
                            'id' => $menu->getId(). '|'. $menu->getTitle(),
                            'name' => $menu->getName(),
                            'status' => true,
                            'category' => $menu->getTitle(),
                            'permissions' => $this->serializeSearchPermission($profile, $menu)
                        ];
                    }
                } else {

                    if (!$menu->getParent()) {
                        $allMyMenus [] = [
                            'id' => $menu->getId() . '|'. $menu->getTitle(),
                            'name' => $menu->getName(),
                            'status' => true,
                            'category' => $menu->getTitle(),
                            'submenu' => $this->serializeSubmenu($profile, $module, $menu)
                        ];
                    } else {
                        $allMyMenus [] = [
                            'id' => $menu->getId(). '|'. $menu->getTitle(),
                            'name' => $menu->getName(),
                            'status' => true,
                            'category' => $menu->getTitle()
                        ];
                    }

                }

            } else {

                if ($menu->getType() === 'link') {
                    if (!$menu->getParent()) {
                        //dd($menu);
                        $allMyMenus [] = [
                            'id' => $menu->getId(). '|'. $menu->getTitle(),
                            'name' => $menu->getName(),
                            'status' => false,
                            'category' => $menu->getTitle(),
                            'permissions' => $this->serializeSearchPermission($profile, $menu)
                        ];
                    }
                } else {

                    if (!$menu->getParent()) {
                        $allMyMenus [] = [
                            'id' => $menu->getId(). '|'. $menu->getTitle(),
                            'name' => $menu->getName(),
                            'status' => false,
                            'category' => $menu->getTitle(),
                            'submenu' => $this->serializeSubmenu($profile, $module, $menu)
                        ];
                    } else {
                        $allMyMenus [] = [
                            'id' => $menu->getId(). '|'. $menu->getTitle(),
                            'name' => $menu->getName(),
                            'status' => false,
                            'category' => $menu->getTitle()
                        ];
                    }

                }

            }


        }


        return $allMyMenus;
    }


    private function serializeSubmenu($profile, $module, $parent): array
    {
        $menus = $this->menuRepository->findByModuleProfileParent($profile, $module, $parent);

        $myMenus = [];
        foreach ($menus as $menu) {
            $myMenus[] = [
                'id' => $menu->getId(). '|'. $menu->getTitle(),
                'data' => $menu->getId(),
                'name' => $menu->getName(),
                'category' => $menu->getTitle()
            ];
        }



        // J'ai customise l'id donc ce qui ne va plus etre efficace pour faire des verifs
        // Voila pourquoi j'ai ajoute data, qui sera l'id.

        $allSubMenus = $this->menuRepository->findByModuleParent($module, $parent);


        $allMySubMenus = [];
        foreach ($allSubMenus as $menu) {

            // dd(in_array(215, array_column($myMenus, 'data')));
            $exist = in_array($menu->getId(), array_column($myMenus, 'data'));
            if ($exist !== false) {
                if ($menu->getType() == 'link') {
                    $allMySubMenus [] = [
                        'id' => $menu->getId(). '|'. $menu->getTitle(),
                        'name' => $menu->getName(),
                        'status' => true,
                        'category' => $menu->getTitle(),
                        'permissions' => $this->serializeSearchPermission($profile, $menu)
                    ];
                } else {
                    $allMySubMenus [] = [
                        'id' => $menu->getId(). '|'. $menu->getTitle(),
                        'name' => $menu->getName(),
                        'status' => true,
                        'category' => $menu->getTitle()
                    ];

                }

            } else {
                if ($menu->getType() == 'link') {
                    $allMySubMenus [] = [
                        'id' => $menu->getId(). '|'. $menu->getTitle(),
                        'name' => $menu->getName(),
                        'status' => false,
                        'category' => $menu->getTitle(),
                        'permissions' => $this->serializeSearchPermission($profile, $menu)
                    ];
                } else {
                    $allMySubMenus [] = [
                        'id' => $menu->getId(). '|'. $menu->getTitle(),
                        'name' => $menu->getName(),
                        'status' => false,
                        'category' => $menu->getTitle(),
                    ];

                }

            }

        }


        return $allMySubMenus;
    }

    private function serializeSearchPermission($profile, $menu): array
    {
        $menu = $this->menuRepository->findOneBy(['id' => $menu]);

        $allPermissions = $menu->getPermissions();

        $allMyPermissions = [];

        foreach ($allPermissions as $perms) {
            $foundPermission = $this->permissionRepository->findSearchExistPermission($profile, $menu, $perms);

            if ($foundPermission) {
                $allMyPermissions[] = [
                    'id' => $perms->getId(),
                    'name' => $perms->getName(),
                    'status' => true,
                    'category' => 'permission'
                ];
            } else {
                $allMyPermissions[] = [
                    'id' => $perms->getId(),
                    'name' => $perms->getName(),
                    'status' => false,
                    'category' => 'permission'
                ];
            }

        }

        return $allMyPermissions;
    }
}