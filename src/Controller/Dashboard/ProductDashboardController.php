<?php

namespace App\Controller\Dashboard;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\FeeRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class ProductDashboardController extends AbstractController
{
    private EntityManagerInterface $manager;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                Request                                $req, EntityManagerInterface $manager,
                                FeeRepository    $feeRepository,)
    {
        $this->manager = $manager;
        $this->feeRepository = $feeRepository;
    }


    #[Route('/api/get/product/dashboard/number-of-fees', name: 'app_get_product_dashboard_number_of_fees')]
    public function NumberOfFees(): JsonResponse
    {

        $feeCount= $this->feeRepository->countFees();
        return new JsonResponse(['hydra:description' => $feeCount]);

    }

    #[Route('/api/get/product/dashboard/recent-fees', name: 'app_get_product_dashboard_recent_fees')]
    public function RecentFees(): JsonResponse
    {
        $fees = $this->feeRepository->findAll();

        $recentFees = [];
        $count = 0;

        foreach ($fees as $fee) {
            $recentFees[] = [
                'id' => $fee->getId(),
                'code' => $fee->getCode(),
                'name' => $fee->getName(),
                'amount' => $fee->getAmount(),
                'school' => $fee->getSchool() ? $fee->getSchool()->getName() : '',
                'speciality' => $fee->getSpeciality() ? $fee->getSpeciality()->getName() : '-',
                'level' => $fee->getLevel() ? $fee->getLevel()->getName() : '-',
                'trainingType' => $fee->getTrainingType() ? $fee->getTrainingType()->getName() : '-',
            ];
            $count++;

            // Stop after adding 5 cash desks
            if ($count >= 5) {
                break;
            }

        }

//        dd($recentClasses);

        return new JsonResponse(['hydra:description' => $recentFees]);

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
