<?php

namespace App\Controller\Billing\Sale\School\Settlement;

use App\Entity\Partner\StudentRegistrationHistory;
use App\Entity\Security\User;
use App\Entity\Treasury\BankHistory;
use App\Entity\Treasury\CashDeskHistory;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Partner\StudentRegistrationHistoryRepository;
use App\Repository\Setting\Finance\PaymentMethodRepository;
use App\Repository\Treasury\BankAccountRepository;
use App\Repository\Treasury\BankHistoryRepository;
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
class ValidateSchoolSaleSettlementController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             SaleSettlementRepository $saleSettlementRepository,
                             EntityManagerInterface $entityManager,
                             CashDeskRepository $cashDeskRepository,
                             CashDeskHistoryRepository $cashDeskHistoryRepository,
                             StudentRegistrationHistoryRepository $studentRegistrationHistoryRepository,
                             PaymentMethodRepository $paymentMethodRepository,
                             BankHistoryRepository $bankHistoryRepository,
                             BankAccountRepository $bankAccountRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $settlement = $saleSettlementRepository->find($id);
        if (!$settlement){
            return new JsonResponse(['hydra:description' => 'This settlement is not found.'], 404);
        }

        $requestData = json_decode($request->getContent(), true);

        if (!$settlement->getStudentRegistration())
        {
            return new JsonResponse(['hydra:title' => 'Student not found'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        $amount = $requestData['amountPay'];

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $requestData['paymentMethod']);
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

            // Write cash desk history
            $cashDeskHistory = new CashDeskHistory();
            $cashDeskHistory->setCashDesk($userCashDesk);
            $cashDeskHistory->setReference($settlement->getReference());
            $cashDeskHistory->setDescription('invoice settlement');
            $cashDeskHistory->setDebit($amount);
            $cashDeskHistory->setCredit(0);
            // balance : en bas
            $cashDeskHistory->setDateAt(new \DateTimeImmutable());

            $cashDeskHistory->setInstitution($this->getUser()->getInstitution());
            $cashDeskHistory->setUser($this->getUser());
            $cashDeskHistory->setYear($this->getUser()->getCurrentYear());
            $entityManager->persist($cashDeskHistory);

            // Update cash desk daily deposit balance
            $userCashDesk->setDailyDeposit($userCashDesk->getDailyDeposit() + $amount);

            // Update cash desk balance
            $cashDeskHistories = $cashDeskHistoryRepository->findBy(['cashDesk' => $userCashDesk]);

            $debit = $amount; $credit = 0;

            foreach ($cashDeskHistories as $item)
            {
                $debit += $item->getDebit();
                $credit += $item->getCredit();
            }

            $balance = $debit - $credit;

            $cashDeskHistory->setBalance($balance);
            $userCashDesk->setBalance($balance);

            $entityManager->flush();
        }

        if ($requestData['bank'])
        {
            // Write bank history
            $bankHistory = new BankHistory();

            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $requestData['bankAccount']);
            $filterId = intval($filter);
            $bankAccount = $bankAccountRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $bankHistory->setBankAccount($bankAccount);
            $bankHistory->setReference($settlement->getReference());
            $bankHistory->setDescription('sale settlement');
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

        $studentRegistrationHistory = new StudentRegistrationHistory();
        $studentRegistrationHistory->setStudentRegistration($settlement->getStudentRegistration());
        $studentRegistrationHistory->setReference($settlement->getReference());
        $studentRegistrationHistory->setUser($this->getUser());
        $studentRegistrationHistory->setInstitution($this->getUser()->getInstitution());
        $studentRegistrationHistory->setYear($this->getUser()->getCurrentYear());

        // Update customer history balance
        $studentRegistrationHistories = $studentRegistrationHistoryRepository->findBy(['studentRegistration' => $settlement->getStudentRegistration()]);

        $debit = 0; $credit = $amount;

        foreach ($studentRegistrationHistories as $item)
        {
            $debit += $item->getDebit();
            $credit += $item->getCredit();
        }

        $balance = $credit - $debit  ;

        $studentRegistrationHistory->setBalance($balance);
        $studentRegistrationHistory->setCredit($amount);
        $studentRegistrationHistory->setDebit(0);
        $studentRegistrationHistory->setDescription('settlement ' . $settlement->getNote());
        $entityManager->persist($studentRegistrationHistory);

        $settlement->getInvoice()?->setAmountPaid($settlement->getInvoice()->getAmountPaid() + $settlement->getAmountPay());
        $settlement->getInvoice()?->setBalance($settlement->getInvoice()->getTtc() - $settlement->getInvoice()->getAmountPaid());

        // $settlement->setIsTreat(false);

        $settlement->setIsValidate(true);
        $settlement->setValidateAt(new \DateTimeImmutable());
        $settlement->setValidateBy($this->getUser());

        // Not exist in student registration
        // $settlement->getCustomer()->setCredit($settlement->getCustomer()->getCredit() + $amount);



        if (!$settlement->isIsTreat())
        {
            // Verifier si le montant a regler est inferieur au panier
            $settlementAmount = $amount;

            // $saleInvoiceItems form $saleInvoice;
            $saleInvoiceItems = $saleInvoiceItemRepository->findSaleInvoiceItemByPositionASC($settlement->getInvoice());

            foreach ($saleInvoiceItems as $saleInvoiceItem)
            {
                $amount = $saleInvoiceItem->getAmountTtc();
                $amountPaid = $saleInvoiceItem->getAmountPaid();

                $balance = $amount - $amountPaid;

                // check if $balance is less than $settlementAmount
                if ($balance < $settlementAmount)
                {
                    // set amount Paid equal to amount Paid + $balance
                    $saleInvoiceItem->setAmountPaid($amountPaid + $balance);

                    // set balance to 0 because it is settle
                    $saleInvoiceItem->setBalance(0);

                    // set is Paid = true
                    $saleInvoiceItem->setIsTreat(true);

                    $settlementAmount = $settlementAmount - $balance;


                }
                elseif ($balance > $settlementAmount)
                {
                    // check if $balance is greater than $settlementAmount

                    // set amount Paid equal to amount Paid + $settlementAmount
                    $saleInvoiceItem->setAmountPaid($amountPaid + $settlementAmount);
                    $saleInvoiceItem->setBalance($balance - $settlementAmount);

                    // set is Paid = false
                    $saleInvoiceItem->setIsTreat(false);

                    $settlementAmount = 0;
                    // break;
                }
                elseif ($balance == $settlementAmount)
                {
                    // check if $balance is equal to $settlementAmount

                    // set amount Paid equal to amount Paid + $balance
                    $saleInvoiceItem->setAmountPaid($amountPaid + $balance);

                    // set balance to 0 because it is settle
                    $saleInvoiceItem->setBalance(0);

                    // set is Paid = true
                    $saleInvoiceItem->setIsTreat(true);

                    $settlementAmount = 0;
                    // break;
                }

            }

            $settlement->setIsTreat(true);
        }


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

}
