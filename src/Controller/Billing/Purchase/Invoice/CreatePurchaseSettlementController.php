<?php

namespace App\Controller\Billing\Purchase\Invoice;

use App\Entity\Billing\Purchase\PurchaseSettlement;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Billing\Purchase\PurchaseSettlementRepository;
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
class CreatePurchaseSettlementController extends AbstractController
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
                             BankAccountRepository $bankAccountRepository,
                             PurchaseSettlementRepository $purchaseSettlementRepository,
                             FileUploader $fileUploader
                             ): JsonResponse
    {

        $request = Request::createFromGlobals();

        $id = $request->get('id');

        $uploadedFile = $request->files->get('file');

        $purchaseInvoice = $purchaseInvoiceRepository->findOneBy(['id' => $id]);
        if (!$purchaseInvoice){
            return new JsonResponse(['hydra:description' => 'This purchase invoice '.$id.' is not found.'], 404);
        }

        if($request->get('amountPay') <= 0 ){
            return new JsonResponse(['hydra:title' => 'Amount can not be less or equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }
        elseif ($request->get('amountPay') > $purchaseInvoice->getVirtualBalance())
        {
            return new JsonResponse(['hydra:title' => 'Amount can not be more than balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if(($purchaseInvoice->getTtc() > 0) && ($purchaseInvoice->getBalance() == 0) && ($purchaseInvoice->getTtc() == $purchaseInvoice->getAmountPaid())){
            return new JsonResponse(['hydra:title' => 'Sale invoice already settle'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if (!$purchaseInvoice->getSupplier())
        {
            return new JsonResponse(['hydra:title' => 'Supplier not found!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        // Payment Method
        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('paymentMethod'));
        $filterId = intval($filter);
        $paymentMethod = $paymentMethodRepository->find($filterId);

        if (!$paymentMethod){
            return new JsonResponse(['hydra:description' => 'Payment method not found !'], 404);
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

            if ($request->get('amountPay') > $userCashDesk->getBalance())
            {
                return new JsonResponse(['hydra:title' => 'Purchase Invoice Amount can not be more than your cash desk balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
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
                    return new JsonResponse(['hydra:description' => 'Bank account not found !'], 404);
                }

                if ($request->get('amountPay') > $bankAccount->getBalance())
                {
                    return new JsonResponse(['hydra:title' => 'Purchase Invoice Amount can not be more than your bank balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
                }
            }
        }
        // END: Filter the uri to just take the id and pass it to our object

        // SETTLEMENT SECTION
        $purchaseSettlement = new PurchaseSettlement();

        $purchaseSettlement->setInvoice($purchaseInvoice);
        $purchaseSettlement->setSupplier($purchaseInvoice->getSupplier());

        if ($request->get('reference') !== null){
            $purchaseSettlement->setReference($request->get('reference'));
        }
        else{
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
        }

        if ($request->get('amountPay') < $purchaseInvoice->getVirtualBalance()){
            $purchaseSettlement->setAmountPay($request->get('amountPay'));
            $purchaseSettlement->setAmountRest($purchaseInvoice->getVirtualBalance() - $request->get('amountPay'));

            // Update purchase invoice
            $purchaseInvoice->setVirtualBalance($purchaseInvoice->getVirtualBalance() - $request->get('amountPay'));
        }
        else{
            $purchaseSettlement->setAmountPay($request->get('amountPay'));
            $purchaseSettlement->setAmountRest(0);

            // Update purchase invoice
            $purchaseInvoice->setVirtualBalance(0);
        }

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

        $purchaseSettlement->setNote('purchase invoice draft settlement');
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

        // other invoice status update
        $purchaseInvoice->setOtherStatus('settlement');

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
