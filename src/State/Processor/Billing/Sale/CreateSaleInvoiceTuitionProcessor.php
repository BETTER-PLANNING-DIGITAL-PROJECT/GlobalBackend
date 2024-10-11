<?php
namespace App\State\Processor\Billing\Sale;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Billing\Sale\SaleInvoiceItem;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class CreateSaleInvoiceTuitionProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                //private readonly TuitionRepository $tuitionRepository,
                                Private readonly SaleInvoiceRepository $saleInvoiceRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $invoiceData = json_decode($this->request->getContent(), true);

        $saleInvoiceItem = new SaleInvoiceItem();

        $saleInvoice = $this->saleInvoiceRepository->find($data->getId());

        if(!$data instanceof SaleInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }
//
//        $tuition = $this->tuitionRepository->find($this->getIdFromApiResourceId($invoiceData['tuition']));
//        //$saleInvoiceItem->setTuition($tuition);
//        $saleInvoiceItem->setName($tuition->getCostArea()->getName());
//        $saleInvoiceItem->setPu($tuition->getRegistrationFees());
//        $saleInvoiceItem->setQuantity(1);
//        $saleInvoiceItem->setSaleInvoice($saleInvoice);


        if (isset($invoiceData['taxes'])){
            foreach ($invoiceData['taxes'] as $tax){
                $taxObject = $this->taxRepository->find($this->getIdFromApiResourceId($tax));
                $saleInvoiceItem->addTax($taxObject);
            }
            //$taxForm = $this->taxRepository->find($this->getIdFromApiResourceId($invoiceData['taxes']));
            //$saleInvoiceItem->addTax($taxForm);
        }

        $saleInvoiceItem->setAmount($saleInvoiceItem->getQuantity() * $saleInvoiceItem->getPu());

        $saleInvoiceItem->setUser($this->getUser());
        $saleInvoiceItem->setInstitution($this->getUser()->getInstitution());
        $saleInvoiceItem->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($saleInvoiceItem);
        $this->manager->flush();

        return $this->processor->process($saleInvoiceItem, $operation, $uriVariables, $context);
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
