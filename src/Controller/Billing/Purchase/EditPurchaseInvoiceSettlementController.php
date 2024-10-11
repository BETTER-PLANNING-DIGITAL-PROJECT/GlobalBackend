<?php

namespace App\Controller\Billing\Purchase;

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
class EditPurchaseInvoiceSettlementController extends AbstractController
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

        $supplierSettlementData = json_decode($request->getContent(), true)['data'];
        $sdata = json_decode($request->getContent(), true);

        $supplierSettlement = $saleSettlementRepository->find($id);

        if(!($supplierSettlement instanceof SaleSettlement))
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This data must be type of settlement.'], 404);
        }

        if(!$supplierSettlement)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This settlement is not found.'], 404);
        }

        if ($supplierSettlement->getInvoice()){
            $oldSettlementAmount = $sdata['oldAmount'];
            $invoiceVirtualBalance = $supplierSettlement->getInvoice()->getVirtualBalance();
            $checkToSettle = $oldSettlementAmount + $invoiceVirtualBalance;

            if ($checkToSettle < $supplierSettlementData['amountPay']){

                return new JsonResponse(['hydra:description' => 'Amount pay can not be more than balance'], 404);
            }

            $supplierSettlement->getInvoice()->setVirtualBalance($supplierSettlement->getInvoice()->getVirtualBalance() + $oldSettlementAmount - $supplierSettlementData['amountPay']);
        }

        $supplierSettlement->setSettleAt(new \DateTimeImmutable($supplierSettlementData['settleAt']));
        $supplierSettlement->setAmountPay($supplierSettlementData['amountPay']);
        $supplierSettlement->setNote($supplierSettlementData['note']);

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $supplierSettlementData['paymentMethod']);
        $filterId = intval($filter);
        $paymentMethod = $this->paymentMethodRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        $supplierSettlement->setPaymentMethod($paymentMethod);


        $supplierSettlement->setUser($this->getUser());
        // $supplierSettlement->setInstitution($this->getUser()->getInstitution());
        $supplierSettlement->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($supplierSettlement);
        $this->manager->flush();


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

}
