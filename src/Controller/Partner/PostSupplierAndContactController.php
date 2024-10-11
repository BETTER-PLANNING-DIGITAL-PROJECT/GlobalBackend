<?php

namespace App\Controller\Partner;

use App\Entity\Partner\Contact;
use App\Entity\Partner\Supplier;
use App\Entity\Security\User;
use App\Repository\Partner\PartnerCategoryRepository;
use App\Repository\Partner\SupplierRepository;
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
class PostSupplierAndContactController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage, private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(mixed $data, Request $request, SupplierRepository $supplierRepository, CivilityRepository $civilityRepository, PartnerCategoryRepository $partnerCategoryRepository, BankAccountRepository $bankAccountRepository, BranchRepository $branchRepository,
    SystemSettingsRepository $systemSettingsRepository)
    {
        $supplierData = json_decode($request->getContent(), true);

        /*$systemSettings = $systemSettingsRepository->findOneBy([]);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                if (!isset($supplierData['branch'])) {
                    return new JsonResponse(['hydra:description' => 'Branch not found!.'], 400);
                }
                $branch = $branchRepository->find($this->getIdFromApiResourceId($supplierData['branch']));
            }
            else{
                $branch = $this->getUser()->getBranch();
            }
        }
        else{
            $branch = $this->getUser()->getBranch();
        }*/

        $code = $supplierData['code'];
        $name = $supplierData['name'];

        // Check for duplicates based on code within the same branch
        $duplicateCheckCode = $supplierRepository->findOneBy(['code' => $code]);
        if ($duplicateCheckCode) {
            return new JsonResponse(['hydra:description' => 'This code already exists.'], 400);
        }

        // Check for duplicates based on name within the same branch
        $duplicateCheckName = $supplierRepository->findOneBy(['name' => $name]);
        if ($duplicateCheckName) {
            return new JsonResponse(['hydra:description' => 'This name already exists.'], 400);
        }

        // contact
        $contact = new Contact();

        $contact->setCode($supplierData['code']);
        $contact->setName($supplierData['name']);

        $contact->setPhone($supplierData['phone']);
        $contact->setEmail($supplierData['email']);
        $contact->setAddress($supplierData['address']);

        $contact->setUser($this->getUser());
        $contact->setInstitution($this->getUser()->getInstitution());
        $contact->setYear($this->getUser()->getCurrentYear());

        $this->manager->persist($contact);

        // Create a new item
        $newSupplier = new Supplier();
        $newSupplier->setCode($supplierData['code']);
        $newSupplier->setName($supplierData['name']);
        $newSupplier->setPhone($supplierData['phone']);
        $newSupplier->setEmail($supplierData['email']);
        $newSupplier->setAddress($supplierData['address']);
        $newSupplier->setPostbox($supplierData['postbox']);
        $newSupplier->setTaxpayernumber($supplierData['taxpayernumber']);
        $newSupplier->setBusinessnumber($supplierData['businessnumber']);
        $newSupplier->setIdCard($supplierData['idCard']);
        $new = new \DateTimeImmutable($supplierData['expiredAt']);
        $newSupplier->setExpiredAt($new);
        $newSupplier->setIsTva($supplierData['isTva']);
        $civility = !isset($supplierData['civility']) ? null : $civilityRepository->find($this->getIdFromApiResourceId($supplierData['civility']));
        $newSupplier->setCivility($civility);
        $bankAccount = !isset($supplierData['bankAccount']) ? null : $bankAccountRepository->find($this->getIdFromApiResourceId($supplierData['bankAccount']));
        $newSupplier->setBankAccount($bankAccount);

        $newSupplier->setDebit(0);
        $newSupplier->setCredit(0);

        $newSupplier->setContact($contact);
        $newSupplier->setBranch($this->getUser()->getBranch());
        $newSupplier->setInstitution($this->getUser()->getInstitution());
        $newSupplier->setUser($this->getUser());
        $newSupplier->setYear($this->getUser()->getCurrentYear());

        // $supplierRepository->save($newSupplier);
        $this->manager->persist($newSupplier);

        $this->manager->flush();

        return $newSupplier;
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
