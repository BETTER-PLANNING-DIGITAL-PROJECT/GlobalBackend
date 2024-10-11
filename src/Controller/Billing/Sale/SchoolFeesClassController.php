<?php

namespace App\Controller\Billing\Sale;

use App\Repository\School\Schooling\Configuration\FeeRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class SchoolFeesClassController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(Request $request, FeeRepository $feeRepository, SchoolClassRepository $schoolClassRepository): JsonResponse
    {
        $id = $request->get('id');
        $class = $schoolClassRepository->find($id);
        if (!$class){
            return new JsonResponse(['hydra:description' => 'This class is not found.'], 404);
        }

        $fees = $feeRepository->findFeeByClass($class);

        $table = [];

        foreach ($fees as $fee){

            $table [] = [
                '@id' => "/api/fee/".$fee->getId(),
                '@type' => "Fee",
                'fee' => $fee->getId() ? [
                    '@id' => "/api/fee/".$fee->getId(),
                    '@type' => "Fee",
                    'id' => $fee->getId(),
                    'name' => $fee->getName(),
                    'code' => $fee->getCode(),
                    'amount' => $fee->getAmount(),
                    'budgetLine' => $fee->getBudgetLine(),
                    //'unity' => $fee->getUnit(),
                    //'defaultQuantity' => $fee->getDefaultQuantity()
                ] : '',
            ];
        }

        return $this->json(['hydra:member' => $table]);
    }

}
