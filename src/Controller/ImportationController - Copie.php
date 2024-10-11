<?php

namespace App\Controller;

use App\Entity\Security\Menu;
use App\Entity\Security\Module;
use App\Entity\Security\Permission;
use App\Entity\Security\Profile;
use App\Entity\Security\Role;
use App\Entity\Security\User;
use App\Repository\Security\MenuRepository;
use App\Repository\Security\ModuleRepository;
use App\Repository\Security\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class ImportationController extends AbstractController
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

    public function __invoke(Request $request, ModuleRepository $moduleRepository, MenuRepository $menuRepository, PermissionRepository $permissionRepository): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $data = $this->jsondecode();

        if(!isset($data->data))
            return throw new BadRequestHttpException('file is compulsory');

        $file = $data->data;

        if (!$file) {
            throw new BadRequestHttpException('"file" is required');
        }

        dd($file);

        $profile = new Profile();
        $profile->setName($name);
        $profile->setUser($this->getUser());
        $this->entityManager->persist($profile);

        foreach ($permissionIds as $moduleId){
            if ($moduleId){
                $role = new Role();
                $role->setProfile($profile);

                $role->setName($name);
                $role->setModule($this->entityManager->getRepository(Module::class)->find($moduleId->module));

                $role->setStatus(true);
                $this->entityManager->persist($role);
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
                'permission' => $role->getPrivilege()->getName(),
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



