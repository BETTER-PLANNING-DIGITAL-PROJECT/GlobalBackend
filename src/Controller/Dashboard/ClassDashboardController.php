<?php

namespace App\Controller\Dashboard;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class ClassDashboardController extends AbstractController
{
    private EntityManagerInterface $manager;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                Request                                $req, EntityManagerInterface $manager,
                                SchoolClassRepository    $schoolClassRepository,)
    {
        $this->manager = $manager;
        $this->schoolClassRepository = $schoolClassRepository;
    }


    #[Route('/api/get/class/dashboard/number-of-classes', name: 'app_get_class_dashboard_number_of_classes')]
    public function NumberOfClasses(): JsonResponse
    {

        $classCount= $this->schoolClassRepository->countClasses();
        return new JsonResponse(['hydra:description' => $classCount]);

    }

    #[Route('/api/get/class/dashboard/recent-classes', name: 'app_get_class_dashboard_recent_classes')]
    public function RecentClasses(): JsonResponse
    {
        $classes = $this->schoolClassRepository->findAll();

        $recentClasses = [];
        $count = 0;

        foreach ($classes as $class) {
            $recentClasses[] = [
                'id' => $class->getId(),
                'code' => $class->getCode(),
                'description' => $class->getDescription(),
                'school' => $class->getSchool() ? $class->getSchool()->getName() : '',
                'speciality' => $class->getSpeciality() ? $class->getSpeciality()->getName() : '-',
                'level' => $class->getLevel() ? $class->getLevel()->getName() : '-',
                'mainRoom' => $class->getMainRoom() ? $class->getMainRoom()->getName() : '-',
                'trainingType' => $class->getTrainingType() ? $class->getTrainingType()->getName() : '-',
            ];
            $count++;

            // Stop after adding 5 cash desks
            if ($count >= 5) {
                break;
            }

        }

//        dd($recentClasses);

        return new JsonResponse(['hydra:description' => $recentClasses]);

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
