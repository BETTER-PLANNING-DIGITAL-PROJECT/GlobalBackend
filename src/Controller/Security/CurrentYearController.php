<?php

namespace App\Controller\Security;

use App\Entity\Security\User;
use App\Repository\Security\Institution\InstitutionRepository;
use App\Repository\Security\RoleRepository;
use App\Repository\Security\Session\YearRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class CurrentYearController extends AbstractController
{

    public function __construct(private readonly YearRepository $yearRepo, private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(Request $request, RoleRepository $roleRepository,
                             InstitutionRepository $institutionRepository)
    {
        $currentYears = $this->yearRepo->findBy(['id'=> $this->getUser()->getCurrentYear()]);

        $myCurrentYears = [];

        foreach ($currentYears  as $currentYear){
            $myCurrentYears [] = [
                '@id' => "/api/year/get/".$currentYear->getId(),
                'id' => $currentYear->getId(),
                'year' => $currentYear->getYear(),
            ];
        }

        return $this->json(['hydra:member' => $myCurrentYears]);

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
