<?php

namespace App\Controller\Partner;

use App\Entity\Security\User;
use App\Repository\Partner\PartnerCategoryRepository;
use App\Repository\Partner\SupplierRepository;
use App\Repository\Security\Institution\BranchRepository;
use App\Repository\Security\SystemSettingsRepository;
use App\Repository\Setting\Person\CivilityRepository;
use App\Repository\Treasury\BankAccountRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class PutSupplierController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
    {
    }

    public function __invoke(mixed $data, Request $request, SupplierRepository $supplierRepository, CivilityRepository $civilityRepository, PartnerCategoryRepository $partnerCategoryRepository, BankAccountRepository $bankAccountRepository, BranchRepository $branchRepository,
                             SystemSettingsRepository $systemSettingsRepository)
    {
        $supplierData = json_decode($request->getContent(), true);

        $code = $supplierData['code'];
        $name = $supplierData['name'];

        // Check for duplicates based on code within the same branch
        $duplicateCheckCode = $supplierRepository->findOneBy(['code' => $code]);
        if ($duplicateCheckCode && ($duplicateCheckCode != $data)) {
            return new JsonResponse(['hydra:description' => 'This code already exists in this branch.'], 400);
        }

        // Check for duplicates based on name within the same branch
        $duplicateCheckName = $supplierRepository->findOneBy(['name' => $name]);
        if ($duplicateCheckName && ($duplicateCheckName != $data)) {
            return new JsonResponse(['hydra:description' => 'This name already exists in this branch.'], 400);
        }

        $data->setCode($supplierData['code']);
        $data->setName($supplierData['name']);
        $data->setPhone($supplierData['phone']);
        $data->setEmail($supplierData['email']);
        $data->setAddress($supplierData['address']);
        $data->setPostbox($supplierData['postbox']);
        $data->setTaxpayernumber($supplierData['taxpayernumber']);
        $data->setBusinessnumber($supplierData['businessnumber']);
        $data->setIdCard($supplierData['idCard']);
        $new = new \DateTimeImmutable($supplierData['expiredAt']);
        $data->setExpiredAt($new);
        $data->setIsTva($supplierData['isTva']);
        $civility = !isset($supplierData['civility']) ? null : $civilityRepository->find($this->getIdFromApiResourceId($supplierData['civility']));
        $data->setCivility($civility);
        $bankAccount = !isset($supplierData['bankAccount']) ? null : $bankAccountRepository->find($this->getIdFromApiResourceId($supplierData['bankAccount']));
        $data->setBankAccount($bankAccount);

        // contact
        $data->getContact()->setCode($supplierData['code']);
        $data->getContact()->setName($supplierData['name']);

        $data->getContact()->setPhone($supplierData['phone']);
        $data->getContact()->setEmail($supplierData['email']);
        $data->getContact()->setAddress($supplierData['address']);

        return $data;
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
