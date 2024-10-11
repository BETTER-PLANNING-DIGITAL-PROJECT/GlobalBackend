<?php
namespace App\Controller\Security;

use App\Entity\Security\User;
use App\Repository\Security\Session\YearRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]

class SetUserCurrentYearController extends AbstractController{

    public function __construct(private readonly EntityManagerInterface $manager, private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, YearRepository $yearRepository)
    {
        // TODO: Implement __invoke() method.

        $year = $yearRepository->findOneBy(['id' => $request->get('id')]);

        $this->getUser()->setCurrentYear($year);
        $this->manager->flush();

        return $year->getYear();
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
