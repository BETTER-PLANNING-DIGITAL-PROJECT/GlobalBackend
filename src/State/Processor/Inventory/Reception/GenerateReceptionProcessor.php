<?php
namespace App\State\Processor\Inventory\Reception;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Inventory\Reception;
use App\Entity\Security\User;
use App\Repository\Inventory\ReceptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class GenerateReceptionProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly ReceptionRepository $receptionRepository,
                                private readonly EntityManagerInterface $manager) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        $reception = new Reception();

        $generateReceptionUniqNumber = $this->receptionRepository->findOneBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);
        if (!$generateReceptionUniqNumber){
            $reference = 'WH/REC/' . str_pad( 1, 5, '0', STR_PAD_LEFT);
        }
        else{
            $filterNumber = preg_replace("/[^0-9]/", '', $generateReceptionUniqNumber->getReference());
            $number = intval($filterNumber);

            // Utilisation de number_format() pour ajouter des zéros à gauche
            $reference = 'WH/REC/' . str_pad($number + 1, 5, '0', STR_PAD_LEFT);
        }

        $reception->setReference($reference);
        $reception->setReceiveAt(new \DateTimeImmutable());
        $reception->setStatus('draft');
        $reception->setOtherStatus('draft');

        $reception->setBranch($this->getUser()->getBranch());
        $reception->setUser($this->getUser());
        $reception->setInstitution($this->getUser()->getInstitution());
        $reception->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($reception);
        $this->manager->flush();

        return $reception;
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
