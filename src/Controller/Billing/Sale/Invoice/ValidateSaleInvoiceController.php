<?php

namespace App\Controller\Billing\Sale\Invoice;

use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Inventory\StockRepository;
use App\Repository\Partner\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class ValidateSaleInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             SaleInvoiceItemStockRepository $saleInvoiceItemStockRepository,
                             StockRepository $stockRepository,
                             CustomerRepository $customerRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {
        $id = $request->get('id');

        $saleInvoice = $saleInvoiceRepository->find($id);
        if(!$saleInvoice instanceof SaleInvoice)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of sale invoice.'], 404);
        }

        if(!$saleInvoice->getCustomer())
        {
            return new JsonResponse(['hydra:description' => 'Choose a customer and save before validate.'], 404);
        }

        $saleInvoiceItem = $saleInvoiceItemRepository->findOneBy(['saleInvoice' => $saleInvoice]);
        if(!$saleInvoiceItem)
        {
            return new JsonResponse(['hydra:description' => 'Can not validate with empty cart.'], 404);
        }

        $saleInvoice->setStatus('invoice');

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