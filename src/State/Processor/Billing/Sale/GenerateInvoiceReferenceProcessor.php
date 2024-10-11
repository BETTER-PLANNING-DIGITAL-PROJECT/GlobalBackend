<?php
namespace App\State\Processor\Billing\Sale;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class GenerateInvoiceReferenceProcessor implements ProcessorInterface
{

    public function __construct(
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $invoiceData = json_decode($this->request->getContent(), true);
        $saleInvoice = new SaleInvoice();

        $uniqueNumber = $this->generer_numero_unique();
        $uniqueNumber = 'REF' . $uniqueNumber;

        $saleInvoice->setInvoiceNumber($uniqueNumber);
        $saleInvoice->setStatus('draft');
        $saleInvoice->setIsStandard($invoiceData['standard']);
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
    function generer_numero_unique() {
        // Génère un nombre aléatoire entre 10000 et 99999 (inclus)
        $numero_unique = rand(10000, 99999);
        return $numero_unique;
    }
}
