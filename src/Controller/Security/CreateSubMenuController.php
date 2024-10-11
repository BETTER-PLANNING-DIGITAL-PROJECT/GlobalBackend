<?php
namespace App\Controller\Security;

use App\Entity\Security\Interface\Menu;
use App\Repository\Security\Interface\MenuRepository;
use App\Repository\Security\Interface\ModuleRepository;
use App\Repository\Security\Interface\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateSubMenuController extends AbstractController
{
    public function __construct(private readonly ModuleRepository $moduleRepository,
                                private readonly PermissionRepository $permissionRepository,
                                private readonly MenuRepository $menuRepository,
                                private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(Request $request): JsonResponse|Menu
    {
        $requestData = json_decode($request->getContent(), true);

        /*if (!$requestData instanceof Menu){
            return new JsonResponse(['hydra:title' => 'Invalid entity process'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }*/

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $requestData['module']);
        $filterId = intval($filter);
        $module = $this->moduleRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object


        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $requestData['parent']);
        $filterId = intval($filter);
        $menuParent = $this->menuRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object


//        $singlePosition = $requestData['positionSingle'];
//        $singleMenu = $this->menuRepository->findSingleMenuBySinglePositionAndModule($module, $singlePosition);
//        if ($singleMenu !== null){
//            return new JsonResponse(
//                [
//                    'hydra:title' => 'This position already exist for `'.$singleMenu->getName().'` on this module  ',
//                ],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
//        }

        $subMenuPosition = $requestData['position'];
        if ($subMenuPosition){
            $subMenu = $this->menuRepository->findByModulePositionMenu($module, $subMenuPosition, $menuParent);
            if ($subMenu !== null){
                return new JsonResponse(
                    [
                        'hydra:title' => 'This position already exist for `'.$subMenu->getName().'` on this Module',
                    ],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
        }

        // On prepare la persistance du Menu

        $menu = new Menu();
        $menu->setName($requestData['name']);
        $menu->setPath($requestData['path']);
        $menu->setModule($module);
        $menu->setPosition($subMenuPosition);
        $menu->setType($requestData['type']);
        $menu->setParent($menuParent);
        $menu->setTitle($requestData['title']);


        if ($requestData['permissions']){
            foreach ($requestData['permissions'] as $permissionUri){

                // START: Filter the uri to just take the id and pass it to our repository
                $filter = preg_replace("/[^0-9]/", '', $permissionUri['@id']);
                $filterId = intval($filter);
                $permission = $this->permissionRepository->find($filterId);
                // END: Filter the uri to just take the id and pass it to our repository

                $menu->addPermission($permission);
                $this->manager->persist($menu);
            }
        }

        $this->manager->persist($menu);

        $this->manager->flush();

        return $menu;
    }
}
