<?php

namespace App\Controller\School\Schooling\Configuration;


use App\Entity\School\Schooling\Configuration\Fee;
use App\Entity\School\Schooling\Configuration\FeeInstallment;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\FeeInstallmentRepository;
use App\Repository\School\Schooling\Configuration\FeeRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\Security\Session\YearRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class FeeInstallmentReportController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager, SchoolRepository $schoolRepository, YearRepository $schoolYearRepository,
                                SchoolClassRepository $schoolClassRepository,
                                private readonly TokenStorageInterface $tokenStorage, FeeInstallmentRepository $feeInstallmentRepository)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->schoolRepository = $schoolRepository;
        $this->schoolYearRepository = $schoolYearRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->feeInstallmentRepository = $feeInstallmentRepository;
    }

    #[Route('/api/get/fee-installment/by-year-school-class', name: 'app_get_fee_installment_by_year_school_class')]
    public function getFeeInstallmentByYearSchoolClass(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $yearId = $data['yearId'];
        $schoolId = $data['schoolId'];
        $classId = $data['classId'];

        $year = $this->schoolYearRepository->find($yearId);
        $school = $this->schoolRepository->find($schoolId);
        $class = $this->schoolClassRepository->find($classId);
        $feeInstallments = $this->feeInstallmentRepository->findByFee($year, $school, $class);

        return $this->json(array_map('self::bindFeeInstallment', $feeInstallments));
    }

    static function bindFeeInstallment(FeeInstallment $feeInstallment): array
    {
        return [
            'id' => $feeInstallment->getId(),
            'fee' => $feeInstallment->getFee() ? $feeInstallment->getFee()->getName(): '',
            'installment' => $feeInstallment->getInstallment() ? $feeInstallment->getInstallment()->getName() : '',
            'amount' => $feeInstallment->getAmount(),
            'paymentDate' => $feeInstallment->getPaymentDate() ? $feeInstallment->getPaymentDate()->format('Y-m-d') : '',
        ];
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



