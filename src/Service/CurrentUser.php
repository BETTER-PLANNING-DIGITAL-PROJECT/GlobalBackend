<?php
namespace App\Service;

use App\Entity\Security\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class CurrentUser {

    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {}
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
