<?php
namespace App\State\Processor\Billing\Sale\School;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Billing\Sale\SaleInvoiceFee;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\School\Schooling\Configuration\FeeRepository;
use App\Repository\Setting\Finance\TaxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class CreateSaleInvoiceFeeProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly FeeRepository $feeRepository,
                                private readonly TaxRepository $taxRepository,
                                private readonly SaleInvoiceFeeRepository $saleInvoiceFeeRepository,
                                private readonly SaleInvoiceRepository $saleInvoiceRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $invoiceData = json_decode($this->request->getContent(), true);

        $saleInvoiceFee = new SaleInvoiceFee();

        $saleInvoice = $this->saleInvoiceRepository->find($data->getId());

        if(!$data instanceof SaleInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }

        if (isset($invoiceData['quantity'])){

            if($invoiceData['quantity'] <= 0)
            {
                // Warning
                return new JsonResponse(['hydra:description' => 'Quantity must be positive value.'], 404);
            }

            $saleInvoiceFee->setQuantity($invoiceData['quantity']);
        }else{
            $saleInvoiceFee->setQuantity(1);
        }

        $fee = $this->feeRepository->find($this->getIdFromApiResourceId($invoiceData['item']));
        $saleInvoiceFee->setFee($fee);
        $saleInvoiceFee->setName($fee?->getName());
        $saleInvoiceFee->setPu($fee->getAmount());

        $saleInvoiceFee->setSaleInvoice($saleInvoice);

        if (isset($invoiceData['discount'])){
            if($invoiceData['discount'] <= 0)
            {
                // Warning
                return new JsonResponse(['hydra:description' => 'Quantity must be positive value.'], 404);
            }

            $saleInvoiceFee->setDiscount($invoiceData['discount']);
        }

        if (isset($invoiceData['taxes'])){
            foreach ($invoiceData['taxes'] as $tax){
                $taxObject = $this->taxRepository->find($this->getIdFromApiResourceId($tax));
                $saleInvoiceFee->addTax($taxObject);
            }
        }


        $saleInvoiceFee->setAmount($saleInvoiceFee->getQuantity() * $saleInvoiceFee->getPu());

        $discountAmount =  $saleInvoiceFee->getAmount() * $saleInvoiceFee->getDiscount() / 100;

        $saleInvoiceFee->setDiscountAmount($discountAmount);
        $saleInvoiceFee->setAmountTtc($saleInvoiceFee->getAmount() - $discountAmount);

        $saleInvoiceFee->setUser($this->getUser());
        $saleInvoiceFee->setBranch($this->getUser()->getBranch());
        $saleInvoiceFee->setInstitution($this->getUser()->getInstitution());
        $saleInvoiceFee->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($saleInvoiceFee);
        $this->manager->flush();

        // update sale invoice
        $amount = $this->saleInvoiceFeeRepository->saleInvoiceHtAmount($saleInvoice)[0][1];
        $saleInvoice->setAmount($amount);

        $totalDiscountAmount = 0;
        $saleInvoiceFees = $this->saleInvoiceFeeRepository->findBy(['saleInvoice' => $saleInvoice]);
        foreach ($saleInvoiceFees as $invoiceFee){
            $totalDiscountAmount += $invoiceFee->getDiscountAmount();
        }

        $amountTtc = $this->saleInvoiceFeeRepository->saleInvoiceHtAmount($saleInvoice)[0][1] - $totalDiscountAmount;
        $saleInvoice->setTtc($amountTtc);
        $saleInvoice->setBalance($amountTtc);
        $saleInvoice->setVirtualBalance($amountTtc);

        $this->manager->flush();

        return $this->processor->process($saleInvoiceFee, $operation, $uriVariables, $context);
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
