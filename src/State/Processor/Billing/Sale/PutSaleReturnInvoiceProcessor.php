<?php
namespace App\State\Processor\Billing\Sale;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use App\Repository\Partner\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PutSaleReturnInvoiceProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly CustomerRepository $customerRepository,
                                Private readonly SaleReturnInvoiceRepository $saleReturnInvoiceRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $saleReturnInvoiceData = json_decode($this->request->getContent(), true);

        if(!$data instanceof SaleReturnInvoice)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of return invoice.'], 404);
        }

        $saleReturnInvoice = $this->saleReturnInvoiceRepository->find($data->getId());
        if(!$saleReturnInvoice)
        {
            return new JsonResponse(['hydra:description' => 'Sale Return Invoice not found.'], 404);
        }

        if (!isset($saleReturnInvoiceData['customer'])){
            // $saleReturnInvoice->setCustomer($this->customerRepository->findOneBy(['code' => 'DIVERS']));
            return new JsonResponse(['hydra:description' => 'Customer not found.'], 404);
        }

        // START: Filter the uri to just take the id and pass it to our object
        $filter = preg_replace("/[^0-9]/", '', $saleReturnInvoiceData['customer']);
        $filterId = intval($filter);
        $customer = $this->customerRepository->find($filterId);
        // END: Filter the uri to just take the id and pass it to our object
        $saleReturnInvoice->setCustomer($customer);

        if (isset($saleReturnInvoiceData['invoiceAt'])){
            $saleReturnInvoice->setInvoiceAt(new \DateTimeImmutable($saleReturnInvoiceData['invoiceAt']));
        }

        if (isset($saleReturnInvoiceData['deadLine'])){
            $saleReturnInvoice->setDeadLine(new \DateTimeImmutable($saleReturnInvoiceData['deadLine']));
        }

        /*if (isset($saleReturnInvoiceData['paymentReference']) && $saleReturnInvoiceData['paymentReference']){
            $saleReturnInvoice->setPaymentReference($saleReturnInvoiceData['paymentReference']);
        }*/

        $this->manager->flush();

        return $this->processor->process($saleReturnInvoice, $operation, $uriVariables, $context);
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