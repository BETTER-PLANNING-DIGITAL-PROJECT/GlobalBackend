<?php

namespace App\Controller\Billing\Sale\Invoice;

use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Setting\Finance\PaymentMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class EditSaleInvoiceSettlementController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly EntityManagerInterface $manager,
                                private readonly PaymentMethodRepository $paymentMethodRepository,
    )
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             SaleSettlementRepository $saleSettlementRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $settlementData = json_decode($request->getContent(), true)['data'];
        $sdata = json_decode($request->getContent(), true);

        $settlement = $saleSettlementRepository->find($id);

        if(!($settlement instanceof SaleSettlement))
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This data must be type of settlement.'], 404);
        }

        if(!$settlement)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This settlement is not found.'], 404);
        }

        if ($settlement->getInvoice()){
            $oldSettlementAmount = $sdata['oldAmount'];
            $invoiceVirtualBalance = $settlement->getInvoice()->getVirtualBalance();
            $checkToSettle = $oldSettlementAmount + $invoiceVirtualBalance;

            if ($checkToSettle < $settlementData['amountPay']){

                return new JsonResponse(['hydra:description' => 'Amount pay can not be more than balance'], 404);
            }

            $settlement->getInvoice()->setVirtualBalance($settlement->getInvoice()->getVirtualBalance() + $oldSettlementAmount - $settlementData['amountPay']);
        }

        if ($settlement->getSaleReturnInvoice()){
            $oldSettlementAmount = $sdata['oldAmount'];
            $invoiceVirtualBalance = $settlement->getSaleReturnInvoice()->getVirtualBalance();
            $checkToSettle = $oldSettlementAmount + $invoiceVirtualBalance;

            if ($checkToSettle < $settlementData['amountPay']){

                return new JsonResponse(['hydra:description' => 'Amount pay can not be more than balance'], 404);
            }

            $settlement->getSaleReturnInvoice()->setVirtualBalance($settlement->getSaleReturnInvoice()->getVirtualBalance() + $oldSettlementAmount - $settlementData['amountPay']);
        }


        //$saleInvoice->setStudentRegistration($this->studentRegistrationRepository->find($this->getIdFromApiResourceId($invoiceData['studentRegistration'])));
        $settlement->setSettleAt(new \DateTimeImmutable($settlementData['settleAt']));
        $settlement->setAmountPay($settlementData['amountPay']);
        $settlement->setNote($settlementData['note']);

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $settlementData['paymentMethod']);
        $filterId = intval($filter);
        $paymentMethod = $this->paymentMethodRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        $settlement->setPaymentMethod($paymentMethod);


        // $settlement->setUser($this->getUser());
        // $settlement->setInstitution($this->getUser()->getInstitution());
        // $settlement->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($settlement);
        $this->manager->flush();


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
