<?php

namespace App\Controller\Billing\Sale\School\Return;

use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceFeeRepository;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class DeleteSaleReturnInvoiceFeeController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SaleReturnInvoiceFeeRepository $saleReturnInvoiceFeeRepository,
                             SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');

        $saleReturnInvoiceFee = $saleReturnInvoiceFeeRepository->findOneBy(['id' => $id]);
        if (!$saleReturnInvoiceFee){
            return new JsonResponse(['hydra:description' => 'This invoice item '.$id.' is not found.'], 404);
        }

        $saleReturnInvoice = $saleReturnInvoiceFee->getSaleReturnInvoice();

        $entityManager->remove($saleReturnInvoiceFee);

        $entityManager->flush();

        // update sale return invoice
        $amount = $saleReturnInvoiceFeeRepository->saleReturnInvoiceHtAmount($saleReturnInvoice)[0][1];
        $saleReturnInvoice->setAmount($amount);

        $discountAmount = 0;
        $saleReturnInvoiceFees = $saleReturnInvoiceFeeRepository->findBy(['saleReturnInvoice' => $saleReturnInvoice]);
        foreach ($saleReturnInvoiceFees as $invoiceFee){
            $discountAmount += $invoiceFee->getDiscountAmount();
        }

        $amountTtc = $saleReturnInvoiceFeeRepository->saleReturnInvoiceHtAmount($saleReturnInvoice)[0][1] - $discountAmount;
        $saleReturnInvoice->setTtc($amountTtc);
        $saleReturnInvoice->setBalance($amountTtc);
        $saleReturnInvoice->setVirtualBalance($amountTtc);


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
