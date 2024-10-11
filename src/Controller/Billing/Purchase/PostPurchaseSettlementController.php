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
use App\Repository\Treasury\CashDeskRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PostPurchaseSettlementController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly EntityManagerInterface $entityManager
                                )
    {
    }

    public function jsondecode()
    {
        try {
            return file_get_contents('php://input') ?
                json_decode(file_get_contents('php://input'), false) :
                [];
        }catch (\Exception $ex)
        {
            return [];
        }

    }

    public function __invoke(
                            PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                            PurchaseInvoiceRepository $purchaseInvoiceRepository,
                            PaymentMethodRepository $paymentMethodRepository,
                            PaymentGatewayRepository $paymentGatewayRepository,
                            CashDeskRepository $cashDeskRepository,
                            BankRepository $bankRepository,
                            SupplierRepository $supplierRepository,
                            PurchaseSettlementRepository $purchaseSettlementRepository,
                            BankAccountRepository $bankAccountRepository,
                            FileUploader $fileUploader
                             ): JsonResponse
    {
        $request = Request::createFromGlobals();

        $uploadedFile = $request->files->get('file');

        if($request->get('amountPay') <= 0 ){
            return new JsonResponse(['hydra:title' => 'Amount paid can not be less or equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        // Payment Method
        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('paymentMethod'));
        $filterId = intval($filter);
        $paymentMethod = $paymentMethodRepository->find($filterId);

        if (!$paymentMethod){
            return new JsonResponse(['hydra:title' => 'Payment method not found !'], 404);
        }

        if ($paymentMethod->isIsCashDesk())
        {
            $userCashDesk = $cashDeskRepository->findOneBy(['operator' => $this->getUser(), 'institution' => $this->getUser()->getInstitution()]);
            if (!$userCashDesk)
            {
                return new JsonResponse(['hydra:title' => 'You are not a cashier!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            if (!$userCashDesk->isIsOpen())
            {
                return new JsonResponse(['hydra:title' => 'Your cash desk is not open!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }
        }
        elseif ($paymentMethod->isIsBank())
        {
            if ($request->get('bankAccount') !== null && $request->get('bankAccount')) {
                // START: Filter the uri to just take the id and pass it to our object
                $filter = preg_replace("/[^0-9]/", '', $request->get('bankAccount'));
                $filterId = intval($filter);
                $bankAccount = $bankAccountRepository->find($filterId);
                // END: Filter the uri to just take the id and pass it to our object

                if (!$bankAccount){
                    return new JsonResponse(['hydra:title' => 'Bank account not found !'], 404);
                }
            }
        }
        // END: Filter the uri to just take the id and pass it to our object

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('supplier'));
        $filterId = intval($filter);
        $supplier = $supplierRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        if (!$supplier){
            return new JsonResponse(['hydra:title' => 'Supplier not found !'], 404);
        }

        // SETTLEMENT SECTION
        $purchaseSettlement = new PurchaseSettlement();

        $purchaseSettlement->setSupplier($supplier);

        $generateSettlementUniqNumber = $purchaseSettlementRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$generateSettlementUniqNumber){
            $uniqueNumber = 'PUR/SET/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $generateSettlementUniqNumber->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'PUR/SET/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }
        $purchaseSettlement->setReference($uniqueNumber);

        $purchaseSettlement->setAmountPay($request->get('amountPay'));
        $purchaseSettlement->setSettleAt(new \DateTimeImmutable($request->get('settleAt')));

        // START: Filter the uri to just take the id and pass it to our object
        if ($paymentMethod->isIsCashDesk())
        {
            $purchaseSettlement->setCashDesk($userCashDesk);
        }
        elseif ($paymentMethod->isIsBank())
        {
            if ($request->get('bankAccount') !== null && $request->get('bankAccount')) {

                $purchaseSettlement->setBankAccount($bankAccount);

                if ($request->get('bank') !== null && $request->get('bank')){
                    // START: Filter the uri to just take the id and pass it to our object
                    $filter = preg_replace("/[^0-9]/", '', $request->get('bank'));
                    $filterId = intval($filter);
                    $bank = $bankRepository->find($filterId);
                    // END: Filter the uri to just take the id and pass it to our object

                    $purchaseSettlement->setBank($bank);
                }
            }
        }
        // END: Filter the uri to just take the id and pass it to our object

        // $purchaseSettlement->setNote('sale invoice draft settlement');
        $purchaseSettlement->setPaymentMethod($paymentMethod);
        $purchaseSettlement->setStatus('draft');
        $purchaseSettlement->setIsValidate(false);

        $purchaseSettlement->setUser($this->getUser());
        $purchaseSettlement->setYear($this->getUser()->getCurrentYear());
        $purchaseSettlement->setBranch($this->getUser()->getBranch());
        $purchaseSettlement->setInstitution($this->getUser()->getInstitution());

        $purchaseSettlement->setIsTreat(false);

        // upload the file and save its filename
        if ($uploadedFile){
            $purchaseSettlement->setPicture($fileUploader->upload($uploadedFile));
            $purchaseSettlement->setFileName($request->get('fileName'));
            $purchaseSettlement->setFileType($request->get('fileType'));
            $purchaseSettlement->setFileSize($request->get('fileSize'));
        }

        // Persist settlement
        $this->entityManager->persist($purchaseSettlement);

        // SETTLEMENT SECTION END

        $this->entityManager->flush();

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
