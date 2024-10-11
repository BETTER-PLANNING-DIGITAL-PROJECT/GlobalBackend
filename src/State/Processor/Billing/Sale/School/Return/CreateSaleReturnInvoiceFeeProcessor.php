<?php
namespace App\State\Processor\Billing\Sale\School\Return;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Billing\Sale\SaleReturnInvoiceFee;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use App\Repository\School\Schooling\Configuration\FeeRepository;
use App\Repository\Setting\Finance\TaxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class CreateSaleReturnInvoiceFeeProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly FeeRepository $feeRepository,
                                private readonly TaxRepository $taxRepository,
                                private readonly SaleReturnInvoiceFeeRepository $saleReturnInvoiceFeeRepository,
                                Private readonly SaleReturnInvoiceRepository $saleReturnInvoiceRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $invoiceData = json_decode($this->request->getContent(), true);

        $saleReturnInvoiceFee = new SaleReturnInvoiceFee();

        $saleReturnInvoice = $this->saleReturnInvoiceRepository->find($data->getId());

        if(!$data instanceof SaleReturnInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }

        $item = $this->feeRepository->find($this->getIdFromApiResourceId($invoiceData['item']));
        $saleReturnInvoiceFee->setFee($item);
        $saleReturnInvoiceFee->setName($item?->getName());
        $saleReturnInvoiceFee->setPu($item?->getAmount());
        if (isset($invoiceData['quantity'])){
            $saleReturnInvoiceFee->setQuantity($invoiceData['quantity']);
        }else{
            $saleReturnInvoiceFee->setQuantity(1);
        }
        $saleReturnInvoiceFee->setSaleReturnInvoice($saleReturnInvoice);

        if (isset($invoiceData['discount'])){
            $saleReturnInvoiceFee->setDiscount($invoiceData['discount']);
        }

        if (isset($invoiceData['taxes'])){
            foreach ($invoiceData['taxes'] as $tax){
                $taxObject = $this->taxRepository->find($this->getIdFromApiResourceId($tax));
                $saleReturnInvoiceFee->addTax($taxObject);
            }
        }

        $saleReturnInvoiceFee->setAmount($saleReturnInvoiceFee->getQuantity() * $saleReturnInvoiceFee->getPu());

        $discountAmount =  $saleReturnInvoiceFee->getAmount() * $saleReturnInvoiceFee->getDiscount() / 100;

        $saleReturnInvoiceFee->setDiscountAmount($discountAmount);
        $saleReturnInvoiceFee->setAmountTtc($saleReturnInvoiceFee->getAmount() - $discountAmount);

        $saleReturnInvoiceFee->setUser($this->getUser());
        $saleReturnInvoiceFee->setBranch($this->getUser()->getBranch());
        $saleReturnInvoiceFee->setInstitution($this->getUser()->getInstitution());
        $saleReturnInvoiceFee->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($saleReturnInvoiceFee);
        $this->manager->flush();


        // update sale invoice
        $amount = $this->saleReturnInvoiceFeeRepository->saleReturnInvoiceHtAmount($saleReturnInvoice)[0][1];
        $saleReturnInvoice->setAmount($amount);

        $totalDiscountAmount = 0;
        $saleReturnInvoiceFees = $this->saleReturnInvoiceFeeRepository->findBy(['saleReturnInvoice' => $saleReturnInvoice]);
        foreach ($saleReturnInvoiceFees as $invoiceFee){
            $totalDiscountAmount += $invoiceFee->getDiscountAmount();
        }

        $amountTtc = $this->saleReturnInvoiceFeeRepository->saleReturnInvoiceHtAmount($saleReturnInvoice)[0][1] - $totalDiscountAmount;
        $saleReturnInvoice->setTtc($amountTtc);
        $saleReturnInvoice->setBalance($amountTtc);
        $saleReturnInvoice->setVirtualBalance($amountTtc);

        $this->manager->flush();


        return $this->processor->process($saleReturnInvoiceFee, $operation, $uriVariables, $context);
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
