<?php

namespace App\Controller\Billing\Sale\School\Settlement;

use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSchoolSaleSettlementController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly SaleSettlementRepository $saleSettlementRepository,
    )
    {
    }

    public function __invoke(Request $request): JsonResponse
    {

        $saleSettlements = $this->saleSettlementRepository->getSettlementByStudentRegistration();

        $mySettlements = [];

        foreach ($saleSettlements as $saleSettlement){
            $mySettlements[] = [
                'id' => $saleSettlement->getId(),
                '@id' => '/api/get/sale-settlement/'.$saleSettlement->getId(),
                'type' => 'SaleInvoice',
                'invoice' => $saleSettlement->getInvoice(),
                'saleReturnInvoice' => $saleSettlement->getSaleReturnInvoice(),
                'studentRegistration' => $saleSettlement->getStudentRegistration(),
                'bank' =>$saleSettlement->getBank(),
                'bankAccount' =>$saleSettlement->getBankAccount(),
                'cashDesk' =>$saleSettlement->getCashDesk(),
                'paymentMethod' =>$saleSettlement->getPaymentMethod(),
                'reference' =>$saleSettlement->getReference(),
                'amountPay' =>$saleSettlement->getAmountPay(),
                'settleAt' => $saleSettlement->getSettleAt() ? $saleSettlement->getSettleAt()->format('Y-m-d') : '',
                'note' => $saleSettlement->getNote(),
                'isValidate' => $saleSettlement->isIsValidate(),
                'paymentGateway' => $saleSettlement->getPaymentGateway(),
            ];
        }

        return $this->json(['hydra:member' => $mySettlements]);
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
