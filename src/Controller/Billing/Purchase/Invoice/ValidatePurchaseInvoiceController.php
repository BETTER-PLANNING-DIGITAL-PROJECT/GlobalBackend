<?php

namespace App\Controller\Billing\Purchase\Invoice;

use App\Entity\Billing\Purchase\PurchaseInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Inventory\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class ValidatePurchaseInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                             PurchaseInvoiceRepository $purchaseInvoiceRepository,
                             EntityManagerInterface $entityManager,
                             StockRepository $stockRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $purchaseInvoice = $purchaseInvoiceRepository->find($id);
        if(!$purchaseInvoice instanceof PurchaseInvoice)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of purchase invoice.'], 404);
        }

        if(!$purchaseInvoice->getSupplier())
        {
            return new JsonResponse(['hydra:description' => 'Choose a supplier and save before validate.'], 404);
        }

        $purchaseInvoiceItem = $purchaseInvoiceItemRepository->findOneBy(['purchaseInvoice' => $purchaseInvoice]);
        if(!$purchaseInvoiceItem)
        {
            return new JsonResponse(['hydra:description' => 'Can not validate with empty cart.'], 404);
        }

        $purchaseInvoice->setStatus('invoice');

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
