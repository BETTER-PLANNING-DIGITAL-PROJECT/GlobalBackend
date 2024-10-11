<?php

namespace App\Controller\Billing\Sale\School\Invoice;

use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Billing\Sale\SaleReturnInvoiceFee;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GenerateSchoolSaleReturnInvoiceController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleReturnInvoiceItemRepository $saleReturnInvoiceItemRepository,
                             SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             SaleInvoiceFeeRepository $saleInvoiceFeeRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $saleInvoice = $saleInvoiceRepository->find($id);

        if(!$saleInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This invoice is not found.'], 404);
        }

        if(!$saleInvoice instanceof SaleInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This data must be type of invoice.'], 404);
        }

        $existGeneratedInvoice = $saleReturnInvoiceRepository->findOneBy(['saleInvoice' => $saleInvoice]);
        if($existGeneratedInvoice)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'Invoice already generated.'], 500);
        }

        $saleReturnInvoice = new SaleReturnInvoice();

        $returnInvoice = $saleReturnInvoiceRepository->findOneBy([], ['id' => 'DESC']);
        if (!$returnInvoice){
            $uniqueNumber = 'SAL/RET/INV/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $returnInvoice->getInvoiceNumber());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'SAL/RET/INV/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        //$uniqueNumber = $this->generer_numero_unique();
        //$uniqueNumber = 'REF/RET' . $uniqueNumber;

        $saleReturnInvoice->setSaleInvoice($saleInvoice);

        $saleReturnInvoice->setInvoiceNumber($uniqueNumber);
        $saleReturnInvoice->setStudentRegistration($saleInvoice->getStudentRegistration());
        $saleReturnInvoice->setAmount($saleInvoice->getAmount());
        $saleReturnInvoice->setAmountPaid(0);
        $saleReturnInvoice->setShippingAddress($saleInvoice->getShippingAddress());
        $saleReturnInvoice->setTtc($saleInvoice->getTtc());
        $saleReturnInvoice->setBalance($saleInvoice->getAmountPaid());
        $saleReturnInvoice->setVirtualBalance($saleInvoice->getAmountPaid());
        $saleReturnInvoice->setStatus('draft');
        $saleReturnInvoice->setIsStandard($saleInvoice->isIsStandard());
        $saleReturnInvoice->setInvoiceAt(new \DateTimeImmutable());
        $saleReturnInvoice->setDeadLine($saleInvoice->getDeadLine());
        
        $saleReturnInvoice->setSchool($saleInvoice->getSchool());
        $saleReturnInvoice->setClass($saleInvoice->getClass());

        $saleReturnInvoice->setUser($this->getUser());

        $saleReturnInvoice->setBranch($this->getUser()->getBranch());
        $saleReturnInvoice->setInstitution($this->getUser()->getInstitution());
        $saleReturnInvoice->setYear($this->getUser()->getCurrentYear());

        $entityManager->persist($saleReturnInvoice);


        $saleInvoiceFees = $saleInvoiceFeeRepository->findBy(['saleInvoice' => $saleInvoice]);
        if ($saleInvoiceFees){
            foreach ($saleInvoiceFees as $saleInvoiceFee){

                $saleReturnInvoiceFee = new SaleReturnInvoiceFee();

                $saleReturnInvoiceFee->setFee($saleInvoiceFee->getFee());
                $saleReturnInvoiceFee->setQuantity($saleInvoiceFee->getQuantity());
                $saleReturnInvoiceFee->setPu($saleInvoiceFee->getPu());
                $saleReturnInvoiceFee->setDiscount($saleInvoiceFee->getDiscount());
                $saleReturnInvoiceFee->setSaleReturnInvoice($saleReturnInvoice);
                $saleReturnInvoiceFee->setName($saleInvoiceFee->getName());
                $saleReturnInvoiceFee->setAmount($saleInvoiceFee->getAmount());
                $saleReturnInvoiceFee->setDiscountAmount($saleInvoiceFee->getDiscountAmount());
                $saleReturnInvoiceFee->setAmountTtc($saleInvoiceFee->getAmountTtc());
                $saleReturnInvoiceFee->setAmountWithTaxes($saleInvoiceFee->getAmountWithTaxes());

                if ($saleInvoiceFee->getTaxes()){
                    foreach ($saleInvoiceFee->getTaxes() as $tax){
                        $saleReturnInvoiceFee->addTax($tax);
                    }
                }

                $saleReturnInvoiceFee->setUser($this->getUser());
                $saleReturnInvoiceFee->setInstitution($this->getUser()->getInstitution());
                $saleReturnInvoiceFee->setYear($this->getUser()->getCurrentYear());

                $entityManager->persist($saleReturnInvoiceFee);
                $entityManager->flush();
            }
        }



        $entityManager->flush();


        return $this->json(['hydra:member' => $saleReturnInvoice]);
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
