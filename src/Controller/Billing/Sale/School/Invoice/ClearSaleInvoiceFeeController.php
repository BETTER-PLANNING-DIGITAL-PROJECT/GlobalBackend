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
class ClearSaleInvoiceFeeController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(SaleInvoiceFeeRepository $saleInvoiceFeeRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {
        $id = $request->get('id');

        $saleInvoice = $saleInvoiceRepository->findOneBy(['id' => $id]);
        if (!$saleInvoice){
            return new JsonResponse(['hydra:description' => 'Invoice '.$id.' not found.'], 404);
        }

        $saleInvoiceFees = $saleInvoiceFeeRepository->findBy(['saleInvoice' => $saleInvoice]);
        foreach ($saleInvoiceFees as $saleInvoiceFee)
        {
            // clear sale invoice item discount
//            $saleInvoiceFeeDiscounts = $saleInvoiceFeeDiscountRepository->findBy(['saleInvoiceFee' => $saleInvoiceFee]);
//            if ($saleInvoiceFeeDiscounts){
//                foreach ($saleInvoiceFeeDiscounts as $saleInvoiceFeeDiscount){
//                    $entityManager->remove($saleInvoiceFeeDiscount);
//                }
//            }


            $entityManager->remove($saleInvoiceFee);
        }

        // update sale invoice
        $saleInvoice->setAmount(0);
        $saleInvoice->setTtc(0);
        $saleInvoice->setBalance(0);
        $saleInvoice->setVirtualBalance(0);

        $entityManager->flush();

        return $this->json(['hydra:member' => $saleInvoice]);
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
