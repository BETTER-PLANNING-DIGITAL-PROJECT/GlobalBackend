<?php

namespace App\Controller\Billing\Purchase;

use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Partner\SupplierHistory;
use App\Entity\Security\User;
use App\Entity\Treasury\BankHistory;
use App\Entity\Treasury\CashDeskHistory;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\School\Finance\SupplierHistoryRepository;
use App\Repository\Setting\Finance\PaymentGatewayRepository;
use App\Repository\Setting\Finance\PaymentMethodRepository;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\BankHistoryRepository;
use App\Repository\Treasury\BankRepository;
use App\Repository\Treasury\CashDeskHistoryRepository;
use App\Repository\Treasury\CashDeskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class SetAndValidateSupplierInvoiceDirectlyController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             SaleSettlement $saleSettlement,
                             EntityManagerInterface $entityManager,
                             CashDeskRepository $cashDeskRepository,
                             CashDeskHistoryRepository $cashDeskHistoryRepository,
                             SupplierHistoryRepository $supplierHistoryRepository,
                             PaymentMethodRepository $paymentMethodRepository,
                             BankHistoryRepository $bankHistoryRepository,
                             BankAccountRepository $bankAccountRepository,
                             BankRepository $bankRepository,
                             PaymentGatewayRepository $paymentGatewayRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $invoice = $saleInvoiceRepository->find($id);
        if (!$invoice){
            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }

        $requestData = json_decode($request->getContent(), true);
        $supplierSettlement = new SaleSettlement();

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $requestData['paymentMethod']);
        $filterId = intval($filter);
        $paymentMethod = $paymentMethodRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object


        $supplierSettlement->setPaymentMethod($paymentMethod);
        $supplierSettlement->setSettleAt(new \DateTimeImmutable($requestData['settleAt']));

        $supplierSettlement->setStudentRegistration($invoice->getStudentRegistration());
        $supplierSettlement->setSupplier($invoice->getSupplier());

        if (isset($requestData['paymentGateway']) && $requestData['paymentGateway']){

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $requestData['paymentGateway']);
            $filterId = intval($filter);
            $paymentGateway = $paymentGatewayRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $supplierSettlement->setPaymentGateway($paymentGateway);
        }

        if (isset($requestData['bank']) && $requestData['bank']){

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $requestData['bank']);
            $filterId = intval($filter);
            $bank = $bankRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $supplierSettlement->setBank($bank);
        }


        $uniqueNumber = $this->generer_numero_unique();
        $uniqueNumber = 'SET/SUP' . $uniqueNumber;

        $supplierSettlement->setReference($uniqueNumber);

        if($requestData['amountPay'] <= 0 ){
            return new JsonResponse(['hydra:title' => 'Amount paid can not be less than or equal to zero'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        if ($requestData['amountPay'] > $invoice->getVirtualBalance())
        {
            return new JsonResponse(['hydra:title' => 'Amount paid can not be more than balance'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        $supplierSettlement->setAmountPay($requestData['amountPay']);
        $supplierSettlement->setAmountRest($invoice->getVirtualBalance() - $requestData['amountPay']);

        $invoice->setVirtualBalance($invoice->getVirtualBalance() - $supplierSettlement->getAmountPay());

        $supplierSettlement->setInvoice($invoice);

        $entityManager->persist($supplierSettlement);




        //validation


        if (!$supplierSettlement->getSupplier())
        {
            return new JsonResponse(['hydra:title' => 'Supplier not found'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        $amount = $requestData['amountPay'];

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $requestData['paymentMethod']);
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
            $cashDeskHistory->setReference($supplierSettlement->getReference());
            $cashDeskHistory->setDescription('settlement DEPOSIT');
            $cashDeskHistory->setDebit($amount);
            $cashDeskHistory->setCredit(0);
            // balance : en bas
            $cashDeskHistory->setDateAt(new \DateTimeImmutable());

            $cashDeskHistory->setInstitution($this->getUser()->getInstitution());
            $cashDeskHistory->setUser($this->getUser());
            $cashDeskHistory->setYear($this->getUser()->getCurrentYear());
            $entityManager->persist($cashDeskHistory);

            // Update cash desk daily deposit balance
            $cashDesk->setDailyDepositAmount($cashDesk->getDailyDepositAmount() + $amount);

            // Update cash desk balance
            $cashDeskHistories = $cashDeskHistoryRepository->findBy(['cashDesk' => $cashDesk]);

            $debit = $amount; $credit = 0;

            foreach ($cashDeskHistories as $item)
            {
                $debit += $item->getDebit();
                $credit += $item->getCredit();
            }

            $balance = $debit - $credit;

            $cashDeskHistory->setBalance($balance);
            $cashDesk->setBalance($balance);

            $entityManager->flush();
        }

        if (isset($requestData['bank']) && $requestData['bank']){
            // Write bank history
            $bankHistory = new BankHistory();

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $requestData['bankAccount']);
            $filterId = intval($filter);
            $bankAccount = $bankAccountRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $bankHistory->setBankAccount($bankAccount);
            $bankHistory->setReference($requestData['bankAccount']. ' - '. 'DEPOSIT');
            $bankHistory->setDescription('settlement DEPOSIT');
            $bankHistory->setDebit($amount);
            $bankHistory->setCredit(0);
            // balance : en bas
            $bankHistory->setDateAt(new \DateTimeImmutable());

            $bankHistory->setInstitution($this->getUser()->getInstitution());
            $bankHistory->setYear($this->getUser()->getCurrentYear());
            $bankHistory->setUser($this->getUser());
            $entityManager->persist($bankHistory);



            // Update bank balance
            $bankHistories = $bankHistoryRepository->findBy(['bankAccount' => $bankAccount]);

            $debit = $amount; $credit = 0;

            foreach ($bankHistories as $item)
            {
                $debit += $item->getDebit();
                $credit += $item->getCredit();
            }

            $balance = $debit - $credit;

            $bankHistory->setBalance($balance);
            $bankAccount->setBalance($balance);
            $entityManager->flush();
        }


        $supplierHistory = new SupplierHistory();
        $supplierHistory->setSupplier($supplierSettlement->getSupplier());
        $supplierHistory->setReference($supplierSettlement->getReference());
        $supplierHistory->setUser($this->getUser());
        $supplierHistory->setInstitution($this->getUser()->getInstitution());
        $supplierHistory->setYear($this->getUser()->getCurrentYear());

        // Update supplier history balance
        $customerHistories = $supplierHistoryRepository->findBy(['supplier' => $supplierSettlement->getSupplier()]);

        $debit = 0; $credit = $amount;

        foreach ($customerHistories as $item)
        {
            $debit += $item->getDebit();
            $credit += $item->getCredit();
        }

        $balance = $credit - $debit  ;

        $supplierHistory->setBalance($balance);
        $supplierHistory->setCredit($amount);
        $supplierHistory->setDebit(0);
        $supplierHistory->setDescription($supplierSettlement->getNote(). '-'. 'SETTLEMENT');
        $entityManager->persist($supplierHistory);


        $supplierSettlement->getInvoice()?->setAmountPaid($supplierSettlement->getInvoice()->getAmountPaid() + $supplierSettlement->getAmountPay());
        $supplierSettlement->getInvoice()?->setBalance($supplierSettlement->getInvoice()->getTtc() - $supplierSettlement->getInvoice()->getAmountPaid());


        $supplierSettlement->setIsValidate(true);
        $supplierSettlement->setValidateAt(new \DateTimeImmutable());
        $supplierSettlement->setValidateBy($this->getUser());

        $entityManager->flush();


        return $this->json(['hydra:member' => $supplierSettlement]);
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
