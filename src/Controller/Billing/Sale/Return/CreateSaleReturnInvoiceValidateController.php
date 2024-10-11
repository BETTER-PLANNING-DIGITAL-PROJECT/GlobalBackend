<?php

namespace App\Controller\Billing\Sale\Return;

use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Partner\CustomerHistory;
use App\Entity\Security\User;
use App\Entity\Treasury\BankHistory;
use App\Entity\Treasury\CashDeskHistory;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Partner\CustomerHistoryRepository;
use App\Repository\Setting\Finance\PaymentGatewayRepository;
use App\Repository\Setting\Finance\PaymentMethodRepository;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\BankHistoryRepository;
use App\Repository\Treasury\BankRepository;
use App\Repository\Treasury\CashDeskHistoryRepository;
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
class CreateSaleReturnInvoiceValidateController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleReturnInvoiceItemRepository $saleReturnInvoiceItemRepository,
                             SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                             SaleSettlementRepository $saleSettlementRepository,
                             EntityManagerInterface $entityManager,
                             CashDeskRepository $cashDeskRepository,
                             CashDeskHistoryRepository $cashDeskHistoryRepository,
                             CustomerHistoryRepository $customerHistoryRepository,
                             PaymentMethodRepository $paymentMethodRepository,
                             BankHistoryRepository $bankHistoryRepository,
                             BankAccountRepository $bankAccountRepository,
                             BankRepository $bankRepository,
                             PaymentGatewayRepository $paymentGatewayRepository,
                             FileUploader $fileUploader,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $uploadedFile = $request->files->get('file');

        $saleReturnInvoice = $saleReturnInvoiceRepository->find($id);
        if (!$saleReturnInvoice){
            return new JsonResponse(['hydra:description' => 'This return invoice is not found.'], 404);
        }

        $requestData = json_decode($request->getContent(), true);
        $saleSettlement = new SaleSettlement();

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('paymentMethod'));
        $filterId = intval($filter);
        $paymentMethod = $paymentMethodRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object


        $saleSettlement->setPaymentMethod($paymentMethod);
        $saleSettlement->setSettleAt(new \DateTimeImmutable($request->get('settleAt')));

        $saleSettlement->setStudentRegistration($saleReturnInvoice->getStudentRegistration());
        $saleSettlement->setSaleReturnInvoice($saleReturnInvoice);
        $saleSettlement->setCustomer($saleReturnInvoice->getCustomer());
        $saleSettlement->setUser($this->getUser());
        $saleSettlement->setYear($this->getUser()->getCurrentYear());
        $saleSettlement->setInstitution($this->getUser()->getInstitution());

        if ($request->get('paymentGateway') !== null && $request->get('paymentGateway')){

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('paymentGateway'));
            $filterId = intval($filter);
            $paymentGateway = $paymentGatewayRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $saleSettlement->setPaymentGateway($paymentGateway);
        }
        
        if ($request->get('bank') !== null && $request->get('bank')){

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('bank'));
            $filterId = intval($filter);
            $bank = $bankRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $saleSettlement->setBank($bank);
        }

        $settlement = $saleSettlementRepository->findOneBy([], ['id' => 'DESC']);
        if (!$settlement){
            $uniqueNumber = 'SAL/SET/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $settlement->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'SAL/SET/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $saleSettlement->setReference($uniqueNumber);

        if($request->get('amountPay') <= 0 ){
            return new JsonResponse(['hydra:title' => 'Amount paid can not be less than or equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if ($request->get('amountPay') > $saleReturnInvoice->getVirtualBalance())
        {
            return new JsonResponse(['hydra:title' => 'Amount paid can not be more than balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        $saleSettlement->setAmountPay($request->get('amountPay'));
        $saleSettlement->setAmountRest($saleReturnInvoice->getVirtualBalance() - $request->get('amountPay'));

        $saleReturnInvoice->setVirtualBalance($saleReturnInvoice->getVirtualBalance() - $saleSettlement->getAmountPay());

        $entityManager->persist($saleSettlement);


        //validation

        if (!$saleSettlement->getCustomer())
        {
            return new JsonResponse(['hydra:title' => 'Customer not found'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        $amount = $request->get('amountPay');

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $request->get('paymentMethod'));
        $filterId = intval($filter);
        $paymentMethod = $paymentMethodRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        if ($paymentMethod->isIsCashDesk()){
            $userCashDesk = $cashDeskRepository->findOneBy(['operator' => $this->getUser(), 'institution' => $this->getUser()->getInstitution()]);
            if (!$userCashDesk)
            {
                return new JsonResponse(['hydra:title' => 'user_is_not_cash_desk'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            $sendCashDesk = $cashDeskRepository->findOneBy(['operator' => $this->getUser()]);

            // Cash Desk
            if ($sendCashDesk !== $cashDeskRepository->findOneBy(['operator' => $this->getUser(), 'institution' => $this->getUser()->getInstitution()]))
            {
                return new JsonResponse(['hydra:title' => 'cash_desk_user_not_found'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            if (!$sendCashDesk->isIsOpen())
            {
                return new JsonResponse(['hydra:title' => 'cash_desk_not_open'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
            }

            // Cashier Adjustment Start
            $cashDesk = $sendCashDesk;
            // $operationCategory = $cashDeskOperation->getOperationCategory();

            // Write cash desk history
            $cashDeskHistory = new CashDeskHistory();
            $cashDeskHistory->setCashDesk($cashDesk);
            $cashDeskHistory->setReference($saleSettlement->getReference());
            $cashDeskHistory->setDescription('settlement DEPOSIT');
            $cashDeskHistory->setDebit(0);
            $cashDeskHistory->setCredit($amount);
            // balance : en bas
            $cashDeskHistory->setDateAt(new \DateTimeImmutable());

            $cashDeskHistory->setInstitution($this->getUser()->getInstitution());
            $cashDeskHistory->setUser($this->getUser());
            $cashDeskHistory->setYear($this->getUser()->getCurrentYear());
            $entityManager->persist($cashDeskHistory);

            // Update cash desk daily deposit balance
            $cashDesk->setDailyDeposit($cashDesk->getDailyDeposit() + $amount);

            // Update cash desk balance
            $cashDeskHistories = $cashDeskHistoryRepository->findBy(['cashDesk' => $cashDesk]);


            $saleSettlement->setCashDesk($cashDesk);

            $debit = 0; $credit = $amount;

            foreach ($cashDeskHistories as $item)
            {
                $debit += $item->getDebit();
                $credit += $item->getCredit();
            }

            $balance = $credit - $debit ;

            $cashDeskHistory->setBalance($balance);
            $cashDesk->setBalance($balance);

            $entityManager->flush();
        }

        elseif ($request->get('bankAccount') !== null && $request->get('bankAccount')){
            // Write bank history
            $bankHistory = new BankHistory();

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $request->get('bankAccount'));
            $filterId = intval($filter);
            $bankAccount = $bankAccountRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $bankHistory->setBankAccount($bankAccount);
            $bankHistory->setReference($request->get('bankAccount'). ' - '. 'CREDIT DEPOSIT');
            $bankHistory->setDescription('settlement DEPOSIT');
            $bankHistory->setDebit(0);
            $bankHistory->setCredit($amount);
            // balance : en bas
            $bankHistory->setDateAt(new \DateTimeImmutable());

            $bankHistory->setInstitution($this->getUser()->getInstitution());
            $bankHistory->setYear($this->getUser()->getCurrentYear());
            $bankHistory->setUser($this->getUser());
            $entityManager->persist($bankHistory);



            // Update bank balance
            $bankHistories = $bankHistoryRepository->findBy(['bankAccount' => $bankAccount]);

            $debit = 0; $credit = $amount;

            foreach ($bankHistories as $item)
            {
                $debit += $item->getDebit();
                $credit += $item->getCredit();
            }

            $balance = $credit - $debit;

            $bankHistory->setBalance($balance);
            $bankAccount->setBalance($balance);
            $entityManager->flush();
        }


        $customerHistory = new CustomerHistory();
        $customerHistory->setCustomer($saleSettlement->getCustomer());
        $customerHistory->setReference($saleSettlement->getReference());
        $customerHistory->setUser($this->getUser());
        $customerHistory->setInstitution($this->getUser()->getInstitution());
        $customerHistory->setYear($this->getUser()->getCurrentYear());

        // Update customer history balance
        $customerHistories = $customerHistoryRepository->findBy(['customer' => $saleSettlement->getCustomer()]);

        $debit = 0; $credit = $amount;

        foreach ($customerHistories as $item)
        {
            $debit += $item->getDebit();
            $credit += $item->getCredit();
        }

        $balance = $credit - $debit  ;

//        $customerHistory->setBalance($balance);
        $customerHistory->setCredit(0);
        $customerHistory->setDebit($amount);
        $customerHistory->setDescription($saleSettlement->getNote(). '-'. 'SETTLEMENT');
        $customerHistory->setInstitution($this->getUser()->getInstitution());
        $entityManager->persist($customerHistory);


        //$saleSettlement->getSaleReturnInvoice()?->setAmountPaid($saleSettlement->getInvoice()->getAmountPaid() + $saleSettlement->getAmountPay());
        $saleSettlement->getSaleReturnInvoice()?->setAmountPaid($saleSettlement->getSaleReturnInvoice()->getAmountPaid() + $request->get('amountPay'));
        $saleSettlement->getSaleReturnInvoice()?->setBalance($saleSettlement->getSaleReturnInvoice()->getTtc() - $saleSettlement->getSaleReturnInvoice()->getAmountPaid());


        $saleSettlement->setIsValidate(true);
        $saleSettlement->setValidateAt(new \DateTimeImmutable());
        $saleSettlement->setValidateBy($this->getUser());

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

    function generer_numero_unique() {
        // Génère un nombre aléatoire entre 10000 et 99999 (inclus)
        $numero_unique = rand(10000, 99999);
        return $numero_unique;
    }

}
