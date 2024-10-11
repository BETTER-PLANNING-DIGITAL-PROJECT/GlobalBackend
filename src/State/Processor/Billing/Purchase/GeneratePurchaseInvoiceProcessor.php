<?php
namespace App\State\Processor\Billing\Purchase;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Purchase\PurchaseInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Partner\SupplierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class GeneratePurchaseInvoiceProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly SupplierRepository $supplierRepository,
                                private readonly Request $request,
                                private readonly PurchaseInvoiceRepository $purchaseInvoiceRepository,
                                private readonly EntityManagerInterface $manager) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // $invoiceData = json_decode($this->request->getContent(), true);

        $purchaseInvoice = new PurchaseInvoice();

        $invoice = $this->purchaseInvoiceRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$invoice){
            $uniqueNumber = 'PUR/INV/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $invoice->getInvoiceNumber());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'PUR/INV/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $supplier = $this->supplierRepository->findOneBy(['code' => 'DIVERS']);
        $purchaseInvoice->setSupplier($supplier);

        $purchaseInvoice->setInvoiceNumber($uniqueNumber);
        $purchaseInvoice->setStatus('draft');
        $purchaseInvoice->setOtherStatus('draft');
        $purchaseInvoice->setInvoiceAt(new \DateTimeImmutable());

        $purchaseInvoice->setUser($this->getUser());
        $purchaseInvoice->setBranch($this->getUser()->getBranch());
        $purchaseInvoice->setInstitution($this->getUser()->getInstitution());
        $purchaseInvoice->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($purchaseInvoice);
        $this->manager->flush();

        return $purchaseInvoice;
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