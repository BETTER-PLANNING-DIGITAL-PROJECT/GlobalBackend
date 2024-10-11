<?php

namespace App\Controller\Security;

use App\Entity\Security\Interface\Menu;
use App\Entity\Security\Interface\Module;
use App\Entity\Security\Interface\Permission;
use App\Entity\Security\Role;
use App\Entity\Security\User;
use App\Repository\Security\Interface\MenuRepository;
use App\Repository\Security\Interface\ModuleRepository;
use App\Repository\Security\Interface\PermissionRepository;
use App\Repository\Security\ProfileRepository;
use App\Repository\Security\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class EditRoleController extends AbstractController
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
                             ProfileRepository $profileRepository,
                             RoleRepository $roleRepository,
                             ModuleRepository $moduleRepository,
                             MenuRepository $menuRepository,
                             PermissionRepository $permissionRepository): JsonResponse
    {
        $data = $this->jsondecode();

        if(!isset($data->name))
            return throw new BadRequestHttpException('name is compulsory');
        if(!isset($data->permissionids))
            return throw new BadRequestHttpException('permission ids are compulsory');

        $name = $data->name;
        $permissionIds = $data->permissionids;

        $profileId = $request->get('id');
        $profile = $profileRepository->find($profileId);

        if (!$name) {
            throw new BadRequestHttpException('"name" is required');
        }
        if (!$permissionIds) {
            throw new BadRequestHttpException('"permission" is required');
        }

        if ($name !== $profile->getName()){
            $profile->setName($name);
        }

        // Get the current roles and delete it
        $roles = $roleRepository->findBy(['profile' => $profile]);
        foreach ($roles as $role){
            $this->entityManager->remove($role);
            $this->entityManager->flush();
        }

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
                            $explodeMenuType = explode("|", $menuId->menu);
                            $explodeMenuId = explode("|", $menuId->menu);
                            $typeVal = $explodeMenuType[1];
                            $menuIdVal = $explodeMenuId[0];
                            //dd($menuIdVal);

                            // On verifie chaque *Menu*
                            if ($menuId){
                                if (isset($menuId->menu))
                                {
                                    if ($typeVal == 'menu'){

                                        if (!isset($menuId->checked) || $menuId->checked){
                                            $role = new Role();
                                            $role->setProfile($profile);

                                            $role->setName($name);
                                            $role->setModule($this->entityManager->getRepository(Module::class)->find($moduleId->module));
                                            $role->setMenu($this->entityManager->getRepository(Menu::class)->find($menuIdVal));
                                            // Les menu n'ont pas de permission donc on s'arrete la !

                                            $role->setStatus(true);
                                            $this->entityManager->persist($role);
                                            $this->entityManager->flush();

                                            if (isset($menuId->children)){
                                                foreach ($menuId->children as $submenuId){
                                                    if ($submenuId){
                                                        if (isset($submenuId->submenu)){
                                                            $explodeSubMenuType = explode("|", $submenuId->submenu);
                                                            $explodeSubMenuId = explode("|", $submenuId->submenu);
                                                            $submenuIdVal = $explodeSubMenuId[0];
                                                            $typeVal = $explodeSubMenuType[1];

                                                            if ($typeVal == 'submenu'){
                                                                // Sous menu ayant des permissions
                                                                //dd($submenuId);

                                                                if ($submenuId->children){
                                                                    foreach ($submenuId->children as $permission){

                                                                        // On verifie chaque *Permission*
                                                                        // On verifie aussi si la *Permission* a ete selectionnee
                                                                        if ($permission && $permission->checked){

                                                                            $perm = $this->entityManager->getRepository(Permission::class)->find($permission->permission);
                                                                            if ($perm){
                                                                                $role = new Role();
                                                                                $role->setProfile($profile);

                                                                                $role->setName($name);
                                                                                $role->setModule($this->entityManager->getRepository(Module::class)->find($moduleId->module));
                                                                                $role->setMenu($this->entityManager->getRepository(Menu::class)->find($submenuIdVal));

                                                                                $role->setStatus(true);
                                                                                $role->setPrivilege($perm);
                                                                                $this->entityManager->persist($role);
                                                                                $this->entityManager->flush();
                                                                            }

                                                                        }

                                                                    }
                                                                }


                                                            }


                                                        }


                                                        // Gymnastique a ce niveau car le front nous renvoi permission lorsque le sous menu n'a pas de permission
                                                        // Donc il considere des a present notre sous menu comme une permission
                                                        if (isset($submenuId->permission)){

                                                            if ($submenuId->checked){
                                                                $explodeSubmenuAsPermissionType = explode("|", $submenuId->permission);
                                                                $submenuAsPermissionTypeVal = $explodeSubmenuAsPermissionType[1];
                                                                $submenuAsPermissionTypeId = $explodeSubmenuAsPermissionType[0];

                                                                if ($submenuAsPermissionTypeVal == 'submenu'){

                                                                    // Sous menu n'ayant pas de permissions
                                                                    $role = new Role();
                                                                    $role->setProfile($profile);

                                                                    $role->setName($name);
                                                                    $role->setModule($this->entityManager->getRepository(Module::class)->find($moduleId->module));
                                                                    $role->setMenu($this->entityManager->getRepository(Menu::class)->find($submenuAsPermissionTypeId));
                                                                    // Pas de permissions so nothing to do about the permissions

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

                                    }

                                    if ($typeVal == 'single'){

                                        if (!isset($menuId->checked))
                                        {
                                            // Si le single est inderminate depuis le treeview
                                            // et ca ne send rien, donc je verifie s'il ya des permissions a l'interieur et je save

                                            if (isset($menuId->children)){

                                                // Debut : Lorsque le single a des permission y'a des permisions
                                                foreach ($menuId->children as $permission) {
                                                    // On verifie chaque *Permission*
                                                    // On verifie aussi si la *Permission* a ete selectionnee
                                                    if ($permission && $permission->checked) {
                                                        $role = new Role();
                                                        $role->setProfile($profile);

                                                        $role->setName($name);
                                                        $role->setModule($this->entityManager->getRepository(Module::class)->find($moduleId->module));
                                                        $role->setMenu($this->entityManager->getRepository(Menu::class)->find($menuIdVal));
                                                        $role->setStatus(true);
                                                        $role->setPrivilege($this->entityManager->getRepository(Permission::class)->find($permission->permission));

                                                        $this->entityManager->persist($role);
                                                        $this->entityManager->flush();
                                                    }
                                                }
                                                // Fin : Lorsque le single a des permission y'a des permisions

                                            }

                                        }
                                        else{

                                            if ($menuId->checked){

                                                if (isset($menuId->children)){
                                                    // Debut : Lorsque le single a des permission y'a des permisions
                                                    foreach ($menuId->children as $permission) {
                                                        // On verifie chaque *Permission*
                                                        // On verifie aussi si la *Permission* a ete selectionnee
                                                        if ($permission && $permission->checked) {
                                                            $role = new Role();
                                                            $role->setProfile($profile);

                                                            $role->setName($name);
                                                            $role->setModule($this->entityManager->getRepository(Module::class)->find($moduleId->module));
                                                            $role->setMenu($this->entityManager->getRepository(Menu::class)->find($menuIdVal));
                                                            $role->setStatus(true);
                                                            $role->setPrivilege($this->entityManager->getRepository(Permission::class)->find($permission->permission));

                                                            $this->entityManager->persist($role);
                                                            $this->entityManager->flush();
                                                        }
                                                    }
                                                    // Fin : Lorsque le single a des permission y'a des permisions

                                                }
                                                else {

                                                    $role = new Role();
                                                    $role->setProfile($profile);

                                                    $role->setName($name);
                                                    $role->setModule($this->entityManager->getRepository(Module::class)->find($moduleId->module));
                                                    $role->setMenu($this->entityManager->getRepository(Menu::class)->find($menuIdVal));
                                                    //

                                                    $role->setStatus(true);
                                                    $this->entityManager->persist($role);
                                                    $this->entityManager->flush();
                                                }

                                            }

                                        }




                                    }

                                    if ($typeVal == 'label'){
                                        // nothing
                                        if ($menuId->checked){

                                        // Debut: Le single s'enregistre meme s'il n'a pas de permissions
                                        $role = new Role();
                                        $role->setProfile($profile);

                                        $role->setName($name);
                                        $role->setModule($this->entityManager->getRepository(Module::class)->find($moduleId->module));
                                        $role->setMenu($this->entityManager->getRepository(Menu::class)->find($menuIdVal));
                                        // Pas de permission

                                        $role->setStatus(true);
                                        $this->entityManager->persist($role);
                                        $this->entityManager->flush();
                                        // Fin:  Le single s'enregistre meme s'il n'a pas de permissions
                                        }
                                    }

                                }

//                                else{
//                                    // nothing
//                                    if ($menuId->checked){
//
//                                        // Debut: Le single s'enregistre meme s'il n'a pas de permissions
//                                        $role = new Role();
//                                        $role->setProfile($profile);
//
//                                        $role->setName($name);
//                                        $role->setModule($this->entityManager->getRepository(Module::class)->find($moduleId->module));
//                                        $role->setMenu($this->entityManager->getRepository(Menu::class)->find($menuId->menu));
//                                        // Pas de permission
//
//                                        $role->setStatus(true);
//                                        $this->entityManager->persist($role);
//                                        $this->entityManager->flush();
//                                        // Fin:  Le single s'enregistre meme s'il n'a pas de permissions
//                                    }
//                                }
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

        return $this->json(['hydra:member' => $this->roleResultName($profile)]);

    }

    private function roleResultName($profile): array
    {
        $roles = $this->entityManager->getRepository(Role::class)->findBy(['profile' => $profile]);

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
