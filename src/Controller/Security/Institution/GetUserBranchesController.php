<?php

namespace App\Controller\Security\Institution;

use App\Entity\Security\User;
use App\Repository\Security\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetUserBranchesController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(Request $request, UserRepository $userRepository):JsonResponse
    {
        $currentUser = $this->getUser();

        $userBranches = $currentUser->getUserBranches();

        $myUserBranches = [];
        foreach ($userBranches as $userBranch) {
            $myUserBranches[] = [
                '@id' => "/api/get/branch/" . $userBranch->getId(),
                '@type' => 'Branch',
                'id' => $userBranch->getId(),
                'name' => $userBranch->getName(),
                'code' => $userBranch->getCode(),
                'userBranches' => [
                    '@id' => "/api/get/user/" . $currentUser->getId(),
                    '@type' => "User",
                    'id' => $currentUser->getId() ? $currentUser->getId() : '',
                ],
            ];
        }

        return $this->json(['hydra:member' => $myUserBranches]);
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
