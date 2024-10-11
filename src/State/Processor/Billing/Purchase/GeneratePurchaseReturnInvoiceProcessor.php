<?php
namespace App\State\Processor\Billing\Purchase;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Purchase\PurchaseReturnInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceRepository;
use App\Repository\Partner\SupplierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class GeneratePurchaseReturnInvoiceProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly PurchaseReturnInvoiceRepository $purchaseReturnInvoiceRepository,
                                private readonly SupplierRepository $supplierRepository,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // $purchaseReturnInvoiceData = json_decode($this->request->getContent(), true);

        $purchaseReturnInvoice = new PurchaseReturnInvoice();

        $returnInvoice = $this->purchaseReturnInvoiceRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$returnInvoice){
            $uniqueNumber = 'PUR/RET/INV/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $returnInvoice->getInvoiceNumber());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'PUR/RET/INV/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        // $supplier = $this->supplierRepository->findOneBy(['code' => 'DIVERS']);
        // $purchaseReturnInvoice->setSupplier($supplier);

        $purchaseReturnInvoice->setInvoiceNumber($uniqueNumber);
        $purchaseReturnInvoice->setAmount(0);
        $purchaseReturnInvoice->setAmountPaid(0);
        $purchaseReturnInvoice->setTtc(0);
        $purchaseReturnInvoice->setBalance(0);
        $purchaseReturnInvoice->setVirtualBalance(0);
        $purchaseReturnInvoice->setStatus('draft');
        $purchaseReturnInvoice->setOtherStatus('draft');
        $purchaseReturnInvoice->setInvoiceAt(new \DateTimeImmutable());

        $purchaseReturnInvoice->setUser($this->getUser());
        $purchaseReturnInvoice->setInstitution($this->getUser()->getInstitution());
        $purchaseReturnInvoice->setBranch($this->getUser()->getBranch());
        $purchaseReturnInvoice->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($purchaseReturnInvoice);
        $this->manager->flush();

        return $purchaseReturnInvoice;
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
