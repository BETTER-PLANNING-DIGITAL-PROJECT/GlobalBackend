<?php

namespace App\Controller\School\Schooling\Configuration;

use App\Repository\School\Schooling\Configuration\FeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
#[AsController]
class DeleteFeeController extends AbstractController
{
    public function __construct()
    {
    }

    public function __invoke(mixed $data, FeeRepository $feeRepository ,Request $request,
                              EntityManagerInterface $entityManager
                             )
    {
        $id = $request->get('id');
        $fee = $feeRepository->findOneBy(['id' => $id]);
        if (!$fee){
            return new JsonResponse(['hydra:description' => 'This fee is not found.'], 404);
        }

        /*$item = $entityManager->getRepository(Item::class)->findOneBy(['fee' => $fee]);
        if ($item){
            $entityManager->remove($item);
        }*/

        $entityManager->remove($fee);

        $entityManager->flush();

        return $data;
    }

}
