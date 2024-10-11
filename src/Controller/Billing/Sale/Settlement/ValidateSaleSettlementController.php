<?php

namespace App\Controller\Billing\Sale\Settlement;

use App\Entity\Partner\CustomerHistory;
use App\Entity\Security\User;
use App\Entity\Treasury\BankHistory;
use App\Entity\Treasury\CashDeskHistory;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Partner\CustomerHistoryRepository;
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
class ValidateSaleSettlementController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             SaleSettlementRepository $saleSettlementRepository,
                             EntityManagerInterface $entityManager,
                             CashDeskRepository $cashDeskRepository,
                             CashDeskHistoryRepository $cashDeskHistoryRepository,
                             CustomerHistoryRepository $customerHistoryRepository,
                             PaymentMethodRepository $paymentMethodRepository,
                             BankHistoryRepository $bankHistoryRepository,
                             BankAccountRepository $bankAccountRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $saleSettlement = $saleSettlementRepository->find($id);
        if (!$saleSettlement){
            return new JsonResponse(['hydra:description' => 'This sale settlement is not found.'], 404);
        }

        $requestData = json_decode($request->getContent(), true);

        if (!$saleSettlement->getCustomer())
        {
            return new JsonResponse(['hydra:title' => 'Customer not found'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
        }

        $amount = $requestData['amountPay'];
        
        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $requestData['paymentMethod']);
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
        }
        elseif ($paymentMethod->isIsBank())
        {
            if (isset($requestData['bankAccount'])) {
                // START: Filter the uri to just take the id and pass it to our object
                $filter = preg_replace("/[^0-9]/", '', $requestData['bankAccount']);
                $filterId = intval($filter);
                $bankAccount = $bankAccountRepository->find($filterId);
                // END: Filter the uri to just take the id and pass it to our object

                if (!$bankAccount){
                    return new JsonResponse(['hydra:description' => 'Bank account not found !'], 404);
                }
            }
        }
        // END: Filter the uri to just take the id and pass it to our object

        if ($paymentMethod->isIsCashDesk())
        {
            // Write cash desk history
            $cashDeskHistory = new CashDeskHistory();

            $cashDeskHistoryRef = $cashDeskHistoryRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
            if (!$cashDeskHistoryRef){
                $reference = 'CASH/HIS/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
            }
            else{
                $filterNumber = preg_replace("/[^0-9]/", '', $cashDeskHistoryRef->getReference());
                $number = intval($filterNumber);

                // Utilisation de number_format() pour ajouter des zéros à gauche
                $reference = 'CASH/HIS/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
            }

            $cashDeskHistory->setCashDesk($userCashDesk);
            $cashDeskHistory->setReference($reference);
            $cashDeskHistory->setDescription($requestData['note']);
            $cashDeskHistory->setDebit($amount);
            $cashDeskHistory->setCredit(0);
            // balance : en bas
            $cashDeskHistory->setDateAt(new \DateTimeImmutable());

            $cashDeskHistory->setBranch($this->getUser()->getBranch());
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

            $saleSettlement->setCashDesk($userCashDesk);
        }

        if ($paymentMethod->isIsBank())
        {
            if (isset($requestData['bankAccount']))
            {
                // Write bank history
                $bankHistory = new BankHistory();

                $bankHistoryRef = $bankHistoryRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
                if (!$bankHistoryRef){
                    $reference = 'BNK/HIS/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
                }
                else{
                    $filterNumber = preg_replace("/[^0-9]/", '', $bankHistoryRef->getReference());
                    $number = intval($filterNumber);

                    // Utilisation de number_format() pour ajouter des zéros à gauche
                    $reference = 'BNK/HIS/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
                }

                $bankHistory->setBankAccount($bankAccount);
                $bankHistory->setReference($reference);
                $bankHistory->setDescription($requestData['note']);
                $bankHistory->setDebit($amount);
                $bankHistory->setCredit(0);
                // balance : en bas
                $bankHistory->setDateAt(new \DateTimeImmutable());

                $bankHistory->setBranch($this->getUser()->getBranch());
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

                $saleSettlement->setBankAccount($bankAccount);
            }
        }

        // Write customer history
        $customerHistory = new CustomerHistory();

        $customerHistoryRef = $customerHistoryRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$customerHistoryRef){
            $reference = 'CLT/HIS/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $customerHistoryRef->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $reference = 'CLT/HIS/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $customerHistory->setCustomer($saleSettlement->getCustomer());
        $customerHistory->setReference($reference);
        $customerHistory->setDescription($requestData['note']);
        $customerHistory->setDebit(0);
        $customerHistory->setCredit($amount);

        // Update customer history balance
        $customerHistories = $customerHistoryRepository->findBy(['customer' => $saleSettlement->getCustomer()]);

        $debit = 0; $credit = $amount;

        foreach ($customerHistories as $item)
        {
            $debit += $item->getDebit();
            $credit += $item->getCredit();
        }

        $balance = $debit - $credit;

        $customerHistory->setBalance($balance);

        $customerHistory->setUser($this->getUser());
        $customerHistory->setBranch($this->getUser()->getBranch());
        $customerHistory->setInstitution($this->getUser()->getInstitution());
        $customerHistory->setYear($this->getUser()->getCurrentYear());
        $entityManager->persist($customerHistory);
        
        // sale invoice update
        $saleSettlement->getInvoice()?->setAmountPaid($saleSettlement->getInvoice()->getAmountPaid() + $saleSettlement->getAmountPay());
        $saleSettlement->getInvoice()?->setBalance($saleSettlement->getInvoice()->getTtc() - $saleSettlement->getInvoice()->getAmountPaid());


        $saleSettlement->setStatus('settlement');
        $saleSettlement->setIsValidate(true);
        $saleSettlement->setValidateAt(new \DateTimeImmutable());
        $saleSettlement->setValidateBy($this->getUser());

        $saleSettlement->getCustomer()->setCredit($saleSettlement->getCustomer()->getCredit() + $amount);


        // Where we manage sale invoice item to slice payment per fee or item
        if (!$saleSettlement->isIsTreat())
        {
            // Verifier si le montant a regler est inferieur au panier
            $saleSettlementAmount = $amount;

            // $saleInvoiceItems form $saleInvoice;
            $saleInvoiceItems = $saleInvoiceItemRepository->findSaleInvoiceItemByPositionASC($saleSettlement->getInvoice());

            foreach ($saleInvoiceItems as $saleInvoiceItem)
            {
                $amount = $saleInvoiceItem->getAmountTtc();
                $amountPaid = $saleInvoiceItem->getAmountPaid();

                $balance = $amount - $amountPaid;

                // check if $balance is less than $saleSettlementAmount
                if ($balance < $saleSettlementAmount)
                {
                    // set amount Paid equal to amount Paid + $balance
                    $saleInvoiceItem->setAmountPaid($amountPaid + $balance);

                    // set balance to 0 because it is settle
                    $saleInvoiceItem->setBalance(0);

                    // set is Paid = true
                    $saleInvoiceItem->setIsTreat(true);

                    $saleSettlementAmount = $saleSettlementAmount - $balance;
                }
                elseif ($balance > $saleSettlementAmount)
                {
                    // check if $balance is greater than $saleSettlementAmount

                    // set amount Paid equal to amount Paid + $saleSettlementAmount
                    $saleInvoiceItem->setAmountPaid($amountPaid + $saleSettlementAmount);
                    $saleInvoiceItem->setBalance($balance - $saleSettlementAmount);

                    // set is Paid = false
                    $saleInvoiceItem->setIsTreat(false);

                    $saleSettlementAmount = 0;
                    // break;
                }
                elseif ($balance == $saleSettlementAmount)
                {
                    // check if $balance is equal to $saleSettlementAmount

                    // set amount Paid equal to amount Paid + $balance
                    $saleInvoiceItem->setAmountPaid($amountPaid + $balance);

                    // set balance to 0 because it is settle
                    $saleInvoiceItem->setBalance(0);

                    // set is Paid = true
                    $saleInvoiceItem->setIsTreat(true);

                    $saleSettlementAmount = 0;
                    // break;
                }

            }

            $saleSettlement->setIsTreat(true);
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

}
