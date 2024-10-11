<?php

namespace App\Controller\Billing\Sale\Return;

use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemStockRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use App\Repository\Inventory\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class ValidateSaleReturnInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(SaleReturnInvoiceItemRepository $saleReturnInvoiceItemRepository,
                             SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                             SaleReturnInvoiceItemStockRepository $saleReturnInvoiceItemStockRepository,
                             StockRepository $stockRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $saleReturnInvoice = $saleReturnInvoiceRepository->find($id);
        if(!$saleReturnInvoice instanceof SaleReturnInvoice)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of return invoice.'], 404);
        }

        if(!$saleReturnInvoice->getCustomer())
        {
            return new JsonResponse(['hydra:description' => 'Choose a customer and save before validate.'], 404);
        }

        $saleReturnInvoiceItem = $saleReturnInvoiceItemRepository->findOneBy(['saleReturnInvoice' => $saleReturnInvoice]);
        if(!$saleReturnInvoiceItem)
        {
            return new JsonResponse(['hydra:description' => 'Can not validate with empty cart.'], 404);
        }

        $saleReturnInvoice->setStatus('return invoice');

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
