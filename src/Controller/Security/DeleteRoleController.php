<?php

namespace App\Controller\Security;

use App\Entity\Security\Profile;
use App\Entity\Security\User;
use App\Repository\Security\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class DeleteRoleController extends AbstractController
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

    public function __construct(private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(Request $request, RoleRepository $roleRepository): Response
    {
        $objectName = $this->container->get('request_stack')->getCurrentRequest()->attributes->get('_api_resource_class');

        // Verifier si l'id envoye est un entier
        $id = $request->get('id');
        /*if (!is_int($id)) {
            return new Response('Invalid request body.', Response::HTTP_BAD_REQUEST);
        }*/

        $objectId = $this->manager->getRepository($objectName)->findOneBy(['id' => $id]);

        // Verifier s'il s'agit d'un profile
        if (!$objectId instanceof Profile){
            return $this->json(['hydra:title' => 'Invalid data type'], Response::HTTP_BAD_REQUEST);
        }

        // Verifier si le profile est utilise pour un utilisateur.
        $user = $this->manager->getRepository(User::class)->findOneBy(['profile' => $objectId]);
        if ($user){
            return $this->json(['hydra:title' => 'Profile already use for user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $roles = $roleRepository->findBy(['profile' => $objectId]);
        foreach ($roles as $role){
            $this->manager->remove($role);
            $this->manager->flush();
        }

        $this->manager->remove($objectId);
        $this->manager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);

    }

}
