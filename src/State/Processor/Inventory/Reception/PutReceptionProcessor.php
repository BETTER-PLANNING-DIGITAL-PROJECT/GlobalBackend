<?php
namespace App\State\Processor\Inventory\Reception;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Inventory\Reception;
use App\Entity\Security\User;
use App\Repository\Inventory\ReceptionItemRepository;
use App\Repository\Inventory\ReceptionRepository;
use App\Repository\Partner\ContactRepository;
use App\Repository\Setting\Inventory\OperationTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PutReceptionProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly ContactRepository $contactRepository,
                                private readonly ReceptionItemRepository $receptionItemRepository,
                                private readonly OperationTypeRepository $operationTypeRepository,
                                Private readonly ReceptionRepository $receptionRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if(!$data instanceof Reception)
        {
            return new JsonResponse(['hydra:description' => 'This data must be type of reception.'], 404);
        }

        $reception = $this->receptionRepository->find($data->getId());
        if(!$reception)
        {
            return new JsonResponse(['hydra:description' => 'This reception is not found.'], 404);
        }

        $receptionData = json_decode($this->request->getContent(), true);

        if (isset($receptionData['contact'])){
            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $receptionData['contact']);
            $filterId = intval($filter);
            $contact = $this->contactRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $reception->setContact($contact);
        }

        $reception->setReceiveAt(new \DateTimeImmutable());

        /*if (isset($receptionData['reference'])){
            $reception->setReference($receptionData['reference']);
        }*/

        if (isset($receptionData['operationType'])){
            // START: Filter the uri to just take the id and pass it to our object
            $filter = preg_replace("/[^0-9]/", '', $receptionData['operationType']);
            $filterId = intval($filter);
            $operationType = $this->operationTypeRepository->find($filterId);
            // END: Filter the uri to just take the id and pass it to our object

            $reception->setOperationType($operationType);
        }

        $receptionItems = $this->receptionItemRepository->findBy(['reception' => $reception]);
        foreach ($receptionItems as $receptionItem){
            $receptionItem->setYear($reception->getYear());
        }

        $reception->setDescription($receptionData['description']);
        $reception->setOriginalDocument($receptionData['OriginalDocument']);

        $this->manager->flush();

        return $this->processor->process($reception, $operation, $uriVariables, $context);
    }

    public function getIdFromApiResourceId(string $apiId){
        $lastIndexOf = strrpos($apiId, '/');
        $id = substr($apiId, $lastIndexOf+1);
        return intval($id);
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
