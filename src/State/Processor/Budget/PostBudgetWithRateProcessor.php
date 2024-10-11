<?php

namespace App\State\Processor\Budget;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Budget\Budget;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PostBudgetWithRateProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor, private readonly TokenStorageInterface $tokenStorage,
    EntityManagerInterface $manager) {
    }


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $institution = $this->getUser()->getInstitution();

        if ($data instanceof Budget){
            $rate = $data->getRate();
            $rate = $rate/100;

            $amount = $data->getAmount();

            $rateAmount = $rate * $amount;

            $isIncrease = $data->isIsIncrease();

            if ($isIncrease){
            $amount =$amount + $rateAmount  ;
            }else
            {
            $amount = $amount - $rateAmount  ;
            }
            $data->setAmount($amount);
            $data->setInstitution($institution);
        }
    
        return $this->processor->process( $data,  $operation, $uriVariables, $context);
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
