<?php
namespace App\State\Processor\Billing\Purchase;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Purchase\PurchaseReturnInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceRepository;
use App\Repository\Partner\SupplierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PutPurchaseReturnInvoiceProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly SupplierRepository $supplierRepository,
                                Private readonly PurchaseReturnInvoiceRepository $purchaseReturnInvoiceRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $purchaseReturnInvoiceData = json_decode($this->request->getContent(), true);

        if(!$data instanceof PurchaseReturnInvoice)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of return invoice.'], 404);
        }

        $purchaseReturnInvoice = $this->purchaseReturnInvoiceRepository->find($data->getId());
        if(!$purchaseReturnInvoice)
        {
            return new JsonResponse(['hydra:description' => 'Return Invoice not found.'], 404);
        }

        if (!isset($purchaseReturnInvoiceData['supplier'])){
            // $purchaseReturnInvoice->setSupplier($this->supplierRepository->findOneBy(['code' => 'DIVERS']));
            return new JsonResponse(['hydra:description' => 'Supplier not found.'], 404);
        }

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $purchaseReturnInvoiceData['supplier']);
        $filterId = intval($filter);
        $supplier = $this->supplierRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object
        $purchaseReturnInvoice->setSupplier($supplier);

        if (isset($purchaseReturnInvoiceData['invoiceAt'])){
            $purchaseReturnInvoice->setInvoiceAt(new \DateTimeImmutable($purchaseReturnInvoiceData['invoiceAt']));
        }

        if (isset($purchaseReturnInvoiceData['deadLine'])){
            $purchaseReturnInvoice->setDeadLine(new \DateTimeImmutable($purchaseReturnInvoiceData['deadLine']));
        }

        if (isset($purchaseReturnInvoiceData['paymentReference']) && $purchaseReturnInvoiceData['paymentReference']){
            $purchaseReturnInvoice->setPaymentReference($purchaseReturnInvoiceData['paymentReference']);
        }

        $this->manager->flush();

        return $this->processor->process($purchaseReturnInvoice, $operation, $uriVariables, $context);
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