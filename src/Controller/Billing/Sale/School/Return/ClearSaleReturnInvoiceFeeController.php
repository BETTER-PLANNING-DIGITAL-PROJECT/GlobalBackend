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
class ClearSaleReturnInvoiceFeeController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(SaleReturnInvoiceFeeRepository $saleReturnInvoiceFeeRepository,
                             SaleReturnInvoiceRepository $saleReturnInvoiceRepository,
                             EntityManagerInterface $entityManager,
                             Request $request): JsonResponse
    {
        $id = $request->get('id');

        $saleReturnInvoice = $saleReturnInvoiceRepository->findOneBy(['id' => $id]);
        if (!$saleReturnInvoice){
            return new JsonResponse(['hydra:description' => 'Invoice '.$id.' not found.'], 404);
        }

        $saleReturnInvoiceFees = $saleReturnInvoiceFeeRepository->findBy(['saleReturnInvoice' => $saleReturnInvoice]);
        foreach ($saleReturnInvoiceFees as $saleReturnInvoiceFee)
        {
            // clear sale invoice item discount
//            $saleInvoiceFeeDiscounts = $saleInvoiceFeeDiscountRepository->findBy(['saleInvoiceFee' => $saleInvoiceFee]);
//            if ($saleInvoiceFeeDiscounts){
//                foreach ($saleInvoiceFeeDiscounts as $saleInvoiceFeeDiscount){
//                    $entityManager->remove($saleInvoiceFeeDiscount);
//                }
//            }


            $entityManager->remove($saleReturnInvoiceFee);
        }

        // update sale invoice
        $saleReturnInvoice->setAmount(0);
        $saleReturnInvoice->setTtc(0);
        $saleReturnInvoice->setBalance(0);
        $saleReturnInvoice->setVirtualBalance(0);

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

}
