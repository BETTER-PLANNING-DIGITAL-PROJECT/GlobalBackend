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
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class EditMenuController extends AbstractController
{
    public function __construct(private readonly ModuleRepository $moduleRepository,
                                private readonly PermissionRepository $permissionRepository,
                                private readonly MenuRepository $menuRepository,
                                private readonly EntityManagerInterface $manager)
    {
    }

//    public function __invoke(Request $request, mixed $data): JsonResponse|Menu
//    {
//        if (!$data instanceof Menu){
//            return new JsonResponse(['hydra:title' => 'Invalid entity process'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
//        }
//
//        $requestData = json_decode($request->getContent(), true);
//
//        // START: Filter the uri to just take the id and pass it to our object
//        $filter = preg_replace("/[^0-9]/", '', $requestData['module']);
//        $filterId = intval($filter);
//        $module = $this->moduleRepository->find($filterId);
//        // END: Filter the uri to just take the id and pass it to our object
//
//        // START: Filter the uri to just take the id and pass it to our object
//        $filter = preg_replace("/[^0-9]/", '', $requestData['children']);
//        $filterId = intval($filter);
//        $menuParent = $this->menuRepository->find($filterId);
//        // END: Filter the uri to just take the id and pass it to our object
//
//        // Récupérer l'ID de l'entité actuelle
//        $menuId = $request->get('id');
//
//        $dql = 'SELECT id, name, position_single, position_submenu, title FROM security_menu s WHERE id = '.$menuId;
//        $conn = $this->manager->getConnection();
//        $resultSet = $conn->executeQuery($dql);
//        $rows = $resultSet->fetchAllAssociative();
//
//        $currentMenu = $rows[0];
//
//        $singlePosition = $requestData['positionSingle'];
//
//        if ($currentMenu){
//            if ($currentMenu['position_single'] !== $singlePosition){
//                $singleMenu = $this->menuRepository->findSingleMenuBySinglePositionAndModule($module, $singlePosition);
//                if ($singleMenu !== null){
//                    return new JsonResponse(
//                        [
//                            'hydra:title' => 'This position already exist for `'.$singleMenu->getName().'` on this module  ',
//                        ],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
//                }
//            }
//        }
//
//        if ($currentMenu){
//            $subMenuPosition = $requestData['positionSubmenu'];
//            if ($currentMenu['position_submenu'] !== $subMenuPosition){
//                $submenu = $this->menuRepository->findSubMenuBySubMenuPositionAndModuleAndParentMenu($module, $subMenuPosition, $menuParent);
//                if ($submenu !== null){
//                    return new JsonResponse(
//                        [
//                            'hydra:title' => 'This position already exist for `'.$submenu->getName().'` on this Module',
//                        ],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
//                }
//            }
//        }
//
//        // On retire d'abord toutes les permission de ce menu
//        /*foreach ($data->getPermissions() as $perms){
//            $data->removePermission($perms);
//        }*/
//
//        // On ajoute maintenant ce qu'on vient de selectionner
//        /*foreach ($requestData['permission'] as $permissionUri){
//
//            $permission = $this->permissionRepository->find($permissionUri['id']);
//            if ($permission){
//                $data->addPermission($permission);
//            }
//        }*/
//
//        $this->manager->flush();
//
//        return $data;
//    }


    public function __invoke(Request $request): JsonResponse|Menu
    {
        // Récupérer l'ID de l'entité actuelle
        $menuId = $request->get('id');
        $data = $this->menuRepository->find($menuId);

        if (!$data){
            return new JsonResponse(['hydra:title' => 'Menu not found'], Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
        }

        if (!$data instanceof Menu){
            return new JsonResponse(['hydra:title' => 'Invalid entity process'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        $requestData = json_decode($request->getContent(), true);

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $requestData['module']);
        $filterId = intval($filter);
        $module = $this->moduleRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object


//        $dql = 'SELECT id, name, position, title FROM security_menu s WHERE id = '.$data->getId();
//        $conn = $this->manager->getConnection();
//        $resultSet = $conn->executeQuery($dql);
//        $rows = $resultSet->fetchAllAssociative();
//
//        $currentMenu = $rows[0];
//        $currentMenu['position'];

        $menuPosition = $requestData['position'];

        if ($menuPosition){
            if ($data->getPosition() !== $menuPosition){
                $menu = $this->menuRepository->findByModulePosition($module, $menuPosition);
                if ($menu !== null){
                    return new JsonResponse(
                        [
                            'hydra:title' => 'This position already exist for `'.$menu->getName().'` on this module  ',
                        ],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
        }


        $data->setName($requestData['name']);
        $data->setIcon($requestData['icon']);
        $data->setModule($module);
        $data->setPosition($requestData['position']);
        $data->setType($requestData['type']);
        $data->setBadgeType($requestData['badgeType']);
        $data->setBadgeValue($requestData['badgeValue']);

        $this->manager->flush();

        return $data;
    }

}
