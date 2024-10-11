<?php
namespace App\State\Processor\Billing\Sale\School;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PutSchoolSaleInvoiceProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly StudentRegistrationRepository $studentRegistrationRepository,
                                private readonly SaleInvoiceFeeRepository $saleInvoiceFeeRepository,
                                Private readonly SaleInvoiceRepository $saleInvoiceRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $invoiceData = json_decode($this->request->getContent(), true);

        if(!$data instanceof SaleInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This data must be type of invoice.'], 404);
        }

        $saleInvoice = $this->saleInvoiceRepository->find($data->getId());
        if(!$saleInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'Invoice not found.'], 404);
        }

        if (isset($invoiceData['invoiceAt'])){
            $saleInvoice->setInvoiceAt(new \DateTimeImmutable($invoiceData['invoiceAt']));
        }

        if (isset($invoiceData['deadLine'])){
            $saleInvoice->setDeadLine(new \DateTimeImmutable($invoiceData['deadLine']));
        }

//        if (isset($invoiceData['paymentReference'])){
//            $saleInvoice->setPaymentReference($invoiceData['paymentReference']);
//        }

        if (isset($invoiceData['studentRegistration'])){
            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $invoiceData['studentRegistration']);
            $filterId = intval($filter);
            //$customer = $this->customerRepository->find($filterId);
            $studentRegistration = $this->studentRegistrationRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            //$saleInvoice->setCustomer($customer);
            $saleInvoice->setStudentRegistration($studentRegistration);
        }

        $amount = $this->saleInvoiceFeeRepository->saleInvoiceHtAmount($saleInvoice)[0][1];

        $taxResult = 0;
        $discountAmount = 0;

        $saleInvoiceFees = $this->saleInvoiceFeeRepository->findBy(['saleInvoice' => $saleInvoice]);
        foreach ($saleInvoiceFees as $saleInvoiceFee){
            if ($saleInvoiceFee->getTaxes()){
                foreach ($saleInvoiceFee->getTaxes() as $tax){
                    $taxResult = $taxResult + $saleInvoiceFee->getAmount() * $tax->getRate() / 100;
                }
            }

            // $saleInvoiceFee->setCustomer($saleInvoice->getCustomer());
            $saleInvoiceFee->setStudentRegistration($saleInvoice->getStudentRegistration());
            $saleInvoiceFee->setClass($saleInvoice->getClass());
            $saleInvoiceFee->setSchool($saleInvoice->getSchool());
            $saleInvoiceFee->setYear($saleInvoice->getYear());

            $discountAmount = $discountAmount + $saleInvoiceFee->getAmount() * $saleInvoiceFee->getDiscount() / 100;
        }

        $amountTtc = $this->saleInvoiceFeeRepository->saleInvoiceHtAmount($saleInvoice)[0][1] + $taxResult - $discountAmount;

        $saleInvoice->setAmount($amount);
        $saleInvoice->setTtc($amountTtc);
        $saleInvoice->setBalance($amountTtc);
        $saleInvoice->setVirtualBalance($amountTtc);

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
