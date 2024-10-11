<?php

namespace App\Controller\Billing\Sale\Settlement;

use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Partner\CustomerRepository;
use App\Repository\Setting\Finance\PaymentGatewayRepository;
use App\Repository\Setting\Finance\PaymentMethodRepository;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\BankRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class EditSaleSettlementController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             SaleSettlementRepository $saleSettlementRepository,
                             EntityManagerInterface $entityManager,
                             PaymentMethodRepository $paymentMethodRepository,
                             BankAccountRepository $bankAccountRepository,
                             BankRepository $bankRepository,
                             CustomerRepository $customerRepository,
                             FileUploader $fileUploader,
                             PaymentGatewayRepository $paymentGatewayRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        
        $uploadedFile = $request->files->get('file');

        $saleSettlement = $saleSettlementRepository->find($id);
        if (!$saleSettlement){
            return new JsonResponse(['hydra:description' => 'This sale settlement is not found.'], 404);
        }

        if(!$saleSettlement instanceof SaleSettlement)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of sale settlement.'], 404);
        }

        if($request->get('amountPay') <= 0){
            return new JsonResponse(['hydra:description' => 'Amount paid can not be less or equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if ($request->get('bank') !== null && $request->get('bank')){
            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('bank'));
            $filterId = intval($filter);
            $bank = $bankRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $saleSettlement->setBank($bank);
        }

        if ($request->get('bankAccount') !== null && $request->get('bankAccount')){
            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('bankAccount'));
            $filterId = intval($filter);
            $bankAccount = $bankAccountRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $saleSettlement->setBankAccount($bankAccount);
        }

        if ($saleSettlement->getInvoice()){
            $oldSettlementAmount = $request->get('oldAmount');

            $invoiceVirtualBalance = $saleSettlement->getInvoice()->getVirtualBalance();

            $checkToSettle = $oldSettlementAmount + $invoiceVirtualBalance;

            if ($checkToSettle < $request->get('amountPay')){
                return new JsonResponse(['hydra:description' => 'Amount pay can not be more than balance'], 404);
            }

            $saleSettlement->getInvoice()->setVirtualBalance($saleSettlement->getInvoice()->getVirtualBalance() + $oldSettlementAmount - $request->get('amountPay'));
        }

        if ($saleSettlement->getSaleReturnInvoice()){
            $oldSettlementAmount = $request->get('oldAmount');

            $invoiceVirtualBalance = $saleSettlement->getSaleReturnInvoice()->getVirtualBalance();

            $checkToSettle = $oldSettlementAmount + $invoiceVirtualBalance;

            if ($checkToSettle < $request->get('amountPay')){
                return new JsonResponse(['hydra:description' => 'Amount pay can not be more than balance'], 404);
            }

            $saleSettlement->getSaleReturnInvoice()->setVirtualBalance($saleSettlement->getSaleReturnInvoice()->getVirtualBalance() + $oldSettlementAmount - $request->get('amountPay'));
        }

        $saleSettlement->setSettleAt(new \DateTimeImmutable($request->get('settleAt')));
        //$saleSettlement->setSettleAt(new \DateTimeImmutable($request->get('data')('settleAt')));
        $saleSettlement->setAmountPay($request->get('amountPay'));
        // $saleSettlement->setNote($request->get['data']['note']);

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('paymentMethod'));
        $filterId = intval($filter);
        $paymentMethod = $paymentMethodRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('customer'));
        $filterId = intval($filter);
        $customer = $customerRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        $saleSettlement->setCustomer($customer);
        $saleSettlement->setPaymentMethod($paymentMethod);

        // upload the file and save its filename
        if ($uploadedFile){
            $saleSettlement->setPicture($fileUploader->upload($uploadedFile));
            $saleSettlement->setFileName($request->get('fileName'));
            $saleSettlement->setFileType($request->get('fileType'));
            $saleSettlement->setFileSize($request->get('fileSize'));
        }

        $entityManager->flush();

        return $this->json(['hydra:member' => $saleSettlement]);
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
