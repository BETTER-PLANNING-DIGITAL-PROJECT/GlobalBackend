<?php

namespace App\Controller\Billing\Purchase\Reception;

use App\Entity\Billing\Purchase\PurchaseInvoice;
use App\Entity\Inventory\Reception;
use App\Entity\Inventory\ReceptionItem;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceItemRepository;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\Repository\Inventory\ReceptionItemRepository;
use App\Repository\Inventory\ReceptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class CreatePurchaseInvoiceReceptionValidateController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                private readonly EntityManagerInterface $manager
    )
    {
    }

    public function __invoke(PurchaseInvoiceItemRepository $purchaseInvoiceItemRepository,
                             PurchaseInvoiceRepository $purchaseInvoiceRepository,
                             ReceptionRepository $receptionRepository,
                             ReceptionItemRepository $receptionItemRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $data = json_decode($request->getContent(), true);

        $purchaseInvoice = $purchaseInvoiceRepository->find($id);

        if(!($purchaseInvoice instanceof PurchaseInvoice))
        {
            return new JsonResponse(['hydra:title' => 'This data must be type of purchase invoice.'], 404);
        }

        $existingReception = $receptionRepository->findOneBy(['purchaseInvoice' => $purchaseInvoice]);
        if ($existingReception){
            return new JsonResponse(['hydra:title' => 'This purchase invoice already has reception on it.'], 500);
        }

        $generateReceptionUniqNumber = $receptionRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$generateReceptionUniqNumber){
            $uniqueNumber = 'WH/REC/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $generateReceptionUniqNumber->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $uniqueNumber = 'WH/REC/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $reception = new Reception();

        $reception->setPurchaseInvoice($purchaseInvoice);
        $reception->setContact($purchaseInvoice->getSupplier()->getContact());
        // shipping address
        // operation type
        // original document
        $reception->setReference($uniqueNumber);
        $reception->setOtherReference($purchaseInvoice->getInvoiceNumber());
        $reception->setOriginalDocument($purchaseInvoice->getInvoiceNumber());
        // serial number
        $reception->setReceiveAt(new \DateTimeImmutable());
        $reception->setDescription('purchase invoice reception validate');
        $reception->setIsValidate(true);
        $reception->setValidateAt(new \DateTimeImmutable());
        $reception->setValidateBy($this->getUser());
        $reception->setStatus('reception');
        $reception->setOtherStatus('reception');

        $reception->setIsEnable(true);
        $reception->setCreatedAt(new \DateTimeImmutable());
        $reception->setYear($this->getUser()->getCurrentYear());
        $reception->setUser($this->getUser());
        $reception->setBranch($this->getUser()->getBranch());
        $reception->setInstitution($this->getUser()->getInstitution());

        $entityManager->persist($reception);

        $purchaseInvoiceItems = $purchaseInvoiceItemRepository->findBy(['purchaseInvoice' => $purchaseInvoice]);
        if ($purchaseInvoiceItems)
        {
            foreach ($purchaseInvoiceItems as $purchaseInvoiceItem)
            {
                $receptionItem = new ReceptionItem();

                $receptionItem->setReception($reception);
                $receptionItem->setItem($purchaseInvoiceItem->getItem());
                $receptionItem->setQuantity($purchaseInvoiceItem->getQuantity());

                $receptionItem->setIsEnable(true);
                $receptionItem->setCreatedAt(new \DateTimeImmutable());
                $receptionItem->setYear($this->getUser()->getCurrentYear());
                $receptionItem->setUser($this->getUser());
                $receptionItem->setInstitution($this->getUser()->getInstitution());

                $this->manager->persist($receptionItem);
            }
        }

        // other invoice status update
        $purchaseInvoice->setOtherStatus('reception');

        $this->manager->flush();

        return $this->json(['hydra:member' => $reception]);
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