<?php
namespace App\State\Processor\Billing\Sale\School\Return;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class GenerateSchoolSaleReturnInvoiceProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $invoiceData = json_decode($this->request->getContent(), true);
        $saleReturnInvoice = new SaleReturnInvoice();

        $returnInvoice = $this->saleReturnInvoiceRepository->findOneBy([], ['id' => 'DESC']);
        if (!$returnInvoice){
            $uniqueNumber = 'SAL/RET/INV/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $returnInvoice->getInvoiceNumber());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'SAL/RET/INV/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $saleReturnInvoice->setInvoiceNumber($uniqueNumber);
        $saleReturnInvoice->setStatus('draft');
        $saleReturnInvoice->setIsStandard($invoiceData['standard']);
        $saleReturnInvoice->setInvoiceAt(new \DateTimeImmutable());

        $saleReturnInvoice->setUser($this->getUser());

        $saleReturnInvoice->setBranch($this->getUser()->getBranch());
        $saleReturnInvoice->setInstitution($this->getUser()->getInstitution());
        $saleReturnInvoice->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($saleReturnInvoice);
        $this->manager->flush();


        return $saleReturnInvoice;
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
