<?php
namespace App\State\Processor\Partner;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Partner\Contact;
use App\Entity\Security\User;
use App\Repository\Setting\Person\CivilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class CustomerContactProcessor implements ProcessorInterface
{

    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $tokenStorage,
                                private readonly Request $request,
                                private readonly EntityManagerInterface $manager,
                                private readonly CivilityRepository $civilityRepository) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $customerData = json_decode($this->request->getContent(), true);

        $contact = new Contact();

        $contact->setCode($customerData['code']);
        $contact->setName($customerData['name']);

        // $civility = $this->civilityRepository->find($this->getIdFromApiResourceId($customerData['civility']));
        // $contact->setCivility($civility);

        $contact->setPhone($customerData['phone']);
        $contact->setEmail($customerData['email']);
        $contact->setAddress($customerData['address']);

        $contact->setCustomer($data);

        $contact->setUser($this->getUser());
        $contact->setInstitution($this->getUser()->getInstitution());
        $contact->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($contact);
        $this->manager->flush();

        // data
        $data->setInstitution($this->getUser()->getInstitution());
        $data->setUser($this->getUser());
        $data->setYear($this->getUser()->getCurrentYear());

        return $this->processor->process($data, $operation, $uriVariables, $context);
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
