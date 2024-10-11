<?php

namespace App\Controller\Security;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\Security\RefreshToken;
use App\Entity\Security\Otp;
use App\Entity\Security\User;
use App\Entity\Setting\SystemConfiguration;
use App\Repository\Security\OtpRepository;
use App\Repository\Security\ProfileRepository;
use App\Repository\Security\RoleRepository;
use App\Repository\Security\Session\YearRepository;
use App\Repository\Security\UserRepository;
use App\Repository\Setting\SystemConfigurationRepository;
use App\Service\CountService;
use App\Service\SmsService;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsController]
class SecurityController extends AbstractController
{

    public function __construct(private readonly JWTEncoderInterface $jwtEncoder,
                                private RefreshTokenManagerInterface $refreshTokenManager,
                                private readonly TokenStorageInterface $tokenStorage,
                                private RequestStack $requestStack,
                                private \Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface $csrfTokenStorage,
                                private EntityManagerInterface $entityManager,
                                private readonly YearRepository $yearRepository,
                                private readonly SystemConfigurationRepository $systemConfigurationRepository,
                                private RoleRepository $roleRepository,
                                private readonly CountService $countService,
                                private readonly SmsService $smsService,
                                private readonly UserPasswordHasherInterface $userPasswordHashed
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

    #[Route('/api/user/modules', name: 'api_user_modules', methods: ['GET'])]
    public function modules(): JsonResponse
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

        $profile = $user->getProfile();
        $roles = $this->roleRepository->findDistinctModuleByProfile($profile);

        $selectedRoles = [];
        foreach ($roles as $role)
        {
            $selectedRoles [] = [
                'id' => $role['id'],
                'name' => $role['name'],
                'color' => $role['color'],
                'icon' => $role['icon'],
                'path' => $role['path'],
                'layout' => $role['layout']
            ];
        }

        return $this->json([
            'user' => $this->getUser(),
            "modules" => $selectedRoles
        ]);
    }

    #[Route('/api/user/menus/{id}', name: 'api_user_menus', methods: ['DELETE'])]
    public function menus($id): JsonResponse
    {
        // L'id en parametre est celui du module.

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

        $profile = $user->getProfile();
        $roles = $this->roleRepository->findDistinctMenuByProfile($profile, $id);

        $selectedRoles = [];

        foreach ($roles as $role)
        {
            if ($this->serializeChildMenu($id, $role['name'], $profile)){
                $selectedRoles [] = [
                    //'bookmark' => true,
                    'path' => $role['path'],
                    'name' => $role['name'],
                    'title' => $role['title'],
                    'icon' => $role['icon'],
                    'type' => $role['type'],
                    'id' => $role['id'],
                    'active' => $role['active'],
                    'children' => $this->serializeChildMenu($id, $role['name'],$profile)
                ];
            }
            else{
                $selectedRoles [] = [
                    //'bookmark' => true,
                    'path' => $role['path'],
                    'name' => $role['name'],
                    'title' => $role['title'],
                    'icon' => $role['icon'],
                    'type' => $role['type'],
                    'id' => $role['id'],
                    'active' => $role['active'],
                    //'children' => $this->serializeChildMenu($id, $role['name'])
                ];
            }


        }

        return $this->json(['modules' => $selectedRoles]);
    }

    private function serializeChildMenu($id, $menuName, $profile): array
    {
        $roles = $this->roleRepository->findDistinctChildrenMenuByRoleName($id, $menuName, $profile);

        $selectedRoles = [];
        foreach ($roles as $menu)
        {
            $selectedRoles[] = [
                'path' => $menu['path'],
                'name' => $menu['name'],
                'title' => $menu['title'],
                'type' => $menu['type'],
                'id' => $menu['id'],
                //'active' => $menu['active'],
            ];
        }

        return $selectedRoles;
    }

    #[Route('/api/auth/login', name: 'api_auth_login', methods: ['POST'])]
    public function coreAuth(JWTTokenManagerInterface $tokenManager,
                             ParameterBagInterface $parameterBag,
                             RefreshTokenManagerInterface $refreshTokenManager,
                             UserRepository $userRepository,
                             ProfileRepository $profileRepository): JsonResponse
    {
        $data = $this->jsondecode();
        $userData = $data->user;

        if (!isset($userData->username, $userData->password, $data->core) || ($userData->username === "" || $userData->password === "" || $data->core === ""))
            return new JsonResponse(['message' => 'Invalid Form'],Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);

        $user = null;

        if ($data->core == "teacher")
        {
            // Lorsque nous sommes dans le portail enseignant: core = teacher
            $profile = $profileRepository->findOneBy(['name' => 'Teacher']);
            if (!$profile)
                return new JsonResponse(['message' => 'teacher profile not found'],Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);

            $user = $userRepository->findOneBy(['username' => $userData->username, 'profile' => $profile]);
        }
        else if ($data->core == "student")
        {
            // Lorsque nous somme dans le portail etudiant: core = student
            $profile = $profileRepository->findOneBy(['name' => 'Student']);
            if (!$profile)
                return new JsonResponse(['message' => 'student profile not found'],Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);

            $user = $userRepository->findOneBy(['username' => $userData->username, 'profile' => $profile]);
        }
        else if ($data->core == "main")
        {
            // Lorsque nous somme dans le mode admin: core = main
            $user = $userRepository->findOneByUsername($userData->username);
        }

        if (!$user)
            return new JsonResponse(['message' => 'user not found'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);

        if (!($user instanceof User))
            return new JsonResponse(['message' => 'internal data error'],Response::HTTP_INTERNAL_SERVER_ERROR, ['Content-Type', 'application/json']);

        if (!$this->hashed()->isPasswordValid($user, $userData->password))
            return new JsonResponse(['message' => 'invalid password'],Response::HTTP_INTERNAL_SERVER_ERROR, ['Content-Type', 'application/json']);

        /* Debut: Traitement du Refresh Token */
        $ttl = $parameterBag->get('gesdinet_jwt_refresh_token.ttl');

        // Générer un refresh token
        $refreshToken = $refreshTokenManager->create();
        $refreshToken->setUsername($user->getUsername());

        // Obtenir la date et l'heure actuelles
        $currentDate = new DateTime();
        $currentDate->modify('+'.$ttl.' seconds');
        $formattedDate = $currentDate->format('Y-m-d H:i:s');
        $dateTime = new DateTime($formattedDate);

        $refreshToken->setValid($dateTime);

        // Générer une chaîne aléatoire pour le refresh token
        $refreshTokenValue = bin2hex(random_bytes(64));
        $refreshToken->setRefreshToken($refreshTokenValue);

        $refreshTokenManager->save($refreshToken);

        /* Fin: Traitement du Refresh Token */


        // To update, after - after
        if (!$user->getCurrentYear()){
            $user->setCurrentYear($this->yearRepository->findOneBy(['isCurrent' => true]));
            $this->entityManager->flush();
        }

        $isTwoAuthConfig = $this->systemConfigurationRepository->findOneBy(['code' => 'TWOAUTH']);
        $isSMSTransportConfig = $this->systemConfigurationRepository->findOneBy(['code' => 'ISSMS']);
        $isMaintenanceConfig = $this->systemConfigurationRepository->findOneBy(['code' => 'MAINTENANCE']);

        $userData = [
            "id" => $user->getId(),
            "firstname" => $user->getFirstname(),
            "lastname" => $user->getLastname(),
            "phone" => $user->getPhone(),
            "email" => $user->getEmail(),
            "picture" => $user->getPicture(),
            "last_login" => $user->getLastLoginAt(),
            "profile" => $user->getProfile() ? $user->getProfile()->getName() : '',
            "isIsStudentSystem" => $user->getProfile() ? $user->getProfile()->isIsStudentSystem() : '',
            "isIsTeacherSystem" => $user->getProfile() ? $user->getProfile()->isIsTeacherSystem() : '',
            'currentYear' => $user->getCurrentYear() ? $user->getCurrentYear()->getYear() : '',
            'systemYear' => $this->yearRepository->findOneBy(['isCurrent' => true])->getYear(),
            'institution' => $user->getInstitution() ? $user->getInstitution()->getCode() : '',
            'instId' => $user->getInstitution() ? $user->getInstitution()->getId() : '',
            "twoAuthMode" => $isTwoAuthConfig ? $isTwoAuthConfig->isValue() : '',
            "twoAuthTransport" => $isSMSTransportConfig ? $isSMSTransportConfig->isValue() : '',
            "maintenance" => $isMaintenanceConfig ? $isMaintenanceConfig->isValue() : '',
        ];

        if ($isTwoAuthConfig){

            // If auth config is configured

            if ($isTwoAuthConfig->isValue()){

                // If auth config (two auth) is true

                if ($isSMSTransportConfig){

                    // If transport config is configue

                    if ($isSMSTransportConfig->isValue()){

                        // If the (sms) transport is true

                        // On essaie d'écrire le l'otp en base de données
                        try{
                            $otp = new Otp();

                            $minutes_to_add = 3;
                            $time = new DateTime('now');
                            $time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
                            $stamp = $time;

                            $otp->setExpiredAt($stamp);
                            $otp->setUser($user);
                            $otp->setOpt($this->countService->random(6));
                            $otp->setDestination($user->getPhone());
                            $otp->setTransport('sms');
                            $otp->setRole('SMS-TWOFACTOR');
                            $otp->setUsed(false);

                            $this->entityManager->persist($otp);
                            $this->entityManager->flush();

                            $this->smsService->opt($user->getPhone(), $otp->getOpt());

                            if (($user->getProfile() ? $user->getProfile()->isIsTeacherSystem(): '') || ($user->getProfile() ? $user->getProfile()->isIsStudentSystem() : '')){
                                //$userData += ['gggg' => 'kjhjkj'];
                                $userData['moduleId'] = $this->roleRepository->findOneBy(['profile' => $user->getProfile()])->getModule()->getId();
                                $userData['modulePath'] = $this->roleRepository->findOneBy(['profile' => $user->getProfile()])->getModule()->getPath();
                                $userData['moduleLayout'] = $this->roleRepository->findOneBy(['profile' => $user->getProfile()])->getModule()->getLayout();

                            }
                            else{

                                // UserData reste normal
                            }


                        } catch (\Exception $e) {
                            return new JsonResponse(['hydra:title' => $e->getMessage()],Response::HTTP_BAD_REQUEST);
                        }
                    }
                    else{

                        // If the (email) transport is enable [when property is false]

                        // On essaie d'écrire le l'otp en base de données
                        try{
                            $otp = new Otp();

                            $minutes_to_add = 3;
                            $time = new DateTime('now');
                            $time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
                            $stamp = $time;

                            $otp->setExpiredAt($stamp);
                            $otp->setUser($user);
                            $otp->setOpt($this->countService->random(6));
                            $otp->setDestination($user->getEmail());
                            $otp->setTransport('email');
                            $otp->setRole('EMAIL-TWOFACTOR');
                            $otp->setUsed(false);
                            $otp->setSendCount($otp->getSendCount() + 1);
                            $otp->setCreatedAt(new \DateTime());

                            $this->entityManager->persist($otp);
                            // $this->entityManager->flush();

                            // On génère l'e-mail
                            $message = (new TemplatedEmail())
                                ->from(new Address('noreply@globalerpcm.com', 'GlobalERP'))
                                ->subject('Two factor auth')
                                ->to($user->getEmail())
                                ->html(
                                    "Bonjour,<br><br>Une demande  de mot de passe a été effectuée. Veuillez copier les numero(s) suivant(s) : " . $otp->getOpt(),
                                    'text/html'
                                )
                            ;


                            // Le long if qui est dans le try la etait normalement ici


                        } catch (\Exception $e) {
                            echo $e->getMessage();
                            return new JsonResponse(['hydra:title' => $e->getMessage()],Response::HTTP_BAD_REQUEST);
                        }


                    }

                }
                else{

                    // return new JsonResponse(['hydra:title' => 'This mode is configure the missing the join one to be able to process'],Response::HTTP_NOT_FOUND);
                }

            }else{
                // If auth config (simple auth) is enabled [when property is false]
                if (($user->getProfile() ? $user->getProfile()->isIsTeacherSystem(): '') || ($user->getProfile() ? $user->getProfile()->isIsStudentSystem() : '')){

                    $userData['moduleId'] = $this->roleRepository->findOneBy(['profile' => $user->getProfile()])->getModule()->getId();
                    $userData['modulePath'] = $this->roleRepository->findOneBy(['profile' => $user->getProfile()])->getModule()->getPath();
                    $userData['moduleLayout'] = $this->roleRepository->findOneBy(['profile' => $user->getProfile()])->getModule()->getLayout();

                }
                else{
                    // userData doit rester inchange

                }

            }
        }
        else {

            // If auth config is not configured
            // return new JsonResponse(['hydra:title' => 'The mode is not configure'],Response::HTTP_NOT_FOUND);

            if (($user->getProfile() ? $user->getProfile()->isIsTeacherSystem() : '') || ($user->getProfile() ? $user->getProfile()->isIsStudentSystem() : '')) {
                $userData['moduleId'] = $this->roleRepository->findOneBy(['profile' => $user->getProfile()])->getModule()->getId();
                $userData['modulePath'] = $this->roleRepository->findOneBy(['profile' => $user->getProfile()])->getModule()->getPath();
                $userData['moduleLayout'] = $this->roleRepository->findOneBy(['profile' => $user->getProfile()])->getModule()->getLayout();
            } else {

                // userData reste inchange

            }
        }

        // To update, after - after

        return $this->json([
            'user' => $userData,
            'token' => $tokenManager->create($user),
            'refresh_token' => $refreshToken->getRefreshToken()
        ]);

    }

    public function hashed(): UserPasswordHasherInterface
    {
        return $this->userPasswordHashed;
    }

    #[Route('/api/verify/user/{id}/two/factor/auth/role/{role_id}/otp/{otp_id}', name: 'api_verify_two_factor_auth', methods: ['POST'])]
    public function twoFactAuth($id, $role_id, $otp_id, OtpRepository $otpRepository): JsonResponse
    {
        if (!isset($id, $role_id, $otp_id) || ($id === "" || $role_id === "" || $otp_id === ""))
            return new JsonResponse(['message' => 'Some parameter(s) is missing'],Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);

        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user){
            return $this->json([
                'message' => 'User not found'
            ], 404);

        }

        if (!$user instanceof User){
            return $this->json([
                'message' => 'Invalid login request: check that the Content Type'
            ], 401);
        }

        $myOtp = $otpRepository->findOneBy(['user' => $user, 'role' => $role_id, 'opt' => $otp_id, 'used' => false]);
        if (!$myOtp)
        {
            return new JsonResponse(['message' => 'Incorrect OTP'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
        }

        if ($myOtp->isUsed()){
            return new JsonResponse(['message' => 'OTP already use'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
        }

        if ($myOtp->getExpiredAt() <= new \DateTime('now'))
        {
            $this->entityManager->remove($myOtp);
            $this->entityManager->flush();
            return new JsonResponse(['message' => 'OTP expired'],Response::HTTP_NOT_ACCEPTABLE, ['Content-Type', 'application/json']);

        }

        $myOtp->setUsed(true);
        $this->entityManager->flush();

        return $this->json([
            //'user' => $this->getUser(),
            'user' => [
                "id" => $user->getId(),
                "firstname" => $user->getFirstname(),
                "lastname" => $user->getLastname(),
                "phone" => $user->getPhone(),
                "email" => $user->getEmail(),
                "picture" => $user->getPicture(),
                "last_login" => $user->getLastLoginAt(),
                "profile" => $user->getProfile() ? $user->getProfile()->getName() : '',
                "isIsStudentSystem" => $user->getProfile()->isIsStudentSystem(),
                "isIsTeacherSystem" => $user->getProfile()->isIsTeacherSystem(),
                "moduleId" => $this->roleRepository->findOneBy(['profile' => $user->getProfile()])->getModule()->getId(),
                "modulePath" => $this->roleRepository->findOneBy(['profile' => $user->getProfile()])->getModule()->getPath(),
                // 'currentYear' => $user->getCurrentSession() ? $user->getCurrentSession()->getYear() : '',
                // 'systemYear' => $this->schoolYearRepository->findOneBy(['isCurrent' => true])->getYear(),
                'institution' => $user->getInstitution() ? $user->getInstitution()->getCode() : '',
            ]
        ]);
    }

    #[Route('/api/resend/otp/user/{id}/two/factor/auth/role/{role_id}', name: 'api_resend_two_factor_auth_otp', methods: ['POST'])]
    public function resendOtp($id, $role_id, OtpRepository $otpRepository): JsonResponse
    {
        if (!isset($id, $role_id) || ($id === "" || $role_id === ""))
            return new JsonResponse(['message' => 'Some parameter(s) is missing'],Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);

        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user){
            return $this->json([
                'message' => 'User not found'
            ], 404);

        }

        if (!$user instanceof User){
            return $this->json([
                'message' => 'Invalid login request: check that the Content Type'
            ], 401);
        }

        $myOpt = $otpRepository->findOneBy(['user' => $user, 'role' => $role_id, 'used' => false]);

        if ($myOpt)
        {
            //dd($myOpt->getExpiredAt()->format('d/m/Y H:i'). '---'.date('d/m/Y H:i'));
            if ($myOpt->getExpiredAt()->format('d/m/Y H:i') > date('d/m/Y H:i'))
            {
                return new JsonResponse(['message' => 'OTP is already send and not yet expired'],Response::HTTP_NOT_ACCEPTABLE, ['Content-Type', 'application/json']);
            }
        }

        if ($role_id == 'SMS-TWOFACTOR'){
            $otp = new Otp();

            $minutes_to_add = 3;
            $time = new \DateTime('now');
            $time->add(new \DateInterval('PT' . $minutes_to_add . 'M'));
            $stamp = $time;

            $otp->setExpiredAt($stamp);
            $otp->setUser($user);
            $otp->setOpt($this->countService->random(6));
            $otp->setDestination($user->getPhone());
            $otp->setTransport('sms');
            $otp->setRole('SMS-TWOFACTOR');
            $otp->setUsed(false);

            $this->smsService->opt($user->getPhone(), $otp->getOpt());

            $this->entityManager->persist($otp);
        }else{
            //
        }

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'OPT sent with success'],Response::HTTP_OK, ['Content-Type', 'application/json']);
    }

    #[Route('/api/user/update/password', name: 'api_update_password_auth', methods: ['POST'])]
    public function updatePassword(UserPasswordHasherInterface $hasher): JsonResponse
    {
        $data = $this->jsondecode();

        $oldPassword = $data->oldPassword;
        $newPassword = $data->newPassword;
        $confirmNewPassword = $data->confirmNewPassword;

        if (!isset($oldPassword, $newPassword, $confirmNewPassword) || ($oldPassword === "" || $newPassword === "" || $confirmNewPassword === ""))
            return new JsonResponse(['message' => 'Invalid form'],Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $this->getUser()]);

        if (!$user){
            return $this->json([
                'message' => 'User not found'
            ], 404);

        }

        if (!$user instanceof User){
            return $this->json([
                'message' => 'Invalid login request: check that the Content Type'
            ], 401);
        }

        if (password_verify($oldPassword, $user->getPassword()))
        {
            if ($newPassword !== $confirmNewPassword)
                return new JsonResponse(['message' => 'Confirm password isn\'t equal to new password'],Response::HTTP_INTERNAL_SERVER_ERROR, ['Content-Type', 'application/json']);

            $hashedPassword = $hasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $this->entityManager->flush();
        }
        else{
            return new JsonResponse(['message' => 'Your current password is wrong'],Response::HTTP_INTERNAL_SERVER_ERROR, ['Content-Type', 'application/json']);
        }

        return new JsonResponse(['message' => 'New password set with success'],Response::HTTP_OK, ['Content-Type', 'application/json']);
    }

    #[Route('/api/user/phone/verification', name: 'api_user_phone_verification', methods: ['POST'])]
    public function phoneVerification(SmsService $smsService, UserRepository $userRepository, CountService $countService): JsonResponse
    {
        $data = $this->jsondecode();

        $phone = $data->phone;

        $user = $userRepository->findOneBy(['phone' => $phone]);

        if (!$user)
        {
            return new JsonResponse(['message' => 'Phone is not found'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
        }

        $x = $countService->random(6);

        $otp = new Otp();

        $minutes_to_add = 3;
        $time = new \DateTime('now');
        $time->add(new \DateInterval('PT' . $minutes_to_add . 'M'));
        $stamp = $time;

        $otp->setExpiredAt($stamp);
        $otp->setUser($user);
        $otp->setOpt($x);
        $otp->setDestination($user->getPhone());
        $otp->setTransport('sms');
        $otp->setRole('Reset');
        $otp->setUsed(false);

        $smsService->reset($user->getPhone(), $x);

        $this->entityManager->persist($otp);

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Opt send with success',
            'role' => $otp->getRole(),
            'id' => $user->getId()
        ],Response::HTTP_OK, ['Content-Type', 'application/json']);
    }

    #[Route('/api/user/{id}/otp/verification/role/{role}', name: 'api_user_otp_verification', methods: ['POST'])]
    public function otpVerification($id, $role, OtpRepository $otpRepository, EntityManagerInterface $entityManager, CountService $countService): JsonResponse
    {
        $data = $this->jsondecode();

        $otp = $data->otp;

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $id]);

        if (!$user){
            return $this->json([
                'message' => 'User not found'
            ], 404);
        }

        $myOtp = $otpRepository->findOneBy(['user' => $user, 'role' => $role, 'opt' => $otp, 'used' => false]);
        if (!$myOtp)
        {
            return new JsonResponse(['message' => 'Incorrect OTP'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
        }

        if ($myOtp->isUsed()){
            return new JsonResponse(['message' => 'OTP already use'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
        }

        if ($myOtp->getExpiredAt() <= new \DateTime('now'))
        {
            $this->entityManager->remove($myOtp);
            $this->entityManager->flush();
            return new JsonResponse(['message' => 'OTP expired'],Response::HTTP_NOT_ACCEPTABLE, ['Content-Type', 'application/json']);

        }

        $myOtp->setUsed(true);

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Opt verified with success, reset your password',
        ],Response::HTTP_OK, ['Content-Type', 'application/json']);
    }

    #[Route('/api/user/{id}/reset/password', name: 'api_user_reset_password', methods: ['POST'])]
    public function resetPassword($id, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $data = $this->jsondecode();

        $newPassword = $data->newPassword;
        $confirmNewPassword = $data->confirmNewPassword;

        if (!isset($newPassword, $confirmNewPassword) || ($newPassword === "" || $confirmNewPassword === ""))
            return new JsonResponse(['message' => 'Invalid form'],Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $id]);

        if (!$user){
            return $this->json([
                'message' => 'User not found'
            ], 404);
        }

        if (!$user instanceof User){
            return $this->json([
                'message' => 'Invalid login request: check that the Content Type'
            ], 401);
        }

        if ($newPassword !== $confirmNewPassword)
            return new JsonResponse(['message' => 'Confirm password isn\'t equal to new password'],Response::HTTP_INTERNAL_SERVER_ERROR, ['Content-Type', 'application/json']);

        $hashedPassword = $hasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Password reset with success'],Response::HTTP_OK, ['Content-Type', 'application/json']);
    }

    #[Route('/api/mob/login', name: 'api_mob_login', methods: ['POST'])]
    public function mobLogin(): JsonResponse
    {
        $data = $this->jsondecode();

        if (!isset($data->username) || ($data->username === ""))
            return new JsonResponse(['message' => 'Invalid form'],Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $data->username]);

        if (!$user)
            return new JsonResponse(['message' => 'User not found'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);

        $optRole = '';

        $isSMSTransportConfig = $this->entityManager->getRepository(SystemConfiguration::class)->findOneBy(['code' => 'ISSMS']);
        if ($isSMSTransportConfig){
            if (!$isSMSTransportConfig->isValue()){
                $optRole = 'EMAIL-TWOFACTOR';
            }else{
                $optRole = 'SMS-TWOFACTOR';
                $otp = new Otp();

                $minutes_to_add = 3;
                $time = new \DateTime('now');
                $time->add(new \DateInterval('PT' . $minutes_to_add . 'M'));
                $stamp = $time;

                $otp->setExpiredAt($stamp);
                $otp->setUser($user);
                $otp->setOpt($this->countService->random(6));
                $otp->setDestination($user->getPhone());
                $otp->setTransport('sms');
                $otp->setRole('SMS-TWOFACTOR');
                $otp->setUsed(false);

                $this->smsService->opt($user->getPhone(), $otp->getOpt());

                $this->entityManager->persist($otp);
            }
        }else{
            $optRole = 'SMS-TWOFACTOR';

            $otp = new Otp();

            $minutes_to_add = 3;
            $time = new \DateTime('now');
            $time->add(new \DateInterval('PT' . $minutes_to_add . 'M'));
            $stamp = $time;

            $otp->setExpiredAt($stamp);
            $otp->setUser($user);
            $otp->setOpt($this->countService->random(6));
            $otp->setDestination($user->getPhone());
            $otp->setTransport('sms');
            $otp->setRole('SMS-TWOFACTOR');
            $otp->setUsed(false);

            $this->smsService->opt($user->getPhone(), $otp->getOpt());

            $this->entityManager->persist($otp);
        }

        $this->entityManager->flush();

        return $this->json([
                'user' => $user->toArray(),
                'otpRole' => $optRole,
                'message' => 'User is well existing',
                // 'message' => 'Welcome '.$user->getName(),
                //'token' => $tokenManager->create($user)
            ]
        );
    }

    #[Route('/api/mob/user/{id}/otp/verification/role/{role}', name: 'api_mob_user_otp_verification', methods: ['POST'])]
    public function mobOtpVerification($id, $role, OtpRepository $otpRepository, JWTTokenManagerInterface $tokenManager, EntityManagerInterface $entityManager, CountService $countService): JsonResponse
    {
        $data = $this->jsondecode();

        $otp = $data->otp;

        if (!isset($data->otp) || ($data->otp === ""))
            return new JsonResponse(['message' => 'Invalid form'],Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $id]);

        if (!$user){
            return $this->json([
                'message' => 'User not found'
            ], 404);
        }

        $myOtp = $otpRepository->findOneBy(['user' => $user, 'role' => $role, 'opt' => $otp, 'used' => false]);
        if (!$myOtp)
        {
            return new JsonResponse(['message' => 'Incorrect OTP'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
        }

        if ($myOtp->isUsed()){
            return new JsonResponse(['message' => 'OTP already use'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
        }

        if ($myOtp->getExpiredAt() <= new \DateTime('now'))
        {
            $this->entityManager->remove($myOtp);
            $this->entityManager->flush();
            return new JsonResponse(['message' => 'OTP expired'],Response::HTTP_NOT_ACCEPTABLE, ['Content-Type', 'application/json']);

        }

        $myOtp->setUsed(true);

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Opt verified with success',
            'token' => $tokenManager->create($user)
        ]);
    }

    #[Route('/api/mob/user/profile', name: 'app_security_mob_user_profile', methods: ['POST'])]
    public function userProfile(UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = $this->jsondecode();

        $firstname = $data->firstname;
        $lastname = $data->lastname;
        $phone = $data->phone;
        $email = $data->email;

        $user = $userRepository->findOneBy(['id' => $this->getUser()]);

        $user->setUpdatedAt(new \DateTimeImmutable());
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setPhone($phone);
        $user->setEmail($email);

        $entityManager->flush();

        $updatedUser[] = $userRepository->findOneBy(['id' => $this->getUser()]);

        return new JsonResponse([
            'message' => 'Profile updated successfully',
            'client' => array_map(function (User $user){
                return $user->profileArray();
            }, $updatedUser)
        ], Response::HTTP_OK, ['Content-Type', 'application/json']);
    }


    #[Route('/api/mob/user/online', name: 'app_security_mob_user_online', methods: ['GET'])]
    public function onlineUser(): JsonResponse
    {
        return $this->json([
                'user' => $this->getUser() ? $this->getUser()->toArray() : "",
            ]
        );
    }

    #[Route('/api/mob/logout', name: 'app_security_mob_logout', methods: ['POST'])]
    public function mobLogout(): JsonResponse
    {
        return $this->json([
                'user' => $this->getUser() ? $this->getUser()->toArray() : "",
                'message' => 'Logout with success'
            ]
        );
    }

    #[Route('/api/token/invalidate', name: 'app_security_logout', methods: ['POST'])]
    public function logout(): Response
    {
        $token = $this->tokenStorage;
        $this->refreshTokenManager->revokeAllInvalid();

        if ($token !== null) {
            $this->jwtEncoder->encode([
                'username' => $token->getToken()->getUser()->getUsername(),
                'exp' => 0,
            ]);
            // $jws['exp'] = 0;
            $this->requestStack->getCurrentRequest()->server->remove('HTTP_AUTHORIZATION');
            $this->requestStack->getCurrentRequest()->headers->remove('authorization');

            $this->requestStack->getCurrentRequest()->getSession()->invalidate();
            $token->setToken(null);
        }


        $csrfTokenId = $this->requestStack->getCurrentRequest()->get('_csrf_token_id');

        if ($csrfTokenId !== null) {
            $this->csrfTokenStorage->removeToken($csrfTokenId);
        }

        $tokens = $this->entityManager->getRepository(RefreshToken::class)->findBy([
            'username' => $token->getToken() ? $token->getToken()->getUserIdentifier(): ''
        ]);

        foreach ($tokens as $item) {
            $this->entityManager->remove($item);
        }

        $this->entityManager->flush();

        return new Response();
    }

}
