<?php

namespace App\Controller;

use App\Repository\EmployeeRepository;
use App\Repository\NjangiRepository;
use App\Repository\Security\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class DeleteSelectedRolesController extends AbstractController
{
    private Request $req;
    private EntityManagerInterface $manager;
    public function __construct(EntityManagerInterface $manager, Request $req)
    {
        $this->req = $req;
        $this->manager = $manager;
    }

    public function __invoke(RoleRepository $roleRepository)
    {
        $objectName = $this->container->get('request_stack')->getCurrentRequest()->attributes->get('_api_resource_class');

        $ids = json_decode($this->req->getContent(), true);

        if (!is_array($ids)) {
            return new Response('Invalid request body.', Response::HTTP_BAD_REQUEST);
        }

        foreach ($ids as $id){
            $objectId = $this->manager->getRepository($objectName)->findOneBy(['id' => $id]);

            $roles = $roleRepository->findBy(['name' => $objectId->getName()]);
            foreach ($roles as $role){
                $this->manager->remove($role);
                $this->manager->flush();
            }
        }

        return new Response(null, Response::HTTP_NO_CONTENT);

    }



}
