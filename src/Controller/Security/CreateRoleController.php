<?php

namespace App\Controller\Security;

use App\Entity\Security\Interface\Menu;
use App\Entity\Security\Interface\Module;
use App\Entity\Security\Interface\Permission;
use App\Entity\Security\Profile;
use App\Entity\Security\Role;
use App\Entity\Security\User;
use App\Repository\Security\Interface\MenuRepository;
use App\Repository\Security\Interface\ModuleRepository;
use App\Repository\Security\Interface\PermissionRepository;
use App\Repository\Security\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
final class CreateRoleController extends AbstractController
{
    public function jsondecode()
    {
        try {
            return file_get_contents('php://input') ?
                json_decode(file_get_contents('php://input'), false) :
                [];
        }catch (\Exception $ex)
        {
            return [];
        }

    }

    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(Request $request,
                             ModuleRepository $moduleRepository,
                             MenuRepository $menuRepository,
                             PermissionRepository $permissionRepository,
                             ProfileRepository $profileRepository): JsonResponse
    {
        $data = $this->jsondecode();

        if(!isset($data->name))
            return throw new BadRequestHttpException('name is compulsory');
        if(!isset($data->permissionids))
            return throw new BadRequestHttpException('permission ids are compulsory');

        $name = $data->name;
        $permissionIds = $data->permissionids;

        if (!$name) {
            throw new BadRequestHttpException('"name" is required');
        }
        if (!$permissionIds) {
            throw new BadRequestHttpException('"permission" is required');
        }

        $profileName = $profileRepository->findOneBy(['name' => $name]);
        if ($profileName){
            return new JsonResponse(
                [
                    'hydra:title' => 'This profile already exist',
                ],Response::HTTP_INTERNAL_SERVER_ERROR, ['Content-Type', 'application/json']);
        }

        $profile = new Profile();
        $profile->setName($name);
        $profile->setInstitution($this->getUser()->getInstitution());
        $profile->setUser($this->getUser());
        $this->entityManager->persist($profile);

        foreach ($permissionIds as $moduleId){

            // On prend le module
            if ($moduleId){

                // SI on a checked le module
                if (!isset($moduleId->checked) || $moduleId->checked){
                    // On verifie si le module a des enfants
                    // Ses enfants ce sont les *Menus*
                    if (isset($moduleId->children)){

                        // On boucle sur la liste des *Menus*
                        foreach ($moduleId->children as $menuId)
                        {
                            // On verifie chaque *Menu*
                            if ($menuId){
                                if (isset($menuId->type))
                                {
                                    if ($menuId->type == 'menu')
                                    {
                                        if (!isset($menuId->checked) || $menuId->checked)
                                        {
                                            $role = new Role();

                                            $role->setProfile($profile);
                                            $role->setName($name);
                                            $role->setModule($this->entityManager->getRepository(Module::class)->find($moduleId->module));
                                            $role->setMenu($this->entityManager->getRepository(Menu::class)->find($menuId->menu));
                                            // Les menu n'ont pas de permission donc on s'arrete la !

                                            $role->setStatus(true);
                                            $this->entityManager->persist($role);
                                            $this->entityManager->flush();

                                            foreach ($menuId->children as $submenuId)
                                            {
                                                if ($submenuId)
                                                {
                                                    if ($submenuId->children)
                                                    {
                                                        foreach ($submenuId->children as $permission)
                                                        {
                                                            // On verifie chaque *Permission*
                                                            // On verifie aussi si la *Permission* a ete selectionnee
                                                            if ($permission && $permission->checked)
                                                            {
                                                                $role = new Role();

                                                                $role->setProfile($profile);
                                                                $role->setName($name);
                                                                $role->setModule($this->entityManager->getRepository(Module::class)->find($moduleId->module));
                                                                $role->setMenu($this->entityManager->getRepository(Menu::class)->find($submenuId->submenu));
                                                                $role->setPrivilege($this->entityManager->getRepository(Permission::class)->find($permission->permission));

                                                                $role->setStatus(true);
                                                                $this->entityManager->persist($role);
                                                                $this->entityManager->flush();
                                                            }

                                                        }
                                                    }

                                                }
                                            }
                                        }

                                    }

                                    if ($menuId->type == 'single')
                                    {
                                        // Debut : Lorsque le single a des permission y'a des permisions
                                        foreach ($menuId->children as $permission) {
                                            // On verifie chaque *Permission*
                                            // On verifie aussi si la *Permission* a ete selectionnee
                                            if ($permission && $permission->checked) {
                                                $role = new Role();
                                                $role->setProfile($profile);

                                                $role->setName($name);
                                                $role->setModule($this->entityManager->getRepository(Module::class)->find($moduleId->module));
                                                $role->setMenu($this->entityManager->getRepository(Menu::class)->find($menuId->menu));
                                                $role->setPrivilege($this->entityManager->getRepository(Permission::class)->find($permission->permission));

                                                $role->setStatus(true);
                                                $this->entityManager->persist($role);
                                                $this->entityManager->flush();
                                            }
                                        }
                                        // Fin : Lorsque le single a des permission y'a des permisions

                                    }

                                }

                                else{
                                    // nothing
                                    if ($menuId->checked){

                                        // Debut: Le single s'enregistre meme s'il n'a pas de permissions
                                        $role = new Role();
                                        $role->setProfile($profile);

                                        $role->setName($name);
                                        $role->setModule($this->entityManager->getRepository(Module::class)->find($moduleId->module));
                                        $role->setMenu($this->entityManager->getRepository(Menu::class)->find($menuId->menu));
                                        // Pas de permission

                                        $role->setStatus(true);
                                        $this->entityManager->persist($role);
                                        $this->entityManager->flush();
                                        // Fin:  Le single s'enregistre meme s'il n'a pas de permissions
                                    }
                                }
                            }

                        }
                    }
                    else{
                        // nothing
                        break;
                    }
                }
                else{
                    // Le module n'a pas ete selectionne donc on ne fait rien !
                }


            }

        }

        $this->entityManager->flush();

        return $this->json(['hydra:member' => $this->roleResultName($profile->getName())]);

    }

    private function roleResultName($name): array
    {
        $roles = $this->entityManager->getRepository(Role::class)->findBy(['name' => $name]);

        $data = [];
        foreach ($roles as $role)
        {
            $data[] = [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'module' => $role->getModule()->getName(),
                'menu' => $role->getMenu()->getName(),
                'permission' => $role->getPrivilege() ? $role->getPrivilege()->getName() : '',
            ];
        }

        return array(
            'roles' => $data,
        );
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
