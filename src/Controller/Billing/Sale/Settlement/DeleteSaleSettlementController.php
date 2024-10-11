<?php

namespace App\Controller\Billing\Sale\Settlement;

use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class DeleteSaleSettlementController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             SaleSettlementRepository $saleSettlementRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $saleSettlement = $saleSettlementRepository->find($id);
        if (!$saleSettlement){
            return new JsonResponse(['hydra:description' => 'This sale settlement is not found.'], 404);
        }

        if ($saleSettlement->isIsValidate()){
            return new JsonResponse(['hydra:description' => 'Can not delete a validated sale settlement.'], 404);
        }

        if ($saleSettlement->getInvoice()){
            $saleSettlement->getInvoice()?->setBalance($saleSettlement->getInvoice()->getBalance() + $saleSettlement->getAmountPay());
            $saleSettlement->getInvoice()?->setAmountPaid($saleSettlement->getInvoice()->getAmountPaid() - $saleSettlement->getAmountPay());
            $saleSettlement->getInvoice()?->setVirtualBalance($saleSettlement->getInvoice()->getVirtualBalance() + $saleSettlement->getAmountPay());
        }

        if ($saleSettlement->getSaleReturnInvoice()){
            $saleSettlement->getSaleReturnInvoice()?->setBalance($saleSettlement->getSaleReturnInvoice()->getBalance() + $saleSettlement->getAmountPay());
            $saleSettlement->getSaleReturnInvoice()?->setAmountPaid($saleSettlement->getSaleReturnInvoice()->getAmountPaid() - $saleSettlement->getAmountPay());
            $saleSettlement->getSaleReturnInvoice()?->setVirtualBalance($saleSettlement->getSaleReturnInvoice()->getVirtualBalance() + $saleSettlement->getAmountPay());
        }

        $entityManager->remove($saleSettlement);
        $entityManager->flush();

        return $this->json(['hydra:member' => '200']);
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