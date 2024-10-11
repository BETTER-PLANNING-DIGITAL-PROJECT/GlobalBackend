<?php
namespace App\Controller\Security;

use App\Entity\Security\Interface\Menu;
use App\Repository\Security\Interface\MenuRepository;
use App\Repository\Security\Interface\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateMenuController extends AbstractController
{
    public function __construct(private readonly ModuleRepository $moduleRepository,
                                private readonly MenuRepository $menuRepository,
                                private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(Request $request): JsonResponse
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

        $menuPosition = $requestData['position'];
        if ($menuPosition){
            $singleMenu = $this->menuRepository->findByModulePosition($module, $menuPosition);
            if ($singleMenu !== null){
                return new JsonResponse(
                    [
                        'hydra:title' => 'This position already exist for `'.$singleMenu->getName().'` on this module  ',
                    ],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
            }
        }


        // On prepare la persistance du Menu

        $menu = new Menu();
        $menu->setName($requestData['name']);

        $menu->setIcon($requestData['icon']);
        $menu->setModule($module);
        $menu->setPosition($requestData['position']);
        $menu->setType($requestData['type']);
        $menu->setTitle($requestData['title']);
        if (isset($requestData['badgeType'])){
        $menu->setBadgeType($requestData['badgeType']);
        }
        $menu->setBadgeValue($requestData['badgeValue']);

        $this->manager->persist($menu);

        $this->manager->flush();

        // Fin de la persistance du Menu

        return $this->json(['hydra:member' => $menu]);
    }
}
