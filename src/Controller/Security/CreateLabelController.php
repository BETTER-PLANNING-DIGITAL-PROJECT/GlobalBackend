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

class CreateLabelController extends AbstractController
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
            // $labelMenu = $this->menuRepository->findByModulePosition($module, $menuPosition);
            $labelMenu = $this->menuRepository->findOneBy(['module' => $module, 'position' => $menuPosition]);

            if ($labelMenu !== null){
                return new JsonResponse(
                    [
                        'hydra:title' => 'This position already exist for `'.$labelMenu->getName().'` on this module  ',
                    ],Response::HTTP_INTERNAL_SERVER_ERROR, ['Content-Type', 'application/json']);
            }
        }


        // On prepare la persistance du Menu

        $menu = new Menu();
        $menu->setModule($module);
        $menu->setName($requestData['name']);
        $menu->setHeadTitle1($requestData['name']);
        $menu->setTitle($requestData['title']);
        $menu->setType($requestData['title']);
        $menu->setPosition($requestData['position']);

        $this->manager->persist($menu);

        $this->manager->flush();

        // Fin de la persistance du Menu

        return $this->json(['hydra:member' => $menu]);
    }
}
