<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class SchoolYearProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof Year){
            $data->setUser($this->getUser());
            $data->setInstitution($this->getUser()->getInstitution());
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
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
