<?php

namespace App\Controller\Partner;

use App\Entity\Security\User;
use App\Repository\Partner\CustomerRepository;
use App\Repository\Partner\PartnerCategoryRepository;
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
class PutCustomerController extends AbstractController
{
    public function __construct( private readonly TokenStorageInterface $tokenStorage,)
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
        if ($duplicateCheckCode && ($duplicateCheckCode != $data)) {
            return new JsonResponse(['hydra:description' => 'This code already exists in this branch.'], 400);
        }

        // Check for duplicates based on name within the same branch
        $duplicateCheckName = $customerRepository->findOneBy(['name' => $name, 'branch' => $branch]);
        if ($duplicateCheckName && ($duplicateCheckName != $data)) {
            return new JsonResponse(['hydra:description' => 'This name already exists in this branch.'], 400);
        }

        $data->setCode($customerData['code']);
        $data->setName($customerData['name']);
        $data->setPhone($customerData['phone']);
        $data->setEmail($customerData['email']);
        $data->setAddress($customerData['address']);
        $data->setPostbox($customerData['postbox']);
        $data->setTaxpayernumber($customerData['taxpayernumber']);
        $data->setBusinessnumber($customerData['businessnumber']);
        $data->setIdCard($customerData['idCard']);
        $new = new \DateTimeImmutable($customerData['expiredAt']);
        $data->setExpiredAt($new);
        $data->setIsTva($customerData['isTva']);
        $civility = !isset($customerData['civility']) ? null : $civilityRepository->find($this->getIdFromApiResourceId($customerData['civility']));
        $data->setCivility($civility);

        $partnerCategory = !isset($customerData['partnerCategory']) ? null : $partnerCategoryRepository->find($this->getIdFromApiResourceId($customerData['partnerCategory']));
        $data->setPartnerCategory($partnerCategory);

        $bankAccount = !isset($customerData['bankAccount']) ? null : $bankAccountRepository->find($this->getIdFromApiResourceId($customerData['bankAccount']));
        $data->setBankAccount($bankAccount);
        if($systemSettings) {
            if ($systemSettings->isIsBranches()) {
                $data->setBranch($branch);
            }
        }

        // contact
        $data->getContact()->setCode($customerData['code']);
        $data->getContact()->setName($customerData['name']);

        $data->getContact()->setPhone($customerData['phone']);
        $data->getContact()->setEmail($customerData['email']);
        $data->getContact()->setAddress($customerData['address']);

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
