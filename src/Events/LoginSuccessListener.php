<?php

namespace App\Events;

use App\Entity\Security\Otp;
use App\Entity\Security\User;
use App\Repository\Security\RoleRepository;
use App\Repository\Security\Session\YearRepository;
use App\Repository\Setting\SystemConfigurationRepository;
use App\Service\CountService;
use App\Service\SmsService;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;

class LoginSuccessListener
{
    public function __construct(private readonly RoleRepository $roleRepository,
                                private readonly EntityManagerInterface $entityManager,
                                private readonly YearRepository $yearRepository,
                                private readonly SystemConfigurationRepository $systemConfigurationRepository,
                                private readonly CountService $countService,
                                private readonly SmsService $smsService

    )
    {
    }

    public function onLoginSuccess(AuthenticationSuccessEvent $event): JsonResponse
    {
        $user = $event->getUser();
        $payload = $event->getData();
        if (!$user instanceof User) {
            return new JsonResponse(['hydra:title' => 'Invalid login request: check that the Content Type'],Response::HTTP_BAD_REQUEST);
        }

        if (!$user->getCurrentYear()){
            $user->setCurrentYear($this->yearRepository->findOneBy(['isCurrent' => true]));
            $this->entityManager->flush();
        }

        if (!$user->getLoginCount() || $user->getLoginCount() == 0){
            $user->setLoginCount(1);
            $user->setFirstLoginAt(new \DateTime());
        }
        else{
            $user->setLoginCount($user->getLoginCount() + 1);
        }
        $this->entityManager->flush();

        $isTwoAuthConfig = $this->systemConfigurationRepository->findOneBy(['code' => 'TWOAUTH']);
        $isSMSTransportConfig = $this->systemConfigurationRepository->findOneBy(['code' => 'ISSMS']);
        $isMaintenanceConfig = $this->systemConfigurationRepository->findOneBy(['code' => 'MAINTENANCE']);

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
                                $payload['user'] = [
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
                                    "moduleLayout" => $this->roleRepository->findOneBy(['profile' => $user->getProfile()])->getModule()->getLayout(),
                                    'currentYear' => $user->getCurrentYear() ? $user->getCurrentYear()->getYear() : '',
                                    'systemYear' => $this->yearRepository->findOneBy(['isCurrent' => true])->getYear(),
                                    'institution' => $user->getInstitution() ? $user->getInstitution()->getCode() : '',
                                    'instId' => $user->getInstitution() ? $user->getInstitution()->getId() : '',
                                    "twoAuthMode" => $isTwoAuthConfig ? $isTwoAuthConfig->isValue() : '',
                                    "twoAuthTransport" => $isSMSTransportConfig ? $isSMSTransportConfig->isValue() : '',
                                    "maintenance" => $isMaintenanceConfig ? $isMaintenanceConfig->isValue() : '',
                                    'loginCount' => $user->getLoginCount()
                                ];
                            }
                            else{
                                // Add information to user payload
                                $payload['user'] = [
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
                                    'loginCount' => $user->getLoginCount()
                                    //"modules" => $selectedRoles
                                ];
                            }

                            $event->setData($payload);

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
                    $payload['user'] = [
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
                        "moduleLayout" => $this->roleRepository->findOneBy(['profile' => $user->getProfile()])->getModule()->getLayout(),
                        'currentYear' => $user->getCurrentYear() ? $user->getCurrentYear()->getYear() : '',
                        'systemYear' => $this->yearRepository->findOneBy(['isCurrent' => true])->getYear(),
                        'institution' => $user->getInstitution() ? $user->getInstitution()->getCode() : '',
                        'instId' => $user->getInstitution() ? $user->getInstitution()->getId() : '',
                        "twoAuthMode" => $isTwoAuthConfig ? $isTwoAuthConfig->isValue() : '',
                        "twoAuthTransport" => $isSMSTransportConfig ? $isSMSTransportConfig->isValue() : '',
                        "maintenance" => $isMaintenanceConfig ? $isMaintenanceConfig->isValue() : '',
                        'loginCount' => $user->getLoginCount()

                    ];
                }
                else{
                    // Add information to user payload
                    $payload['user'] = [
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
                        'loginCount' => $user->getLoginCount()
                        //"modules" => $selectedRoles
                    ];
                }

                $event->setData($payload);

            }
        }
        else{
            // If auth config is not configured
            // return new JsonResponse(['hydra:title' => 'The mode is not configure'],Response::HTTP_NOT_FOUND);

            if (($user->getProfile() ? $user->getProfile()->isIsTeacherSystem(): '') || ($user->getProfile() ? $user->getProfile()->isIsStudentSystem() : '')){
                $payload['user'] = [
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
                    "moduleLayout" => $this->roleRepository->findOneBy(['profile' => $user->getProfile()])->getModule()->getLayout(),
                    'currentYear' => $user->getCurrentYear() ? $user->getCurrentYear()->getYear() : '',
                    'systemYear' => $this->yearRepository->findOneBy(['isCurrent' => true])->getYear(),
                    'institution' => $user->getInstitution() ? $user->getInstitution()->getCode() : '',
                    'instId' => $user->getInstitution() ? $user->getInstitution()->getId() : '',
                    "twoAuthMode" => $isTwoAuthConfig ? $isTwoAuthConfig->isValue() : '',
                    "twoAuthTransport" => $isSMSTransportConfig ? $isSMSTransportConfig->isValue() : '',
                    "maintenance" => $isMaintenanceConfig ? $isMaintenanceConfig->isValue() : '',
                    'loginCount' => $user->getLoginCount()

                ];
            }
            else{
                // Add information to user payload
                $payload['user'] = [
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
                    'loginCount' => $user->getLoginCount()

                    //"modules" => $selectedRoles
                ];
            }

            $event->setData($payload);

        }

        return new JsonResponse(['user' => $payload], Response::HTTP_OK);
    }

    public function getDashboard($user): void
    {
        if ($user->getProfile()->isIsStudentSystem() || $user->getProfile()->isIsStudentSystem()){
            [
                $this->roleRepository->findOneBy(['profile' => $user->getProfile()])->getModule()
            ];
        }
    }
}
