<?php

namespace App\Controller;

use App\Entity\Security\User;
use App\Repository\Security\Institution\InstitutionRepository;
use App\Repository\Security\ProfileRepository;
use App\Repository\Security\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GenerateSingleCredentialController extends AbstractController
{
    private EntityManagerInterface $manager;
    public function __construct(EntityManagerInterface $manager, private readonly TokenStorageInterface $tokenStorage)
    {
        $this->manager = $manager;
    }

    public function __invoke(Request                     $request,
                             ProfileRepository           $profileRepository,
                             InstitutionRepository       $institutionRepository,
                             UserRepository $userRepository,
                             UserPasswordHasherInterface $passwordHashed): JsonResponse|Response
    {
        $objectClass = $this->container->get('request_stack')->getCurrentRequest()->attributes->get('_api_resource_class');
        $objectName = $this->container->get('request_stack')->getCurrentRequest()->attributes->get('_api_operation')->getShortName();

        $id = $request->get('id');

        $roleTeacher = $profileRepository->findOneBy(['isTeacherSystem' => true]);
        $roleStudent = $profileRepository->findOneBy(['isStudentSystem' => true]);

        $objectById = $this->manager->getRepository($objectClass)->findOneBy(['id' => $id]);

        if ($objectById->getOperator())
        {
            return new JsonResponse(['hydra:title' => $objectName.' already has credential'],Response::HTTP_NOT_FOUND, ['Content-Type', 'application/json']);
        }

        $user = new User();

        if ($objectName == 'Student')
        {
            if (!$roleStudent) {
                return new JsonResponse(['hydra:title' => ' '.$objectName.' profile not found.'],Response::HTTP_BAD_REQUEST);
            }

            $user->setProfile($roleStudent);
        }

        if ($objectName == 'Teacher')
        {
            if (!$roleTeacher) {
                return new JsonResponse(['hydra:title' => ' '.$objectName.' profile not found.'],Response::HTTP_BAD_REQUEST);
            }

            $user->setProfile($roleTeacher);
        }

        $user->setPhone($objectName == 'Teacher' ? $objectById->getPhone() : $objectById->getStudentphone());
        $email = $objectName == 'Teacher' ? $objectById->getEmail() : $objectById->getStudentemail();
        $matricule = $objectName == 'Teacher' ? $objectById->getRegistrationNumber() : $objectById->getMatricule();
        $user->setUsername($matricule);
        $user->setFirstname($objectById->getFirstName());
        $user->setLastname($objectById->getName());
        $user->setEmail($email);

        $user->setInstitution($this->getUser()->getInstitution());
        $user->setBranch($this->getUser()->getBranch());
        $hashedPassword = $passwordHashed->hashPassword(
            $user,
            'user'
        );
        $user->setPassword($hashedPassword);

        $user->setIsLock(false);
        $user->setCreatedAt(new \DateTime());
        $user->setIsEnable(true);

        // Consider there is not picture for assign user
        $user->setPicture('7.jpg');
        $user->setFileName('7.jpg');
        $user->setFileType('image/jpg');
        $user->setFileSize(null);

        $this->manager->persist($user);

        $objectById->setOperator($user);

        $this->manager->flush();

        return new Response(null, Response::HTTP_OK);
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
