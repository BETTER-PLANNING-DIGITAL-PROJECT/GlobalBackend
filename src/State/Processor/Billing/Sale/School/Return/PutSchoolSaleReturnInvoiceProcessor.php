<?php
namespace App\State\Processor\Billing\Sale\School\Return;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PutSchoolSaleReturnInvoiceProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly SaleReturnInvoiceFeeRepository $saleReturnInvoiceFeeRepository,
                                Private readonly SaleReturnInvoiceRepository $saleReturnInvoiceRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $invoiceData = json_decode($this->request->getContent(), true);

        if(!$data instanceof SaleReturnInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This data must be type of invoice.'], 404);
        }

        $saleReturnInvoice = $this->saleReturnInvoiceRepository->find($data->getId());

        if(!$saleReturnInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }

        //$saleReturnInvoice->setStudentRegistration($this->studentRegistrationRepository->find($this->getIdFromApiResourceId($invoiceData['studentRegistration'])));
        $saleReturnInvoice->setInvoiceAt(new \DateTimeImmutable());
        if (isset($invoiceData['paymentReference']) && $invoiceData['paymentReference']){
            $saleReturnInvoice->setPaymentReference($invoiceData['paymentReference']);
        }

        $amount = $this->saleReturnInvoiceFeeRepository->saleReturnInvoiceHtAmount($saleReturnInvoice)[0][1];

        $saleReturnInvoice->setAmount($amount);

        $saleReturnInvoice->setUser($this->getUser());
        $saleReturnInvoice->setInstitution($this->getUser()->getInstitution());
        $saleReturnInvoice->setYear($this->getUser()->getCurrentYear());

        $taxResult = 0;
        $discountAmount = 0;
        // $saleReturnInvoiceItemsWithTaxes = $saleReturnInvoiceItemRepository->sumAmountWithTaxesByInvoice($invoice);
        $saleReturnInvoiceItems = $this->saleReturnInvoiceFeeRepository->findBy(['saleReturnInvoice' => $saleReturnInvoice]);

        foreach ($saleReturnInvoiceItems as $saleReturnInvoiceItem){
            if ($saleReturnInvoiceItem->getTaxes()){
                foreach ($saleReturnInvoiceItem->getTaxes() as $tax){
                    $taxResult = $taxResult + $saleReturnInvoiceItem->getAmount() * $tax->getRate() / 100;
                }
            }

            //
            //$amountWithTax = $taxResult
            $discountAmount = $discountAmount + $saleReturnInvoiceItem->getAmount() * $saleReturnInvoiceItem->getDiscount() / 100;
        }

        $amountTtc = $this->saleReturnInvoiceFeeRepository->saleReturnInvoiceHtAmount($saleReturnInvoice)[0][1] + $taxResult - $discountAmount;

        $saleReturnInvoice->setTtc($amountTtc);
        $saleReturnInvoice->setBalance($amountTtc);
        $saleReturnInvoice->setVirtualBalance($amountTtc);
        //$saleReturnInvoice->setBalance($saleReturnInvoice->getTtc() - $saleReturnInvoice->getAmountPaid());

        //$this->manager->persist($saleReturnInvoice);
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
