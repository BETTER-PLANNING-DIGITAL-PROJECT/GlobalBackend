<?php

namespace App\State\Processor\Global;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Security\Profile;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SystemProcessor implements ProcessorInterface
{
    public function __construct(private ProcessorInterface $persistProcessor, private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        // institution
        $data->setInstitution($this->getUser()->getInstitution());

        // branch
        // $data->setBranch($this->getUser()->getBranch());

        // user
        $data->setUser($this->getUser());

        // year
        if ($data instanceof Year){
            // nothing to do
        } else{
            $data->setYear($this->getUser()->getCurrentYear());
        }

        $this->persistProcessor->process($data, $operation, $uriVariables, $context);
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
