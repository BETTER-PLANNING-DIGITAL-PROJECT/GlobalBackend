<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\FeeRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetFeeController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(Request $request, FeeRepository $feeRepository, SystemSettingsRepository $systemSettingsRepository, SchoolRepository $schoolRepository):JsonResponse
    {
        $requestData = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $fees = $feeRepository->findBy([], ['id' => 'DESC']);

            foreach ($fees as $fee){

                $requestData [] = [
                    '@id' => "/api/fee/".$fee->getId(),
                    '@type' => "Fee",
                    'id' => $fee->getId(),
                    'costArea' => $fee->getCostArea() ? [
                        '@id' => "/api/cost-area/".$fee->getCostArea()->getId(),
                        '@type' => "CostArea",
                        'id' => $fee->getCostArea()->getId(),
                        'code' => $fee->getCostArea()->getCode(),
                        'name' => $fee->getCostArea()->getName(),
                    ] : '',
                    'class' => $fee->getClass() ? [
                        '@id' => "/api/class/".$fee->getClass()->getId(),
                        '@type' => "Class",
                        'id' => $fee->getClass()->getId(),
                        'code' => $fee->getClass()->getCode(),
                        'description' => $fee->getClass()->getDescription(),
                    ] : '',
                    'pensionScheme' => $fee->getPensionScheme() ? [
                        '@id' => "/api/pension-scheme/".$fee->getPensionScheme()->getId(),
                        '@type' => "PensionScheme",
                        'id' => $fee->getPensionScheme()->getId(),
                        'code' => $fee->getPensionScheme()->getName(),
                    ] : '',
                    'school' => $fee->getSchool() ? [
                        '@id' => "/api/schools/".$fee->getSchool()->getId(),
                        '@type' => "School",
                        'id' => $fee->getSchool()->getId(),
                        'code' => $fee->getSchool()->getCode(),
                        'name' => $fee->getSchool()->getName(),
                        'email' => $fee->getSchool()->getEmail(),
                        'phone' => $fee->getSchool()->getPhone(),
                        'postalCode' => $fee->getSchool()->getPostalCode(),
                        'city' => $fee->getSchool()->getCity(),
                        'address' => $fee->getSchool()->getAddress(),
                        'manager' => $fee->getSchool()->getManager(),
                        'managerType' => $fee->getSchool()->getManagerType() ? [
                            '@id' => "/api/manager_types/".$fee->getSchool()->getManagerType()->getId(),
                            '@type' => "ManagerType",
                            'id' => $fee->getSchool()->getManagerType()->getId(),
                            'code' => $fee->getSchool()->getManagerType()->getCode(),
                            'name' => $fee->getSchool()->getManagerType()->getName(),
                        ] : '',
                    ] : '',
                    'cycle' => $fee->getCycle() ? [
                        '@id' => "/api/cycle/".$fee->getCycle()->getId(),
                        '@type' => "Cycle",
                        'id' => $fee->getCycle()->getId(),
                        'code' => $fee->getCycle()->getName(),
                    ] : '',
                    'speciality' => $fee->getSpeciality() ? [
                        '@id' => "/api/speciality/".$fee->getSpeciality()->getId(),
                        '@type' => "Speciality",
                        'id' => $fee->getSpeciality()->getId(),
                        'code' => $fee->getSpeciality()->getName(),
                    ] : '',
                    'level' => $fee->getLevel() ? [
                        '@id' => "/api/level/".$fee->getLevel()->getId(),
                        '@type' => "Level",
                        'id' => $fee->getLevel()->getId(),
                        'code' => $fee->getLevel()->getName(),
                    ] : '',
                    'trainingType' => $fee->getTrainingType() ? [
                        '@id' => "/api/training-type/".$fee->getTrainingType()->getId(),
                        '@type' => "TrainingType",
                        'id' => $fee->getTrainingType()->getId(),
                        'code' => $fee->getTrainingType()->getName(),
                    ] : '',
                    'budgetLine' => $fee->getBudgetLine() ? [
                        '@id' => "/api/budget-line/".$fee->getBudgetLine()->getId(),
                        '@type' => "BudgetLine",
                        'id' => $fee->getBudgetLine()->getId(),
                        'code' => $fee->getBudgetLine()->getName(),
                    ] : '',
                    'code' => $fee->getCode(),
                    'name' => $fee->getName(),
                    'amount' => $fee->getAmount(),
                    'paymentDate' => $fee->getPaymentDate(),
                ];
            }
        }
        else
        {
            $systemSettings = $systemSettingsRepository->findOneBy([]);
            if($systemSettings)
            {
                if($systemSettings->isIsBranches())
                {
                    $userBranches = $this->getUser()->getUserBranches();
                    foreach ($userBranches as $userBranch) {
                        $school = $schoolRepository->findOneBy(['schoolBranch' => $userBranch]);
                        if ($school) {
                            $fees = $feeRepository->findBy(['school' => $school], ['id' => 'DESC']);

                            foreach ($fees as $fee){

                                $requestData [] = [
                                    '@id' => "/api/fee/".$fee->getId(),
                                    '@type' => "Fee",
                                    'id' => $fee->getId(),
                                    'costArea' => $fee->getCostArea() ? [
                                        '@id' => "/api/cost-area/".$fee->getCostArea()->getId(),
                                        '@type' => "CostArea",
                                        'id' => $fee->getCostArea()->getId(),
                                        'code' => $fee->getCostArea()->getCode(),
                                        'name' => $fee->getCostArea()->getName(),
                                    ] : '',
                                    'class' => $fee->getClass() ? [
                                        '@id' => "/api/class/".$fee->getClass()->getId(),
                                        '@type' => "Class",
                                        'id' => $fee->getClass()->getId(),
                                        'code' => $fee->getClass()->getCode(),
                                        'description' => $fee->getClass()->getDescription(),
                                    ] : '',
                                    'pensionScheme' => $fee->getPensionScheme() ? [
                                        '@id' => "/api/pension-scheme/".$fee->getPensionScheme()->getId(),
                                        '@type' => "PensionScheme",
                                        'id' => $fee->getPensionScheme()->getId(),
                                        'code' => $fee->getPensionScheme()->getName(),
                                    ] : '',
                                    'school' => $fee->getSchool() ? [
                                        '@id' => "/api/schools/".$fee->getSchool()->getId(),
                                        '@type' => "School",
                                        'id' => $fee->getSchool()->getId(),
                                        'code' => $fee->getSchool()->getCode(),
                                        'name' => $fee->getSchool()->getName(),
                                        'email' => $fee->getSchool()->getEmail(),
                                        'phone' => $fee->getSchool()->getPhone(),
                                        'postalCode' => $fee->getSchool()->getPostalCode(),
                                        'city' => $fee->getSchool()->getCity(),
                                        'address' => $fee->getSchool()->getAddress(),
                                        'manager' => $fee->getSchool()->getManager(),
                                        'managerType' => $fee->getSchool()->getManagerType() ? [
                                            '@id' => "/api/manager_types/".$fee->getSchool()->getManagerType()->getId(),
                                            '@type' => "ManagerType",
                                            'id' => $fee->getSchool()->getManagerType()->getId(),
                                            'code' => $fee->getSchool()->getManagerType()->getCode(),
                                            'name' => $fee->getSchool()->getManagerType()->getName(),
                                        ] : '',
                                    ] : '',
                                    'cycle' => $fee->getCycle() ? [
                                        '@id' => "/api/cycle/".$fee->getCycle()->getId(),
                                        '@type' => "Cycle",
                                        'id' => $fee->getCycle()->getId(),
                                        'code' => $fee->getCycle()->getName(),
                                    ] : '',
                                    'speciality' => $fee->getSpeciality() ? [
                                        '@id' => "/api/speciality/".$fee->getSpeciality()->getId(),
                                        '@type' => "Speciality",
                                        'id' => $fee->getSpeciality()->getId(),
                                        'code' => $fee->getSpeciality()->getName(),
                                    ] : '',
                                    'level' => $fee->getLevel() ? [
                                        '@id' => "/api/level/".$fee->getLevel()->getId(),
                                        '@type' => "Level",
                                        'id' => $fee->getLevel()->getId(),
                                        'code' => $fee->getLevel()->getName(),
                                    ] : '',
                                    'trainingType' => $fee->getTrainingType() ? [
                                        '@id' => "/api/training-type/".$fee->getTrainingType()->getId(),
                                        '@type' => "TrainingType",
                                        'id' => $fee->getTrainingType()->getId(),
                                        'code' => $fee->getTrainingType()->getName(),
                                    ] : '',
                                    'budgetLine' => $fee->getBudgetLine() ? [
                                        '@id' => "/api/budget-line/".$fee->getBudgetLine()->getId(),
                                        '@type' => "BudgetLine",
                                        'id' => $fee->getBudgetLine()->getId(),
                                        'code' => $fee->getBudgetLine()->getName(),
                                    ] : '',
                                    'code' => $fee->getCode(),
                                    'name' => $fee->getName(),
                                    'amount' => $fee->getAmount(),
                                    'paymentDate' => $fee->getPaymentDate(),
                                ];
                            }
                        }
                    }
                }
                else {
                    $school = $schoolRepository->findOneBy(['branch' => $this->getUser()->getBranch()]);
                    if($school) {
                        $fees = $feeRepository->findBy(['school' => $school], ['id' => 'DESC']);

                        foreach ($fees as $fee) {
                            if ($fee) {
                                $requestData [] = [
                                    '@id' => "/api/fee/".$fee->getId(),
                                    '@type' => "Fee",
                                    'id' => $fee->getId(),
                                    'costArea' => $fee->getCostArea() ? [
                                        '@id' => "/api/cost-area/".$fee->getCostArea()->getId(),
                                        '@type' => "CostArea",
                                        'id' => $fee->getCostArea()->getId(),
                                        'code' => $fee->getCostArea()->getCode(),
                                        'name' => $fee->getCostArea()->getName(),
                                    ] : '',
                                    'class' => $fee->getClass() ? [
                                        '@id' => "/api/class/".$fee->getClass()->getId(),
                                        '@type' => "Class",
                                        'id' => $fee->getClass()->getId(),
                                        'code' => $fee->getClass()->getCode(),
                                        'description' => $fee->getClass()->getDescription(),
                                    ] : '',
                                    'pensionScheme' => $fee->getPensionScheme() ? [
                                        '@id' => "/api/pension-scheme/".$fee->getPensionScheme()->getId(),
                                        '@type' => "PensionScheme",
                                        'id' => $fee->getPensionScheme()->getId(),
                                        'code' => $fee->getPensionScheme()->getName(),
                                    ] : '',
                                    'school' => $fee->getSchool() ? [
                                        '@id' => "/api/schools/".$fee->getSchool()->getId(),
                                        '@type' => "School",
                                        'id' => $fee->getSchool()->getId(),
                                        'code' => $fee->getSchool()->getCode(),
                                        'name' => $fee->getSchool()->getName(),
                                        'email' => $fee->getSchool()->getEmail(),
                                        'phone' => $fee->getSchool()->getPhone(),
                                        'postalCode' => $fee->getSchool()->getPostalCode(),
                                        'city' => $fee->getSchool()->getCity(),
                                        'address' => $fee->getSchool()->getAddress(),
                                        'manager' => $fee->getSchool()->getManager(),
                                        'managerType' => $fee->getSchool()->getManagerType() ? [
                                            '@id' => "/api/manager_types/".$fee->getSchool()->getManagerType()->getId(),
                                            '@type' => "ManagerType",
                                            'id' => $fee->getSchool()->getManagerType()->getId(),
                                            'code' => $fee->getSchool()->getManagerType()->getCode(),
                                            'name' => $fee->getSchool()->getManagerType()->getName(),
                                        ] : '',
                                    ] : '',
                                    'cycle' => $fee->getCycle() ? [
                                        '@id' => "/api/cycle/".$fee->getCycle()->getId(),
                                        '@type' => "Cycle",
                                        'id' => $fee->getCycle()->getId(),
                                        'code' => $fee->getCycle()->getName(),
                                    ] : '',
                                    'speciality' => $fee->getSpeciality() ? [
                                        '@id' => "/api/speciality/".$fee->getSpeciality()->getId(),
                                        '@type' => "Speciality",
                                        'id' => $fee->getSpeciality()->getId(),
                                        'code' => $fee->getSpeciality()->getName(),
                                    ] : '',
                                    'level' => $fee->getLevel() ? [
                                        '@id' => "/api/level/".$fee->getLevel()->getId(),
                                        '@type' => "Level",
                                        'id' => $fee->getLevel()->getId(),
                                        'code' => $fee->getLevel()->getName(),
                                    ] : '',
                                    'trainingType' => $fee->getTrainingType() ? [
                                        '@id' => "/api/training-type/".$fee->getTrainingType()->getId(),
                                        '@type' => "TrainingType",
                                        'id' => $fee->getTrainingType()->getId(),
                                        'code' => $fee->getTrainingType()->getName(),
                                    ] : '',
                                    'budgetLine' => $fee->getBudgetLine() ? [
                                        '@id' => "/api/budget-line/".$fee->getBudgetLine()->getId(),
                                        '@type' => "BudgetLine",
                                        'id' => $fee->getBudgetLine()->getId(),
                                        'code' => $fee->getBudgetLine()->getName(),
                                    ] : '',
                                    'code' => $fee->getCode(),
                                    'name' => $fee->getName(),
                                    'amount' => $fee->getAmount(),
                                    'paymentDate' => $fee->getPaymentDate(),
                                ];
                            }
                        }
                    }

                }
            }
        }


        return $this->json(['hydra:member' => $requestData]);
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
