<?php

namespace App\Command;

use App\Entity\Partner\CustomerHistory;
use App\Entity\Security\User;
use App\Entity\Treasury\CashDeskHistory;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Partner\CustomerHistoryRepository;
use App\Repository\Treasury\CashDeskHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CheckPaymentStatusCommand extends Command
{
    public function __construct(private readonly SaleSettlementRepository $saleSettlementRepository,
                                private readonly EntityManagerInterface $manager,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly CashDeskHistoryRepository $cashDeskHistoryRepository,
                                private readonly CustomerHistoryRepository $customerHistoryRepository,
                                private readonly SaleInvoiceItemRepository $saleInvoiceItemRepository,
    )
    {
        parent::__construct();
    }

    protected static $defaultName = 'app:payment:status';
    // ...

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // $numberSent = 0;
        // $numberFailed = 0;

        date_default_timezone_set("Africa/Douala");


        // Avant meme de verifier quoique ce soit, il faut qu'on ait un token

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://mamonipay.me/api/auth/login");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = array(
            "user_name" => "betterplanning",
            "password" => "12345678"
        );

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json"
        ));

        $response = curl_exec($ch);

        if ($response === false) {
            //echo "Erreur cURL : " . curl_error($ch);
            curl_error($ch);
        } else {
            // var_dump($response);
        }

        curl_close($ch);


        $data = json_decode($response, true);

        $token = $data['data']['token'];

        if ($token){
            $saleSettlements = $this->saleSettlementRepository->findBy(['status' => 'PAYMENT_IN_PROGRESS']);
			echo 'total : '. count($saleSettlements);

            foreach ($saleSettlements as $saleSettlement)
            {
				//echo 'amount : '. $saleSettlement->getAmountPay();

                // Utilisez le token dans votre requête suivante
                $ch2 = curl_init();
                curl_setopt($ch2, CURLOPT_URL, "https://mamonipay.me/api/transaction/payment_status");
                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch2, CURLOPT_HEADER, FALSE);
                curl_setopt($ch2, CURLOPT_POST, TRUE);
                curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
				
				
				$transformedReference = str_replace('/', '-', $saleSettlement->getGatewayReference());

                $chaine = (string)$transformedReference;
                echo $chaine .'+++++';

                $data2 = array(
                    "gateway_reference" => $chaine
                );
                $json_data = json_encode($data2);

                curl_setopt($ch2, CURLOPT_POSTFIELDS, $json_data);
                curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                    "Authorization: Bearer $token"
                ));

                $response2 = curl_exec($ch2);

                if ($response2 === false) {
                    echo "Erreur cURL : " . curl_error($ch2);
                } else {
                    var_dump($response2);
                }

                curl_close($ch2);

                $data2 = json_decode($response2, true);
				
				if ($data2['success'] == true){
					
					
					if ($data2['data']['status'] == 'SUCCESSFUL'){

                    // Mise a jour du reglement si c'est un success

                    if ($saleSettlement->getPaymentGateway() && $saleSettlement->getCashDesk())
                    {
                        if (!$saleSettlement->getCashDesk()->isIsOpen())
                        {
                            return new JsonResponse(['hydra:title' => 'You cash desk is not open!'], Response::HTTP_BAD_REQUEST, ['Content-Type', 'application/json']);
                        }

                        // Write cash desk history
                        $cashDeskHistory = new CashDeskHistory();
                        $cashDeskHistory->setCashDesk($saleSettlement->getCashDesk());
                        $cashDeskHistory->setReference($saleSettlement->getReference());
                        $cashDeskHistory->setDescription('invoice settlement');
                        $cashDeskHistory->setDebit($saleSettlement->getAmountPay());
                        $cashDeskHistory->setCredit(0);
                        // balance : en bas
                        $cashDeskHistory->setDateAt(new \DateTimeImmutable());

                        $cashDeskHistory->setInstitution($saleSettlement->getInstitution());
                        $cashDeskHistory->setYear($saleSettlement->getYear());
                        //$cashDeskHistory->setInstitution($this->getUser()->getInstitution());
                        // $cashDeskHistory->setYear($this->getUser()->getCurrentYear());
                        $cashDeskHistory->setUser($this->getUser());
                        $this->manager->persist($cashDeskHistory);

                        // Update cash desk daily deposit balance
                        $saleSettlement->getCashDesk()->setDailyDeposit($saleSettlement->getCashDesk()->getDailyDeposit() + $saleSettlement->getAmountPay());

                        // Update cash desk balance
                        $cashDeskHistories = $this->cashDeskHistoryRepository->findBy(['cashDesk' => $saleSettlement->getCashDesk()]);

                        $debit = $saleSettlement->getAmountPay(); $credit = 0;

                        foreach ($cashDeskHistories as $item)
                        {
                            $debit += $item->getDebit();
                            $credit += $item->getCredit();
                        }

                        $balance = $debit - $credit;

                        $cashDeskHistory->setBalance($balance);
                        $saleSettlement->getCashDesk()->setBalance($balance);

                        $this->manager->flush();
                    }

                    // Customer history
                    $customerHistory = new CustomerHistory();
                    $customerHistory->setCustomer($saleSettlement->getCustomer());
                    $customerHistory->setReference($saleSettlement->getReference());
                    $customerHistory->setUser($this->getUser());
					
					$customerHistory->setInstitution($saleSettlement->getInstitution());
                    $customerHistory->setYear($saleSettlement->getYear());
                    //$customerHistory->setInstitution($this->getUser()->getInstitution());
                    //$customerHistory->setYear($this->getUser()->getCurrentYear());

                    // Update customer history balance
                    $customerHistories = $this->customerHistoryRepository->findBy(['customer' => $saleSettlement->getCustomer()]);

                    $debit = 0; $credit = $saleSettlement->getAmountPay();

                    foreach ($customerHistories as $item)
                    {
                        $debit += $item->getDebit();
                        $credit += $item->getCredit();
                    }

                    $balance = $credit - $debit;

                    $customerHistory->setBalance($balance);
                    $customerHistory->setCredit($saleSettlement->getAmountPay());
                    $customerHistory->setDebit(0);
                    $customerHistory->setDescription($saleSettlement->getNote(). '-'. 'settlement');
                    $this->manager->persist($customerHistory);

                    $saleSettlement->getInvoice()?->setAmountPaid($saleSettlement->getInvoice()->getAmountPaid() + $saleSettlement->getAmountPay());
                    $saleSettlement->getInvoice()?->setBalance($saleSettlement->getInvoice()->getTtc() - $saleSettlement->getInvoice()->getAmountPaid());

                    // $settlement->setIsTreat(false);

                    $saleSettlement->setIsValidate(true);
                    $saleSettlement->setValidateAt(new \DateTimeImmutable());
                    $saleSettlement->setValidateBy($this->getUser());

                    $saleSettlement->getCustomer()->setCredit($saleSettlement->getCustomer()->getCredit() + $saleSettlement->getAmountPay());


                    if (!$saleSettlement->isIsTreat())
                    {
                        // Verifier si le montant a regler est inferieur au panier
                        $settlementAmount = $saleSettlement->getAmountPay();

                        // $saleInvoiceItems form $saleInvoice;
                        $saleInvoiceItems = $this->saleInvoiceItemRepository->findSaleInvoiceItemByPositionASC($saleSettlement->getInvoice());

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

                        $saleSettlement->setIsTreat(true);
                    }





                    // Mise a jour du status
                    $saleSettlement->setStatus($data2['data']['status']);
                    $this->manager->flush();
                }
				else{
					$saleSettlement->setStatus($data2['data']['status']);
					$this->manager->flush();
				}
					
				}
				
				else{
					echo 'The transaction : '.$chaine.' is not found';
					// $saleSettlement->setStatus($data2['data']['status']);
				}
				

            }
			
			//$this->manager->flush();
        }


        return Command::SUCCESS;
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


