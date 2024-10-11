<?php

namespace App\Controller\Budget;

use App\Entity\Security\User;
use App\Repository\Budget\BudgetSectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class BudgetSectionUniquenessPerLevelController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data,Request $request, BudgetSectionRepository $budgetSectionRepo)
    {

        $code = $data->getCode();
        $name = $data->getName();
        $level = $data->getLevel();

        $existingBudgetSections = $budgetSectionRepo->findOneBy(['code' => $code, 'name' => $name, 'level' => $level]);
        if ($existingBudgetSections) {
            return new JsonResponse(['hydra:description' => 'This code and name Combination already exists for this level.'], 400);
        } else {
            $data->setInstitution($this->getUser()->getInstitution());
            $data->setUser($this->getUser());
            $data->setYear($this->getUser()->getCurrentYear());

        }
        return $data;
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
