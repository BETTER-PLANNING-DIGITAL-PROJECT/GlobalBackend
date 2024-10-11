<?php

namespace App\Controller\Inventory\Reception;

use App\Entity\Billing\Sale\SaleInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Inventory\ReceptionItemRepository;
use App\Repository\Inventory\ReceptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetReceptionItemController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                )
    {
    }

    public function __invoke(ReceptionItemRepository $receptionItemRepository,
                             ReceptionRepository $receptionRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $reception = $receptionRepository->find($id);
        if (!$reception){

            return new JsonResponse(['hydra:description' => 'This reception is not found.'], 404);
        }

        $receptionItems = $receptionItemRepository->findBy(['reception' => $reception]);

        $items = [];

        foreach ($receptionItems as $receptionItem){
            $items[] = [
                'id' => $receptionItem->getId(),
                'reception' => [
                    'id' => $receptionItem->getReception() ? $receptionItem->getReception()->getId() : '',
                    '@id' => '/api/get/receptions/'. $receptionItem->getReception()->getId(),
                    'reference' => $receptionItem->getReception() ? $receptionItem->getReception()->getReference() : '',
                ],
                'item' => [
                    'id' => $receptionItem->getItem()->getId(),
                    '@id' => '/api/get/items/'. $receptionItem->getItem()->getId(),
                    'name' => $receptionItem->getItem() ? $receptionItem->getItem()->getName() : '',
                    'reference' => $receptionItem->getItem() ? $receptionItem->getItem()->getReference() : '',
                    'barcode' => $receptionItem->getItem() ? $receptionItem->getItem()->getBarcode() : '',
                    'price' => $receptionItem->getItem() ? $receptionItem->getItem()->getPrice() : '',
                    'salePrice' => $receptionItem->getItem() ? $receptionItem->getItem()->getSalePrice() : '',
                    'cost' => $receptionItem->getItem() ? $receptionItem->getItem()->getCost() : '',
                ],
                'quantity' => $receptionItem->getQuantity(),

            ];
        }

        return $this->json(['hydra:member' => $items]);
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
