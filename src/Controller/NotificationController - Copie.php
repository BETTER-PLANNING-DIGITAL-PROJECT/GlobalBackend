<?php

namespace App\Controller;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\RefreshToken;
use App\Entity\Security\User;
use App\Repository\Security\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsController]
class NotificationController extends AbstractController
{

    public function __construct(private readonly JWTEncoderInterface $jwtEncoder,
                                private RefreshTokenManagerInterface $refreshTokenManager,
                                private readonly TokenStorageInterface $tokenStorage,
                                private RequestStack $requestStack,
                                private \Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface $csrfTokenStorage,
                                private EntityManagerInterface $entityManager,
                                private RoleRepository $roleRepository,
    )
    {
    }

    public function jsondecode()
    {
        try {
            return file_get_contents('php://input') ?
                json_decode(file_get_contents('php://input'), false) :
                [];
        }catch (\Exception $ex)
        {
            return [];
        }

    }

    #[Route('/api/sms/notification', name: 'api_sms_notification', methods: ['POST'])]
    public function sms(): JsonResponse
    {
        if (!$this->getUser()){
            return $this->json([
                'error' => 'Invalid login request: check that the Content Type'
            ], 401);
        }

        $user = $this->getUser();
        if (!$user instanceof User){
            return $this->json([
                'error' => 'User not found'
            ], 404);
        }

        $roleName = $user->getProfile()->getName();
        $roles = $this->roleRepository->findDistinctModuleByRoleName($roleName);

        $selectedRoles = [];
        foreach ($roles as $role)
        {
            $selectedRoles [] = [
                'id' => $role['id'],
                'name' => $role['name'],
                'color' => $role['color'],
                'icon' => $role['icon']
            ];
        }

        return $this->json([
            'user' => $this->getUser(),
            "modules" => $selectedRoles
        ]);
    }

    #[Route('/api/email/notification', name: 'api_email_notification', methods: ['POST'])]
    public function email(): JsonResponse
    {
        $data = $this->jsondecode();

        if (!$this->getUser()){
            return $this->json([
                'error' => 'Invalid login request: check that the Content Type'
            ], 401);
        }

        $user = $this->getUser();
        if (!$user instanceof User){
            return $this->json([
                'error' => 'User not found'
            ], 404);
        }


        return $this->json(['modules' => $user]);
    }


}
