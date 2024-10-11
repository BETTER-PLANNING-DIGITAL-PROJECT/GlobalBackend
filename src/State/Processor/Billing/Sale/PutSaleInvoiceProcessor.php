<?php
namespace App\State\Processor\Billing\Sale;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Partner\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PutSaleInvoiceProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly CustomerRepository $customerRepository,
                                Private readonly SaleInvoiceRepository $saleInvoiceRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $saleInvoiceData = json_decode($this->request->getContent(), true);
        if(!$data instanceof SaleInvoice)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of sale invoice.'], 404);
        }

        $saleInvoice = $this->saleInvoiceRepository->find($data->getId());
        if(!$saleInvoice)
        {
            return new JsonResponse(['hydra:description' => 'Sale Invoice not found.'], 404);
        }

        if (!isset($saleInvoiceData['customer']))
        {
            return new JsonResponse(['hydra:description' => 'Customer not found!'], 404);
        }

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $saleInvoiceData['customer']);
        $filterId = intval($filter);
        $customer = $this->customerRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object
        $saleInvoice->setCustomer($customer);

        if (isset($saleInvoiceData['invoiceAt'])){
            $saleInvoice->setInvoiceAt(new \DateTimeImmutable($saleInvoiceData['invoiceAt']));
        }

        if (isset($saleInvoiceData['deadLine'])){
            $saleInvoice->setDeadLine(new \DateTimeImmutable($saleInvoiceData['deadLine']));
        }

        $this->manager->flush();

        return $this->processor->process($saleInvoice, $operation, $uriVariables, $context);
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