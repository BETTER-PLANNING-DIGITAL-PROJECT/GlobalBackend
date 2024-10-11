<?php

namespace App\Controller\Billing\Sale;

use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetSaleInvoiceFeeGatewayPaymentController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $invoice = $saleInvoiceRepository->find($id);
        if (!$invoice){
            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }

        $invoiceItems = $saleInvoiceItemRepository->findBy(['saleInvoice' => $invoice]);

        $gateways = [];
        foreach ($invoiceItems as $invoiceItem){
            if ($invoiceItem->getItem()){

                $paymentGateways = $invoiceItem->getItem()->getFee()?->getPaymentGateways();
                if ($paymentGateways){
                    foreach ($paymentGateways as $paymentGateway){
                        $gateways[] = [
                            '@id' => '/api/get/payment/gateway/'.$paymentGateway->getId(),
                            '@type' => 'PaymentGateway',
                            'id' => $paymentGateway->getId(),
                            'code' => $paymentGateway->getCode(),
                            'name' => $paymentGateway->getName(),
                            'key1' => $paymentGateway->getKey1(),
                            'key2' => $paymentGateway->getKey2(),
                            'key3' => $paymentGateway->getKey3(),
                            'better_play_code' => $paymentGateway->getBetterPayCode(),
                            'status' => $paymentGateway->getStatus()
                        ];
                    }
                }



            }
        }

        return $this->json(['hydra:member' => $gateways]);
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
