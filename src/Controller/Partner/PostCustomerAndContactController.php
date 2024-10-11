<?php

namespace App\Controller\Partner;

use App\Entity\Partner\Contact;
use App\Entity\Partner\Customer;
use App\Entity\Security\User;
use App\Repository\Partner\CustomerRepository;
use App\Repository\Partner\PartnerCategoryRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use App\Repository\Setting\Person\CivilityRepository;
use App\Repository\Treasury\BankAccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PostCustomerAndContactController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(mixed $data, Request $request, CustomerRepository $customerRepository, CivilityRepository $civilityRepository, PartnerCategoryRepository $partnerCategoryRepository, BankAccountRepository $bankAccountRepository, BranchRepository $branchRepository,
    SystemSettingsRepository $systemSettingsRepository)
    {
        $customerData = json_decode($request->getContent(), true);

        // $branch = !isset($customerData['branch']) ? null : $branchRepository->find($this->getIdFromApiResourceId($customerData['branch']));
        $systemSettings = $systemSettingsRepository->findOneBy([]);

        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                if (!isset($customerData['branch'])) {
                    return new JsonResponse(['hydra:description' => 'Branch not found!.'], 400);
                }
                $branch = $branchRepository->find($this->getIdFromApiResourceId($customerData['branch']));
            }
            else{
                $branch = $this->getUser()->getBranch();
            }
        }
        else{
            $branch = $this->getUser()->getBranch();
        }

        $code = $customerData['code'];
        $name = $customerData['name'];

        // Check for duplicates based on code within the same branch
        $duplicateCheckCode = $customerRepository->findOneBy(['code' => $code, 'branch' => $branch]);
        if ($duplicateCheckCode) {
            return new JsonResponse(['hydra:description' => 'This code already exists in this branch.'], 400);
        }

        // Check for duplicates based on name within the same branch
        $duplicateCheckName = $customerRepository->findOneBy(['name' => $name, 'branch' => $branch]);
        if ($duplicateCheckName) {
            return new JsonResponse(['hydra:description' => 'This name already exists in this branch.'], 400);
        }

        // contact
        $contact = new Contact();

        $contact->setCode($customerData['code']);
        $contact->setName($customerData['name']);
        $contact->setPhone($customerData['phone']);
        $contact->setEmail($customerData['email']);
        $contact->setAddress($customerData['address']);

        $contact->setUser($this->getUser());
        $contact->setInstitution($this->getUser()->getInstitution());
        $contact->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($contact);

        // Create a new item
        $newCustomer = new Customer();
        $newCustomer->setCode($customerData['code']);
        $newCustomer->setName($customerData['name']);
        $newCustomer->setPhone($customerData['phone']);
        $newCustomer->setEmail($customerData['email']);
        $newCustomer->setAddress($customerData['address']);
        $newCustomer->setPostbox($customerData['postbox']);
        $newCustomer->setTaxpayernumber($customerData['taxpayernumber']);
        $newCustomer->setBusinessnumber($customerData['businessnumber']);
        $newCustomer->setIdCard($customerData['idCard']);
        $new = new \DateTimeImmutable($customerData['expiredAt']);
        $newCustomer->setExpiredAt($new);
        $newCustomer->setIsTva($customerData['isTva']);
        $civility = !isset($customerData['civility']) ? null : $civilityRepository->find($this->getIdFromApiResourceId($customerData['civility']));
        $newCustomer->setCivility($civility);

        $partnerCategory = !isset($customerData['partnerCategory']) ? null : $partnerCategoryRepository->find($this->getIdFromApiResourceId($customerData['partnerCategory']));
        $newCustomer->setPartnerCategory($partnerCategory);

        $bankAccount = !isset($customerData['bankAccount']) ? null : $bankAccountRepository->find($this->getIdFromApiResourceId($customerData['bankAccount']));
        $newCustomer->setBankAccount($bankAccount);

        $newCustomer->setDebit(0);
        $newCustomer->setCredit(0);
        $newCustomer->setContact($contact);

        $newCustomer->setBranch($branch);
        $newCustomer->setInstitution($this->getUser()->getInstitution());
        $newCustomer->setUser($this->getUser());
        $newCustomer->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($newCustomer);
        // $customerRepository->save($newCustomer);

        $this->manager->flush();

        return $newCustomer;
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
