<?php

namespace App\Controller\Inventory\Reception;

use App\Entity\Inventory\Reception;
use App\Entity\Security\User;
use App\Repository\Inventory\ReceptionItemRepository;
use App\Repository\Inventory\ReceptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class ValidateReceptionController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(ReceptionItemRepository $receptionItemRepository,
                             ReceptionRepository $receptionRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $reception = $receptionRepository->find($id);

        if(!$reception instanceof Reception)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This data must be type of reception.'], 404);
        }

        if(!$reception)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This reception is not found.'], 404);
        }

        $receptionItem = $receptionItemRepository->findOneBy(['reception' => $reception]);
        if(!$receptionItem)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'Cannot proceed : the cart is empty.'], 404);
        }

        $reception->setStatus('reception');

        $entityManager->flush();

        return $this->json(['hydra:member' => '200']);
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
