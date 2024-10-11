<?php

namespace App\Controller\Billing\Sale\School\Settlement;

use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
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
class EditSchoolSaleSettlementController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             SaleSettlementRepository $saleSettlementRepository,
                             CashDeskRepository $cashDeskRepository,
                             EntityManagerInterface $entityManager,
                             PaymentMethodRepository $paymentMethodRepository,
                             BankAccountRepository $bankAccountRepository,
                             BankRepository $bankRepository,
                             StudentRegistrationRepository $studentRegistrationRepository,
                             FileUploader $fileUploader,
                             PaymentGatewayRepository $paymentGatewayRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');


        $uploadedFile = $request->files->get('file');

        $settlement = $saleSettlementRepository->find($id);
        if (!$settlement){
            return new JsonResponse(['hydra:description' => 'This settlement is not found.'], 404);
        }

        if(!$settlement instanceof SaleSettlement)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This data must be type of settlement.'], 404);
        }

        if($request->get('amountPay') <= 0){
            return new JsonResponse(['hydra:description' => 'Amounted paid can not be less or equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if ($request->get('paymentGateway') !== null && $request->get('paymentGateway')){

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('paymentGateway'));
            $filterId = intval($filter);
            $paymentGateway = $paymentGatewayRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $settlement->setPaymentGateway($paymentGateway);
        }

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('paymentMethod'));
        $filterId = intval($filter);
        $paymentMethod = $paymentMethodRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        if ($paymentMethod->isIsCashDesk())
        {
            $userCashDesk = $cashDeskRepository->findOneBy(['operator' => $this->getUser(), 'institution' => $this->getUser()->getInstitution()]);
            if (!$userCashDesk)
            {
                return new JsonResponse(['hydra:title' => 'You are not a cashier!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            if (!$userCashDesk->isIsOpen())
            {
                return new JsonResponse(['hydra:title' => 'You cash desk is not open!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            $settlement->setCashDesk($userCashDesk);
        }

        if ($request->get('bank') !== null && $request->get('bank')){

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('bank'));
            $filterId = intval($filter);
            $bank = $bankRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $settlement->setBank($bank);
        }

        if ($request->get('bankAccount') !== null && $request->get('bankAccount')){

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('bankAccount'));
            $filterId = intval($filter);
            $bankAccount = $bankAccountRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $settlement->setBankAccount($bankAccount);
        }

        if ($settlement->getInvoice()){
            $oldSettlementAmount = $request->get('oldAmount');

            $invoiceVirtualBalance = $settlement->getInvoice()->getVirtualBalance();

            $checkToSettle = $oldSettlementAmount + $invoiceVirtualBalance;

            if ($checkToSettle < $request->get('amountPay')){

                return new JsonResponse(['hydra:description' => 'Amount pay can not be more than balance'], 404);
            }

            $settlement->getInvoice()->setVirtualBalance($settlement->getInvoice()->getVirtualBalance() + $oldSettlementAmount - $request->get('amountPay'));
        }

        if ($settlement->getSaleReturnInvoice()){
            $oldSettlementAmount = $request->get('oldAmount');

            $invoiceVirtualBalance = $settlement->getSaleReturnInvoice()->getVirtualBalance();

            $checkToSettle = $oldSettlementAmount + $invoiceVirtualBalance;

            if ($checkToSettle < $request->get('amountPay')){

                return new JsonResponse(['hydra:description' => 'Amount pay can not be more than balance'], 404);
            }


            $settlement->getSaleReturnInvoice()->setVirtualBalance($settlement->getSaleReturnInvoice()->getVirtualBalance() + $oldSettlementAmount - $request->get('amountPay'));
        }

        $settlement->setSettleAt(new \DateTimeImmutable($request->get('settleAt')));
        //$settlement->setSettleAt(new \DateTimeImmutable($request->get('data')('settleAt')));
        $settlement->setAmountPay($request->get('amountPay'));
        // $settlement->setNote($request->get['data']['note']);


        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('studentRegistration'));
        $filterId = intval($filter);
        $studentRegistration = $studentRegistrationRepository->find($filterId);
        $settlement->setStudentRegistration($studentRegistration);
        // END: Filter the uri to just take the id and pass it to our object

        //$settlement->setCustomer($customer);


        $settlement->setPaymentMethod($paymentMethod);

        // $settlement->setUser($this->getUser());
        // $settlement->setInstitution($this->getUser()->getInstitution());
        // $settlement->setYear($this->getUser()->getCurrentYear());

        // upload the file and save its filename
        if ($uploadedFile){
            $settlement->setPicture($fileUploader->upload($uploadedFile));
            $settlement->setFileName($request->get('fileName'));
            $settlement->setFileType($request->get('fileType'));
            $settlement->setFileSize($request->get('fileSize'));
        }

        $entityManager->persist($settlement);

        $entityManager->flush();

        return $this->json(['hydra:member' => $settlement]);
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
