<?php

namespace App\Controller\Billing\Sale\School\Return;

use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemRepository;
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
class CreateSchoolSaleReturnInvoiceSettlementController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly EntityManagerInterface $entityManager
                                )
    {
    }

    public function __invoke(SaleReturnInvoiceItemRepository $saleReturnInvoiceItemRepository,
                             SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                             PaymentMethodRepository $paymentMethodRepository,
                             PaymentGatewayRepository $paymentGatewayRepository,
                             CashDeskRepository $cashDeskRepository,
                             BankRepository $bankRepository,
                             SaleSettlementRepository $settlementRepository,
                             BankAccountRepository $bankAccountRepository,
                             FileUploader $fileUploader): JsonResponse
    {
        $request = Request::createFromGlobals();
        $uploadedFile = $request->files->get('file');

        $id = $request->get('id');
        $saleReturnInvoice = $saleReturnInvoiceRepository->find($id);
        if (!$saleReturnInvoice){
            return new JsonResponse(['hydra:description' => 'This return invoice is not found.'], 404);
        }

        $settlement = new SaleSettlement();

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('paymentMethod'));
        $filterId = intval($filter);
        $paymentMethod = $paymentMethodRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

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
                return new JsonResponse(['hydra:title' => 'You cash desk is not open!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            $settlement->setCashDesk($userCashDesk);
        }


        if ($paymentMethod->isIsBank())
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

                $settlement->setBankAccount($bankAccount);

                if ($request->get('bank') !== null && $request->get('bank')){
                    // START: Filter the uri to just take the id and pass it to our object
                    $filter = preg_replace("/[^0-9]/", '', $request->get('bank'));
                    $filterId = intval($filter);
                    $bank = $bankRepository->find($filterId);
                    // END: Filter the uri to just take the id and pass it to our object

                    $settlement->setBank($bank);
                }
            }
        }
        // END: Filter the uri to just take the id and pass it to our object

        $sumSettlementPaid = $settlementRepository->sumReturnSettlementValidatedAmountByInvoice($saleReturnInvoice)[0][1];
        if ($saleReturnInvoice->getSaleInvoice()->getAmountPaid() < $request->get('amountPay') + $sumSettlementPaid){
            return new JsonResponse(['hydra:title' => 'Amount to return cannot be more than the one paid : '. $saleReturnInvoice->getSaleInvoice()->getAmountPaid()], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        $settlement->setSaleReturnInvoice($saleReturnInvoice);
        $settlement->setStudentRegistration($saleReturnInvoice->getStudentRegistration());

        if ($request->get('reference') !== null){
            $settlement->setReference($request->get('reference'));
        }
        else{
            $generateSettlementUniqNumber = $settlementRepository->findOneBy([], ['id' => 'DESC']);

            if (!$generateSettlementUniqNumber){
                $uniqueNumber = 'SAL/SET/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
            }
            else{
                $filterNumber = preg_replace("/[^0-9]/", '', $generateSettlementUniqNumber->getReference());
                $number = intval($filterNumber);

                // Utilisation de number_format() pour ajouter des zéros à gauche
                $uniqueNumber = 'SAL/SET/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
            }
            $settlement->setReference($uniqueNumber);
        }

        // Les sens ici ?
        if ($request->get('amountPay') < $saleReturnInvoice->getVirtualBalance()){
            $settlement->setAmountPay($request->get('amountPay'));
            $settlement->setAmountRest($saleReturnInvoice->getVirtualBalance() - $request->get('amountPay'));

            // Update return invoice
            $saleReturnInvoice->setVirtualBalance($saleReturnInvoice->getVirtualBalance() - $request->get('amountPay'));
        }
        else{
            $settlement->setAmountPay($request->get('amountPay'));
            $settlement->setAmountRest(0);

            // Update return invoice
            $saleReturnInvoice->setVirtualBalance(0);
        }
        // Les sens ici ?

        $settlement->setSettleAt(new \DateTimeImmutable($request->get('settleAt')));

        $settlement->setPaymentMethod($paymentMethod);
        $settlement->setNote('draft settlement');

        $settlement->setStudentRegistration($saleReturnInvoice->getStudentRegistration());
        $settlement->setClass($saleReturnInvoice->getClass());
        $settlement->setSchool($saleReturnInvoice->getSchool());

        $settlement->setUser($this->getUser());
        $settlement->setYear($this->getUser()->getCurrentYear());
        $settlement->setBranch($this->getUser()->getBranch());
        $settlement->setInstitution($this->getUser()->getInstitution());

        $settlement->setStatus('draft');

        $settlement->setIsTreat(false);
        $settlement->setIsValidate(false);

        // upload the file and save its filename
        if ($uploadedFile){
            $settlement->setPicture($fileUploader->upload($uploadedFile));
            $settlement->setFileName($request->get('fileName'));
            $settlement->setFileType($request->get('fileType'));
            $settlement->setFileSize($request->get('fileSize'));
        }

        // Persist settlement
        $this->entityManager->persist($settlement);

        // SETTLEMENT SECTION END

        $this->entityManager->flush();

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
