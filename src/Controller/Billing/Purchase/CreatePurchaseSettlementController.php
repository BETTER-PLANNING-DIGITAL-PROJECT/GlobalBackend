<?php

namespace App\Controller\Billing\Purchase;

use App\Entity\Billing\Purchase\PurchaseSettlement;
use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Billing\Purchase\PurchaseSettlementRepository;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
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

        $invoice = $purchaseInvoiceRepository->findOneBy(['id' => $id]);
        if (!$invoice){
            return new JsonResponse(['hydra:description' => 'This invoice '.$id.' is not found.'], 404);
        }

        $purchaseSettlement = new PurchaseSettlement();

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('paymentMethod'));
        $filterId = intval($filter);
        $paymentMethod = $paymentMethodRepository->find($filterId);

        if (!$paymentMethod){
            return new JsonResponse(['hydra:description' => 'Payment method not found.'], 404);
        }
        // END: Filter the uri to just take the id and pass it to our object

        if (isset($requestData['cashDesk']) && $requestData['cashDesk']){

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $requestData['cashDesk']);
            $filterId = intval($filter);
            $cashDesk = $cashDeskRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $purchaseSettlement->setCashDesk($cashDesk);
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

        /*if ($request->get('paymentGateway') !== null && $request->get('paymentGateway')){

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('paymentGateway'));
            $filterId = intval($filter);
            $paymentGateway = $paymentGatewayRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $purchaseSettlement->setPaymentGateway($paymentGateway);
        }*/

        if($request->get('amountPay') <= 0 ){
            return new JsonResponse(['hydra:title' => 'Amount can not be less or equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if ($request->get('amountPay') > $invoice->getVirtualBalance())
        {
            return new JsonResponse(['hydra:title' => 'Amount can not be more than balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if (!$invoice->getSupplier())
        {
            return new JsonResponse(['hydra:title' => 'Customer not found!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        // CREATE SETTLEMENT SECTION

        // Set settlement
        $purchaseSettlement->setPaymentMethod($paymentMethod);
        $purchaseSettlement->setSettleAt(new \DateTimeImmutable($request->get('settleAt')));
        $purchaseSettlement->setInvoice($invoice);

        $purchaseSettlement->setUser($this->getUser());
        $purchaseSettlement->setYear($this->getUser()->getCurrentYear());
        $purchaseSettlement->setInstitution($this->getUser()->getInstitution());

       // $purchaseSettlement->setStudentRegistration($invoice->getStudentRegistration());
        //$purchaseSettlement->setClass($invoice->getClass());
        //$purchaseSettlement->setSchool($invoice->getSchool());
        $purchaseSettlement->setSupplier($invoice->getSupplier());

        $generateSettlementUniqNumber = $purchaseSettlementRepository->findOneBy([], ['id' => 'DESC']);

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

        if ($request->get('amountPay') <= $invoice->getAmount()){
            $purchaseSettlement->setAmountPay($request->get('amountPay'));
            $purchaseSettlement->setAmountRest($invoice->getAmount() - $request->get('amountPay'));
        }
        else{
            $purchaseSettlement->setAmountPay($request->get('amountPay'));
            $purchaseSettlement->setAmountRest(0);
        }
        // $purchaseSettlement->setAmountRest($invoice->getVirtualBalance() - $request->get('amountPay'));

        $purchaseSettlement->setIsTreat(false);
        $purchaseSettlement->setIsValidate(false);

        // upload the file and save its filename
        if ($uploadedFile){
            $purchaseSettlement->setPicture($fileUploader->upload($uploadedFile));
            $purchaseSettlement->setFileName($request->get('fileName'));
            $purchaseSettlement->setFileType($request->get('fileType'));
            $purchaseSettlement->setFileSize($request->get('fileSize'));
        }

        // Update invoice
        $invoice->setVirtualBalance($invoice->getVirtualBalance() - $purchaseSettlement->getAmountPay());

        // Persist settlement
        $this->entityManager->persist($purchaseSettlement);

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

    function generer_numero_unique() {
        // Génère un nombre aléatoire entre 10000 et 99999 (inclus)
        $numero_unique = rand(10000, 99999);
        return $numero_unique;
    }
}
