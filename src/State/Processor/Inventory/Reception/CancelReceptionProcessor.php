<?php
namespace App\State\Processor\Inventory\Reception;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Inventory\Reception;
use App\Repository\Inventory\ReceptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CancelReceptionProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly EntityManagerInterface $manager,
                                Private readonly ReceptionRepository $receptionRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        if(!$data instanceof Reception)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This data must be type of reception.'], 404);
        }

        $reception = $this->receptionRepository->find($data->getId());
        if(!$reception)
        {
            // Warning
            return new JsonResponse(['hydra:description' => 'This reception is not found.'], 404);
        }

        $reception->setStatus('draft');
        $this->manager->flush();

        return $this->processor->process($reception, $operation, $uriVariables, $context);
    }
}
