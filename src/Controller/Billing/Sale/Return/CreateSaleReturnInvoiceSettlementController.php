<?php

namespace App\Controller\Billing\Sale\Return;

use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
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
class CreateSaleReturnInvoiceSettlementController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $entityManager)
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

    public function __invoke(SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                             PaymentMethodRepository $paymentMethodRepository,
                             PaymentGatewayRepository $paymentGatewayRepository,
                             CashDeskRepository $cashDeskRepository,
                             BankRepository $bankRepository,
                             BankAccountRepository $bankAccountRepository,
                             SaleSettlementRepository $saleSettlementRepository,
                             FileUploader $fileUploader): JsonResponse
    {
        $request = Request::createFromGlobals();

        $id = $request->get('id');

        $uploadedFile = $request->files->get('file');

        $saleReturnInvoice = $saleReturnInvoiceRepository->findOneBy(['id' => $id]);
        if (!$saleReturnInvoice){
            return new JsonResponse(['hydra:description' => 'This sale return invoice '.$id.' is not found.'], 404);
        }

        if($request->get('amountPay') <= 0 ){
            return new JsonResponse(['hydra:title' => 'Amount can not be less or equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }
        elseif ($request->get('amountPay') > $saleReturnInvoice->getVirtualBalance())
        {
            return new JsonResponse(['hydra:title' => 'Amount can not be more than balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }
        $amount = $saleReturnInvoice->getVirtualBalance();

        if(($saleReturnInvoice->getVirtualBalance() == 0) || ($saleReturnInvoice->getBalance() == 0)){
            return new JsonResponse(['hydra:title' => 'Sale return invoice already settle'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if (!$saleReturnInvoice->getCustomer())
        {
            return new JsonResponse(['hydra:title' => 'Customer not found!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
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

            if($amount > $userCashDesk->getBalance())
            {
                return new JsonResponse(['hydra:title' => 'Insufficient balance for this operation'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
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

                if($amount > $bankAccount->getBalance()){
                    return new JsonResponse(['hydra:title' => 'Insufficient balance for this operation'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
                }
            }
        }
        // END: Filter the uri to just take the id and pass it to our object

        // SETTLEMENT SECTION
        $saleSettlement = new SaleSettlement();

        $saleSettlement->setSaleReturnInvoice($saleReturnInvoice);
        $saleSettlement->setCustomer($saleReturnInvoice->getCustomer());

        if ($request->get('reference') !== null){
            $saleSettlement->setReference($request->get('reference'));
        }
        else{
            $generateSettlementUniqNumber = $saleSettlementRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
            if (!$generateSettlementUniqNumber){
                $uniqueNumber = 'SAL/SET/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
            }
            else{
                $filterNumber = preg_replace("/[^0-9]/", '', $generateSettlementUniqNumber->getReference());
                $number = intval($filterNumber);

                // Utilisation de number_format() pour ajouter des zéros à gauche
                $uniqueNumber = 'SAL/SET/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
            }
            $saleSettlement->setReference($uniqueNumber);
        }

        if ($request->get('amountPay') < $saleReturnInvoice->getVirtualBalance()){
            $saleSettlement->setAmountPay($request->get('amountPay'));
            $saleSettlement->setAmountRest($saleReturnInvoice->getVirtualBalance() - $request->get('amountPay'));

            // Update sale return invoice
            $saleReturnInvoice->setVirtualBalance($saleReturnInvoice->getVirtualBalance() - $request->get('amountPay'));
        }
        else{
            $saleSettlement->setAmountPay($request->get('amountPay'));
            $saleSettlement->setAmountRest(0);

            // Update sale return invoice
            $saleReturnInvoice->setVirtualBalance(0);
        }

        $saleSettlement->setSettleAt(new \DateTimeImmutable($request->get('settleAt')));

        // START: Filter the uri to just take the id and pass it to our object
        if ($paymentMethod->isIsCashDesk())
        {
            $saleSettlement->setCashDesk($userCashDesk);
        }
        elseif ($paymentMethod->isIsBank())
        {
            if ($request->get('bankAccount') !== null && $request->get('bankAccount')) {

                $saleSettlement->setBankAccount($bankAccount);

                if ($request->get('bank') !== null && $request->get('bank')){
                    // START: Filter the uri to just take the id and pass it to our object
                    $filter = preg_replace("/[^0-9]/", '', $request->get('bank'));
                    $filterId = intval($filter);
                    $bank = $bankRepository->find($filterId);
                    // END: Filter the uri to just take the id and pass it to our object

                    $saleSettlement->setBank($bank);
                }
            }
        }
        // END: Filter the uri to just take the id and pass it to our object

        $saleSettlement->setNote('sale return invoice draft settlement');
        $saleSettlement->setPaymentMethod($paymentMethod);
        $saleSettlement->setStatus('draft');
        $saleSettlement->setIsValidate(false);

        $saleSettlement->setUser($this->getUser());
        $saleSettlement->setYear($this->getUser()->getCurrentYear());
        $saleSettlement->setBranch($this->getUser()->getBranch());
        $saleSettlement->setInstitution($this->getUser()->getInstitution());

        $saleSettlement->setIsTreat(false);

        // upload the file and save its filename
        if ($uploadedFile){
            $saleSettlement->setPicture($fileUploader->upload($uploadedFile));
            $saleSettlement->setFileName($request->get('fileName'));
            $saleSettlement->setFileType($request->get('fileType'));
            $saleSettlement->setFileSize($request->get('fileSize'));
        }

        // Persist settlement
        $this->entityManager->persist($saleSettlement);

        // SETTLEMENT SECTION END

        // other invoice status update
        $saleReturnInvoice->setOtherStatus('settlement');

        $this->entityManager->flush();

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

    /*function generer_numero_unique() {
        // Génère un nombre aléatoire entre 10000 et 99999 (inclus)
        $numero_unique = rand(10000, 99999);
        return $numero_unique;
    }*/
}
