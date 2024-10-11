<?php

namespace App\Controller\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseSettlement;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Billing\Purchase\PurchaseSettlementRepository;
use App\Repository\Partner\SupplierRepository;
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
class PutPurchaseSettlementController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                             PurchaseInvoiceRepository $purchaseInvoiceRepository,
                             PurchaseSettlementRepository $purchaseSettlementRepository,
                             EntityManagerInterface $entityManager,
                             PaymentMethodRepository $paymentMethodRepository,
                             BankAccountRepository $bankAccountRepository,
                             BankRepository $bankRepository,
                             SupplierRepository $supplierRepository,
                             FileUploader $fileUploader,
                             PaymentGatewayRepository $paymentGatewayRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');


        $uploadedFile = $request->files->get('file');

        $purchaseSettlement = $purchaseSettlementRepository->find($id);
        if (!$purchaseSettlement){
            return new JsonResponse(['hydra:description' => 'This purchase settlement is not found.'], 404);
        }

        if(!$purchaseSettlement instanceof PurchaseSettlement)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of purchase settlement.'], 404);
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

            $purchaseSettlement->setBank($bank);
        }

        if ($request->get('bankAccount') !== null && $request->get('bankAccount')){
            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('bankAccount'));
            $filterId = intval($filter);
            $bankAccount = $bankAccountRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $purchaseSettlement->setBankAccount($bankAccount);
        }

        if ($purchaseSettlement->getInvoice()){
            $oldSettlementAmount = $request->get('oldAmount');

            $invoiceVirtualBalance = $purchaseSettlement->getInvoice()->getVirtualBalance();

            $checkToSettle = $oldSettlementAmount + $invoiceVirtualBalance;

            if ($checkToSettle < $request->get('amountPay')){
                return new JsonResponse(['hydra:description' => 'Amount pay can not be more than balance'], 404);
            }

            $purchaseSettlement->getInvoice()->setVirtualBalance($purchaseSettlement->getInvoice()->getVirtualBalance() + $oldSettlementAmount - $request->get('amountPay'));
        }

        if ($purchaseSettlement->getPurchaseReturnInvoice()){
            $oldSettlementAmount = $request->get('oldAmount');

            $invoiceVirtualBalance = $purchaseSettlement->getPurchaseReturnInvoice()->getVirtualBalance();

            $checkToSettle = $oldSettlementAmount + $invoiceVirtualBalance;

            if ($checkToSettle < $request->get('amountPay')){
                return new JsonResponse(['hydra:description' => 'Amount pay can not be more than balance'], 404);
            }

            $purchaseSettlement->getPurchaseReturnInvoice()->setVirtualBalance($purchaseSettlement->getPurchaseReturnInvoice()->getVirtualBalance() + $oldSettlementAmount - $request->get('amountPay'));
        }

        $purchaseSettlement->setSettleAt(new \DateTimeImmutable($request->get('settleAt')));
        //$purchaseSettlement->setSettleAt(new \DateTimeImmutable($request->get('data')('settleAt')));
        $purchaseSettlement->setAmountPay($request->get('amountPay'));
        // $purchaseSettlement->setNote($request->get['data']['note']);

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('paymentMethod'));
        $filterId = intval($filter);
        $paymentMethod = $paymentMethodRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('supplier'));
        $filterId = intval($filter);
        $supplier = $supplierRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        $purchaseSettlement->setSupplier($supplier);
        $purchaseSettlement->setPaymentMethod($paymentMethod);

        // upload the file and save its filename
        if ($uploadedFile){
            $purchaseSettlement->setPicture($fileUploader->upload($uploadedFile));
            $purchaseSettlement->setFileName($request->get('fileName'));
            $purchaseSettlement->setFileType($request->get('fileType'));
            $purchaseSettlement->setFileSize($request->get('fileSize'));
        }

        $entityManager->flush();

        return $this->json(['hydra:member' => $purchaseSettlement]);
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
