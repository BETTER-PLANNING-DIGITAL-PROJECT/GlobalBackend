<?php

namespace App\Controller\Billing\Sale\School\Invoice;

use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class DeleteSaleInvoiceFeeController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleInvoiceFeeRepository $saleInvoiceFeeRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $saleInvoiceFee = $saleInvoiceFeeRepository->findOneBy(['id' => $id]);
        if (!$saleInvoiceFee){
            return new JsonResponse(['hydra:description' => 'This invoice item '.$id.' is not found.'], 404);
        }

        $saleInvoice = $saleInvoiceFee->getSaleInvoice();

        $entityManager->remove($saleInvoiceFee);

        $entityManager->flush();

        // update sale invoice
        $amount = $saleInvoiceFeeRepository->saleInvoiceHtAmount($saleInvoice)[0][1];
        $saleInvoice->setAmount($amount);

        $discountAmount = 0;
        $saleInvoiceFees = $saleInvoiceFeeRepository->findBy(['saleInvoice' => $saleInvoice]);
        foreach ($saleInvoiceFees as $invoiceFee){
            $discountAmount += $invoiceFee->getDiscountAmount();
        }

        $amountTtc = $saleInvoiceFeeRepository->saleInvoiceHtAmount($saleInvoice)[0][1] - $discountAmount;
        $saleInvoice->setTtc($amountTtc);
        $saleInvoice->setBalance($amountTtc);
        $saleInvoice->setVirtualBalance($amountTtc);


        $entityManager->flush();

        return $this->json(['hydra:member' => 200]);
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
