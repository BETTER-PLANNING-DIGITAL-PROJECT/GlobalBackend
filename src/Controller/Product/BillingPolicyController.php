<?php

namespace App\Controller\Product;

use App\Repository\Product\BillingPolicyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class BillingPolicyController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(BillingPolicyRepository $billingPolicyRepository): JsonResponse
    {
        $billingPolicies = $billingPolicyRepository->findByInstitution($this->getUser()->getInstitution());

        $table = [];

        foreach ($billingPolicies as $billingPolicy){

            $table [] = [
                '@id' => "/api/billing_policies/".$billingPolicy->getId(),
                '@type' => "BillingPolicy",
                'id' => $billingPolicy->getId(),
                'code' => $billingPolicy->getCode(),
                'name' => $billingPolicy->getName(),
            ];
        }

        return $this->json(['hydra:member' => $table]);
    }

}
