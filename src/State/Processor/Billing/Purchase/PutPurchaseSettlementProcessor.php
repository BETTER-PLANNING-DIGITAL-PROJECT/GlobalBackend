<?php
namespace App\State\Processor\Billing\Purchase;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use App\Repository\Partner\SupplierRepository;
use App\Repository\Setting\Finance\PaymentMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PutPurchaseSettlementProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly SupplierRepository $supplierRepository,
                                private readonly PaymentMethodRepository $paymentMethodRepository,
                                Private readonly SaleSettlementRepository $saleSettlementRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $settlementData = json_decode($this->request->getContent(), true);

        if(!$data instanceof SaleSettlement)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This data must be type of settlement.'], 404);
        }

        $settlement = $this->saleSettlementRepository->find($data->getId());

        if(!$settlement)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This settlement is not found.'], 404);
        }

        if ($settlement->getInvoice()){
            $oldSettlementAmount = $settlement->getAmountPay();
            $invoiceVirtualBalance = $settlement->getInvoice()->getVirtualBalance();
            //dd($settlement);
            $checkToSettle = $oldSettlementAmount + $invoiceVirtualBalance;

             if ($checkToSettle < $settlementData['amountPay']){

                 return new JsonResponse(['hydra:description' => 'Amount pay can not be more than balance'], 404);
             }
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

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $settlementData['supplier']);
        $filterId = intval($filter);
        $supplier = $this->supplierRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        $settlement->setSupplier($supplier);

        $settlement->setPaymentMethod($paymentMethod);


        $settlement->setUser($this->getUser());
       // $settlement->setInstitution($this->getUser()->getInstitution());
        $settlement->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($settlement);
        $this->manager->flush();

        return $this->processor->process($settlement, $operation, $uriVariables, $context);
    }

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
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
