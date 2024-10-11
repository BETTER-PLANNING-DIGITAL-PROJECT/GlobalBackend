<?php

namespace App\Controller\Dashboard;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\Security\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class SecurityDashboardController extends AbstractController
{
    private EntityManagerInterface $manager;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                Request                                $req, EntityManagerInterface $manager,
                                UserRepository    $userRepository,)
    {
        $this->manager = $manager;
        $this->userRepository = $userRepository;
    }


    #[Route('/api/get/security/dashboard/number-of-users', name: 'app_get_security_dashboard_number_of_users')]
    public function NumberOfUsers(): JsonResponse
    {

        $userCount= $this->userRepository->countUsers();
        return new JsonResponse(['hydra:description' => $userCount]);

    }

    #[Route('/api/get/security/dashboard/current-session', name: 'app_get_security_dashboard_current_session')]
    public function CurrentSession(): JsonResponse
    {
        $currentYear = $this->getUser()->getCurrentYear()->getYear();
        return new JsonResponse(['hydra:description' => $currentYear]);

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
