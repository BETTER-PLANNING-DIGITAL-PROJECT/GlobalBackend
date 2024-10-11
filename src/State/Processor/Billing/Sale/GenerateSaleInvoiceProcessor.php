<?php
namespace App\State\Processor\Billing\Sale;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Partner\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class GenerateSaleInvoiceProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly SaleInvoiceRepository $saleInvoiceRepository,
                                private readonly CustomerRepository $customerRepository,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // $saleInvoiceData = json_decode($this->request->getContent(), true);

        $saleInvoice = new SaleInvoice();

        $invoice = $this->saleInvoiceRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$invoice){
            $uniqueNumber = 'SAL/INV/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $invoice->getInvoiceNumber());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'SAL/INV/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $customer = $this->customerRepository->findOneBy(['code' => 'DIVERS']);
        $saleInvoice->setCustomer($customer);

        $saleInvoice->setInvoiceNumber($uniqueNumber);
        $saleInvoice->setStatus('draft');
        $saleInvoice->setOtherStatus('draft');
        $saleInvoice->setIsStandard(true);
        $saleInvoice->setInvoiceAt(new \DateTimeImmutable());

        $saleInvoice->setUser($this->getUser());
        $saleInvoice->setBranch($this->getUser()->getBranch());
        $saleInvoice->setInstitution($this->getUser()->getInstitution());
        $saleInvoice->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($saleInvoice);
        $this->manager->flush();

        return $saleInvoice;
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
