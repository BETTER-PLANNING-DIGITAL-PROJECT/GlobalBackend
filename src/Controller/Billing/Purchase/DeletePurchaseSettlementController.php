<?php

namespace App\Controller\Billing\Purchase;

use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Billing\Purchase\PurchaseSettlementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class DeletePurchaseSettlementController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                             PurchaseInvoiceRepository $purchaseInvoiceRepository,
                             PurchaseSettlementRepository $purchaseSettlementRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $purchaseSettlement = $purchaseSettlementRepository->find($id);
        if (!$purchaseSettlement){
            return new JsonResponse(['hydra:description' => 'This purchase settlement is not found.'], 404);
        }

        if ($purchaseSettlement->isIsValidate()){
            return new JsonResponse(['hydra:description' => 'Can not delete a validated purchase settlement.'], 404);
        }

        if ($purchaseSettlement->getInvoice()){
            $purchaseSettlement->getInvoice()?->setBalance($purchaseSettlement->getInvoice()->getBalance() + $purchaseSettlement->getAmountPay());
            $purchaseSettlement->getInvoice()?->setAmountPaid($purchaseSettlement->getInvoice()->getAmountPaid() - $purchaseSettlement->getAmountPay());
            $purchaseSettlement->getInvoice()?->setVirtualBalance($purchaseSettlement->getInvoice()->getVirtualBalance() + $purchaseSettlement->getAmountPay());
        }

        if ($purchaseSettlement->getPurchaseReturnInvoice()){
            $purchaseSettlement->getPurchaseReturnInvoice()?->setBalance($purchaseSettlement->getPurchaseReturnInvoice()->getBalance() + $purchaseSettlement->getAmountPay());
            $purchaseSettlement->getPurchaseReturnInvoice()?->setAmountPaid($purchaseSettlement->getPurchaseReturnInvoice()->getAmountPaid() - $purchaseSettlement->getAmountPay());
            $purchaseSettlement->getPurchaseReturnInvoice()?->setVirtualBalance($purchaseSettlement->getPurchaseReturnInvoice()->getVirtualBalance() + $purchaseSettlement->getAmountPay());
        }

        $entityManager->remove($purchaseSettlement);
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
