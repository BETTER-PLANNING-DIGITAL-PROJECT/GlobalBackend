<?php
namespace App\State\Processor\Billing\Purchase;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Purchase\PurchaseInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Partner\SupplierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PutPurchaseInvoiceProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly SupplierRepository $supplierRepository,
                                Private readonly PurchaseInvoiceRepository $purchaseInvoiceRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $purchaseInvoiceData = json_decode($this->request->getContent(), true);
        if(!$data instanceof PurchaseInvoice)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of purchase invoice.'], 404);
        }

        $purchaseInvoice = $this->purchaseInvoiceRepository->find($data->getId());
        if(!$purchaseInvoice)
        {
            return new JsonResponse(['hydra:description' => 'Purchase Invoice not found.'], 404);
        }

        if (!isset($purchaseInvoiceData['supplier']))
        {
            return new JsonResponse(['hydra:description' => 'Supplier not found!'], 404);
        }

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $purchaseInvoiceData['supplier']);
        $filterId = intval($filter);
        $supplier = $this->supplierRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object
        $purchaseInvoice->setSupplier($supplier);

        if (isset($purchaseInvoiceData['invoiceAt'])){
            $purchaseInvoice->setInvoiceAt(new \DateTimeImmutable($purchaseInvoiceData['invoiceAt']));
        }

        if (isset($purchaseInvoiceData['deadLine'])){
            $purchaseInvoice->setDeadLine(new \DateTimeImmutable($purchaseInvoiceData['deadLine']));
        }

        $this->manager->flush();

        return $this->processor->process($purchaseInvoice, $operation, $uriVariables, $context);
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