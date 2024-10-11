<?php
namespace App\State\Processor\Billing\Sale;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class GenerateSaleReturnInvoiceProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // $saleReturnInvoiceData = json_decode($this->request->getContent(), true);

        $saleReturnInvoice = new SaleReturnInvoice();

        $returnInvoice = $this->saleReturnInvoiceRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
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
        $saleReturnInvoice->setAmount(0);
        $saleReturnInvoice->setAmountPaid(0);
        $saleReturnInvoice->setTtc(0);
        $saleReturnInvoice->setBalance(0);
        $saleReturnInvoice->setVirtualBalance(0);
        $saleReturnInvoice->setStatus('draft');
        $saleReturnInvoice->setOtherStatus('draft');
        $saleReturnInvoice->setIsStandard(true);
        $saleReturnInvoice->setInvoiceAt(new \DateTimeImmutable());

        $saleReturnInvoice->setUser($this->getUser());
        $saleReturnInvoice->setInstitution($this->getUser()->getInstitution());
        $saleReturnInvoice->setBranch($this->getUser()->getBranch());
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
