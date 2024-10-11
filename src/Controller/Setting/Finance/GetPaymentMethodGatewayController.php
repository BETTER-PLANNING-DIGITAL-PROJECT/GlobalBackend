<?php

namespace App\Controller\Setting\Finance;

use App\Entity\Security\User;
use App\Repository\Setting\Finance\PaymentGatewayRepository;
use App\Repository\Setting\Finance\PaymentMethodRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetPaymentMethodGatewayController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                )
    {
    }

    public function __invoke(PaymentGatewayRepository $paymentGatewayRepository,
                             PaymentMethodRepository $paymentMethodRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $paymentMethod = $paymentMethodRepository->find($id);

        if (!$paymentMethod){
            return new JsonResponse(['hydra:description' => 'This payment method is not found.'], 404);
        }

        $items = [];

        $paymentGateways = $paymentGatewayRepository->findBy(['paymentMethod' => $paymentMethod]);
        foreach ($paymentGateways as $paymentGateway){
            $items[] = [
                'id' => $paymentGateway->getId(),
                '@id' => '/api/get/payment/gateway/'. $paymentGateway->getId(),
                'code' => $paymentGateway->getCode(),
                'name' => $paymentGateway->getName(),
                'username' => $paymentGateway->getUsername(),
                'password' => $paymentGateway->getPassword(),
                'payTypeCode' => $paymentGateway->getPayTypeCode(),
                'accountNumber' => $paymentGateway->getAccountNumber(),
                'paymentMethod' => [
                    'id' => $paymentGateway->getPaymentMethod() ? $paymentGateway->getPaymentMethod()->getId() : '',
                    '@id' => '/api/get/payment/method/'. $paymentGateway->getPaymentMethod()->getId(),
                    'type' => 'PaymentMethod',
                    'code' => $paymentGateway->getPaymentMethod() ? $paymentGateway->getPaymentMethod()->getCode() : '',
                    'name' => $paymentGateway->getPaymentMethod() ? $paymentGateway->getPaymentMethod()->getName() : '',
                ],
                'feeRate' => $paymentGateway->getFeeRate()

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
