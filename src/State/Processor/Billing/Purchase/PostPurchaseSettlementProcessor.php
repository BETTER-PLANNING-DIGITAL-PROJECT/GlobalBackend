<?php
namespace App\State\Processor\Billing\Purchase;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Purchase\PurchaseSettlement;
use App\Entity\Billing\Sale\SaleSettlement;
use App\Entity\Security\User;
use App\Repository\Partner\SupplierRepository;
use App\Repository\Setting\Finance\PaymentMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PostPurchaseSettlementProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly SupplierRepository $supplierRepository,
                                private readonly PaymentMethodRepository $paymentMethodRepository,
                                private readonly EntityManagerInterface $manager) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $invoiceData = json_decode($this->request->getContent(), true);
        $settlement = new PurchaseSettlement();

        $reference = $this->generer_numero_unique();
        $reference = 'SET' . $reference;
        $settlement->setReference($reference);
        $settlement->setNote($invoiceData['note']);
        $settlement->setAmountPay($invoiceData['amountPay']);
        $settlement->setSettleAt(new \DateTimeImmutable($invoiceData['settleAt']));

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $invoiceData['paymentMethod']);
        $filterId = intval($filter);
        $paymentMethod = $this->paymentMethodRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $invoiceData['supplier']);
        $filterId = intval($filter);
        $supplier = $this->supplierRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object

        $settlement->setPaymentMethod($paymentMethod);
        $settlement->setSupplier($supplier);

        $settlement->setUser($this->getUser());

        //$settlement->setInstitution($this->getUser()->getInstitution());
        $settlement->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($settlement);
        $this->manager->flush();


        return $settlement;
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
