<?php

namespace App\Controller\School\Schooling\Configuration;


use App\Entity\School\Schooling\Configuration\Fee;
use App\Entity\Security\User;
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
class FeeReportController extends AbstractController
{

    public function __construct(Request $req, EntityManagerInterface $entityManager, SchoolRepository $schoolRepository, YearRepository $schoolYearRepository,
                                SchoolClassRepository $schoolClassRepository,
                                private readonly TokenStorageInterface $tokenStorage, FeeRepository $feeRepository)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->schoolRepository = $schoolRepository;
        $this->schoolYearRepository = $schoolYearRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->feeRepository = $feeRepository;
    }

    #[Route('/api/get/fee/by-year-school-class', name: 'app_get_fee_by_year_school_class')]
    public function getFeeByYearSchoolClass(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $yearId = $data['yearId'];
        $schoolId = $data['schoolId'];
        $classId = $data['classId'];

        $year = $this->schoolYearRepository->find($yearId);
        $school = $this->schoolRepository->find($schoolId);
        $class = $this->schoolClassRepository->find($classId);
        $fees = $this->feeRepository->findBy(['year' => $year, 'school' => $school, 'class' => $class]);

        return $this->json(array_map('self::bindFee', $fees));
    }

    static function bindFee(Fee $fee): array
    {
        return [
            'id' => $fee->getId(),
            'code' => $fee->getCode(),
            'name' => $fee->getName(),
            'costArea' => $fee->getCostArea() ? $fee->getCostArea()->getName(): '',
            'pensionScheme' => $fee->getPensionScheme() ? $fee->getPensionScheme()->getName() : '',
            'school' => $fee->getSchool() ? $fee->getSchool()->getName(): '',
            'cycle' => $fee->getCycle() ? $fee->getCycle()->getName(): '',
            'speciality' => $fee->getSpeciality() ? $fee->getSpeciality()->getName(): '',
            'level' => $fee->getLevel() ? $fee->getLevel()->getName(): '',
            'trainingType' => $fee->getTrainingType() ? $fee->getTrainingType()->getName(): '',
            'budgetLine' => $fee->getBudgetLine() ? $fee->getBudgetLine()->getName(): '',
            'year' => $fee->getYear() ? $fee->getYear()->getYear() : '',
            'amount' => $fee->getAmount(),
            'class' => $fee->getClass() ? $fee->getClass()->getCode() : '',

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



