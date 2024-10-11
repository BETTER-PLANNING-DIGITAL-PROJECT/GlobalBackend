<?php
namespace App\Controller\Security;

use App\Entity\Security\Interface\Menu;
use App\Repository\Security\Interface\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetParentMenuController extends AbstractController
{
    public function __construct(private readonly MenuRepository $menuRepository)
    {
    }

    public function __invoke(mixed $data, Request $request): JsonResponse|Menu
    {
        $parentMenus = $this->menuRepository->findParentMenu();
        $myParentMenus = [];
        foreach ($parentMenus as $myParentMenu)
        {
            $myParentMenus[] = [
                '@id' => '/api/get/menu/'.$myParentMenu->getId(),
                '@type' => 'Menu',
                'id' => $myParentMenu->getId(),
                'name' => $myParentMenu->getName(),
                'title' => $myParentMenu->getTitle(),
                'path' => $myParentMenu->getPath(),
                'type' => $myParentMenu->getType(),
//                'module' => [
//                    'id' => $myParentMenu->getModule()->getId(),
//                    '@id' => '/api/get/module/'. $myParentMenu->getModule()->getId(),
//                    'name' => $myParentMenu->getModule() ? $myParentMenu->getModule()->getName() : '',
//                    'color' => $myParentMenu->getModule() ? $myParentMenu->getModule()->getColor() : '',
//                    'icon' => $myParentMenu->getModule() ? $myParentMenu->getModule()->getIcon() : '',
//                    'position' => $myParentMenu->getModule() ? $myParentMenu->getModule()->getPosition() : '',
//                    'path' => $myParentMenu->getModule() ? $myParentMenu->getModule()->getPath() : '',
//                    'layout' => $myParentMenu->getModule() ? $myParentMenu->getModule()->getLayout() : '',
//                ],
                'module' => '/api/get/module/'.$myParentMenu->getModule()->getId(),
                //'module' => $myParentMenu->getModule()->getId(),
                'position' => $myParentMenu->getPosition(),
            ];
        }
        return new JsonResponse(['hydra:member' => $myParentMenus], Response::HTTP_OK);

    }

}
