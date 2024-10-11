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
class EditLabelController extends AbstractController
{
    public function __construct(private readonly ModuleRepository $moduleRepository,
                                private readonly PermissionRepository $permissionRepository,
                                private readonly MenuRepository $menuRepository,
                                private readonly EntityManagerInterface $manager)
    {
    }


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
                //$menu = $this->menuRepository->findByModulePosition($module, $menuPosition);
                $menu = $this->menuRepository->findOneBy(['module' => $module, 'position' => $menuPosition]);
                if ($menu !== null){
                    return new JsonResponse(
                        [
                            'hydra:title' => 'This position already exist for `'.$menu->getName().'` on this module  ',
                        ],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
                }
            }
        }


        $data->setName($requestData['name']);
        $data->setModule($module);
        $data->setPosition($requestData['position']);

        $this->manager->flush();

        return $data;
    }

}
