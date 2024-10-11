<?php
namespace App\State\Security;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Security\Session\Year;
use App\Repository\Security\Session\YearRepository;
use Doctrine\ORM\EntityManagerInterface;

final class SetCurrentYearProcessor implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly YearRepository $yearRepository,
                                private readonly EntityManagerInterface $entityManager) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof Year){
            $currentYear = $this->yearRepository->findOneBy(['isCurrent' => true]);
            $currentYear?->setIsCurrent(false);

            $data->setIsCurrent(true);

            $this->entityManager->flush();
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }

}
