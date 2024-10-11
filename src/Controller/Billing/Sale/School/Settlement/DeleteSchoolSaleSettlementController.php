<?php

namespace App\Controller\Billing\Sale\School\Settlement;

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
class DeleteSchoolSaleSettlementController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             SaleSettlementRepository $saleSettlementRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $settlement = $saleSettlementRepository->find($id);
        if (!$settlement){
            return new JsonResponse(['hydra:description' => 'This settlement is not found.'], 404);
        }

        if ($settlement->getInvoice()){
            $settlement->getInvoice()?->setBalance($settlement->getInvoice()->getBalance() + $settlement->getAmountPay());
            $settlement->getInvoice()?->setAmountPaid($settlement->getInvoice()->getAmountPaid() - $settlement->getAmountPay());
            $settlement->getInvoice()?->setVirtualBalance($settlement->getInvoice()->getVirtualBalance() + $settlement->getAmountPay());
        }

        if ($settlement->getSaleReturnInvoice()){
            $settlement->getSaleReturnInvoice()?->setBalance($settlement->getSaleReturnInvoice()->getBalance() + $settlement->getAmountPay());
            $settlement->getSaleReturnInvoice()?->setAmountPaid($settlement->getSaleReturnInvoice()->getAmountPaid() - $settlement->getAmountPay());
            $settlement->getSaleReturnInvoice()?->setVirtualBalance($settlement->getSaleReturnInvoice()->getVirtualBalance() + $settlement->getAmountPay());
        }

        $entityManager->remove($settlement);
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
