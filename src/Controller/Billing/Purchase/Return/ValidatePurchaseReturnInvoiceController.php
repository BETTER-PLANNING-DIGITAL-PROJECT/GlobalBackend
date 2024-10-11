<?php

namespace App\Controller\Billing\Purchase\Return;

use App\Entity\Billing\Purchase\PurchaseReturnInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemStockRepository;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceRepository;
use App\Repository\Inventory\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class ValidatePurchaseReturnInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(PurchaseReturnInvoiceItemRepository $purchaseReturnInvoiceItemRepository,
                             PurchaseReturnInvoiceRepository $purchaseReturnInvoiceRepository,
                             PurchaseReturnInvoiceItemStockRepository $purchaseReturnInvoiceItemStockRepository,
                             StockRepository $stockRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $purchaseReturnInvoice = $purchaseReturnInvoiceRepository->find($id);
        if(!$purchaseReturnInvoice instanceof PurchaseReturnInvoice)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of return invoice.'], 404);
        }

        if(!$purchaseReturnInvoice->getSupplier())
        {
            return new JsonResponse(['hydra:description' => 'Choose a supplier and save before validate.'], 404);
        }

        $purchaseReturnInvoiceItem = $purchaseReturnInvoiceItemRepository->findOneBy(['purchaseReturnInvoice' => $purchaseReturnInvoice]);
        if(!$purchaseReturnInvoiceItem)
        {
            return new JsonResponse(['hydra:description' => 'Can not validate with empty cart.'], 404);
        }

        $purchaseReturnInvoice->setStatus('return invoice');

        // quantity not need to be reserved

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