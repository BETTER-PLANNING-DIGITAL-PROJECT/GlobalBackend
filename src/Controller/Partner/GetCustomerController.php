<?php

namespace App\Controller\Partner;

use App\Entity\Security\User;
use App\Repository\Partner\CustomerRepository;
use App\Repository\Security\SystemSettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetCustomerController extends AbstractController
{
    private CustomerRepository $customerRepository;
    private SystemSettingsRepository $systemSettingsRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                CustomerRepository                     $customerRepository,
                                SystemSettingsRepository                     $systemSettingsRepository,
    )
    {
        $this->customerRepository = $customerRepository;
        $this->systemSettingsRepository = $systemSettingsRepository;
    }

    public function __invoke(Request $request):JsonResponse
    {
        $customerData = [];

        if($this->getUser()->isIsBranchManager()){
            // get all bank account
            $customers = $this->customerRepository->findBy([], ['id' => 'DESC']);

            foreach ($customers as $customer)
            {
                $customerData[] = [
                    '@id' => "/api/get/customer/" . $customer->getId(),
                    '@type' => "Customer",
                    'id'=> $customer ->getId(),
                    'code'=> $customer->getCode(),
                    'name'=> $customer->getName(),
                    'phone'=> $customer->getPhone(),
                    'email'=> $customer->getEmail(),
                    'address'=> $customer->getAddress(),
                    'postbox'=> $customer->getPostbox(),
                    'taxpayernumber'=> $customer->getTaxpayernumber(),
                    'businessnumber'=> $customer->getBusinessnumber(),
                    'debit'=> $customer->getDebit(),
                    'credit'=> $customer->getCredit(),
                    'isTva'=> $customer->isIsTva(),
                    'civility' => [
                        '@id' => "/api/get/civility/" . $customer->getId(),
                        '@type' => "Civility",
                        'id' => $customer->getCivility() ? $customer->getCivility()->getId() : '',
                        'name' => $customer->getCivility() ? $customer->getCivility()->getName() : '',
                    ],
                    'partnerCategory' => [
                        '@id' => "/api/get/partner-category/" . $customer->getId(),
                        '@type' => "PartnerCategory",
                        'id' => $customer->getPartnerCategory() ? $customer->getPartnerCategory()->getId() : '',
                        'name' => $customer->getPartnerCategory() ? $customer->getPartnerCategory()->getName() : '',
                    ],
                    'bankAccount' => [
                        '@id' => "/api/get/bank-account/" . $customer->getId(),
                        '@type' => "BankAccount",
                        'id' => $customer->getBankAccount() ? $customer->getBankAccount()->getId() : '',
                        'accountName' => $customer->getBankAccount() ? $customer->getBankAccount()->getAccountName() : '',
                        'accountNumber' => $customer->getBankAccount() ? $customer->getBankAccount()->getAccountNumber() : '',
                    ],
                    'branch' => [
                        '@id' => "/api/get/branch/" . $customer->getId(),
                        '@type' => "Branch",
                        'id' => $customer->getBranch() ? $customer->getBranch()->getId() : '',
                        'code' => $customer->getBranch() ? $customer->getBranch()->getCode() : '',
                        'name' => $customer->getBranch() ? $customer->getBranch()->getName() : '',
                    ],
                ];
            }
        }
        else
        {
            $systemSettings = $this->systemSettingsRepository->findOneBy([]);
            if($systemSettings)
            {
                if($systemSettings->isIsBranches())
               {
                   $userBranches = $this->getUser()->getUserBranches();
                   foreach ($userBranches as $userBranch) {

                       $customers = $this->customerRepository->findBy(['branch' => $userBranch], ['id' => 'DESC']);
                          foreach ($customers as $customer){
                              $customerData[] = [
                                  '@id' => "/api/get/customer/" . $customer->getId(),
                                  '@type' => "Customer",
                                  'id'=> $customer ->getId(),
                                  'code'=> $customer->getCode(),
                                  'name'=> $customer->getName(),
                                  'phone'=> $customer->getPhone(),
                                  'email'=> $customer->getEmail(),
                                  'address'=> $customer->getAddress(),
                                  'postbox'=> $customer->getPostbox(),
                                  'taxpayernumber'=> $customer->getTaxpayernumber(),
                                  'businessnumber'=> $customer->getBusinessnumber(),
                                  'debit'=> $customer->getDebit(),
                                  'credit'=> $customer->getCredit(),
                                  'isTva'=> $customer->isIsTva(),
                                  'civility' => [
                                      '@id' => "/api/get/civility/" . $customer->getId(),
                                      '@type' => "Civility",
                                      'id' => $customer->getCivility() ? $customer->getCivility()->getId() : '',
                                      'name' => $customer->getCivility() ? $customer->getCivility()->getName() : '',
                                  ],
                                  'partnerCategory' => [
                                      '@id' => "/api/get/partner-category/" . $customer->getId(),
                                      '@type' => "PartnerCategory",
                                      'id' => $customer->getPartnerCategory() ? $customer->getPartnerCategory()->getId() : '',
                                      'name' => $customer->getPartnerCategory() ? $customer->getPartnerCategory()->getName() : '',
                                  ],
                                  'bankAccount' => [
                                      '@id' => "/api/get/bank-account/" . $customer->getId(),
                                      '@type' => "BankAccount",
                                      'id' => $customer->getBankAccount() ? $customer->getBankAccount()->getId() : '',
                                      'accountName' => $customer->getBankAccount() ? $customer->getBankAccount()->getAccountName() : '',
                                      'accountNumber' => $customer->getBankAccount() ? $customer->getBankAccount()->getAccountNumber() : '',
                                  ],
                                  'branch' => [
                                      '@id' => "/api/get/branch/" . $customer->getId(),
                                      '@type' => "Branch",
                                      'id' => $customer->getBranch() ? $customer->getBranch()->getId() : '',
                                      'code' => $customer->getBranch() ? $customer->getBranch()->getCode() : '',
                                      'name' => $customer->getBranch() ? $customer->getBranch()->getName() : '',
                                  ],
                              ];
                          }
                       }
               }
               else {
                   $customers = $this->customerRepository->findBy(['branch' => $this->getUser()->getBranch()], ['id' => 'DESC']);

                   foreach ($customers as $customer) {
                       if ($customer) {
                           $customerData[] = [
                               '@id' => "/api/get/customer/" . $customer->getId(),
                               '@type' => "Customer",
                               'id'=> $customer ->getId(),
                               'code'=> $customer->getCode(),
                               'name'=> $customer->getName(),
                               'phone'=> $customer->getPhone(),
                               'email'=> $customer->getEmail(),
                               'address'=> $customer->getAddress(),
                               'postbox'=> $customer->getPostbox(),
                               'taxpayernumber'=> $customer->getTaxpayernumber(),
                               'businessnumber'=> $customer->getBusinessnumber(),
                               'debit'=> $customer->getDebit(),
                               'credit'=> $customer->getCredit(),
                               'isTva'=> $customer->isIsTva(),
                               'civility' => [
                                   '@id' => "/api/get/civility/" . $customer->getId(),
                                   '@type' => "Civility",
                                   'id' => $customer->getCivility() ? $customer->getCivility()->getId() : '',
                                   'name' => $customer->getCivility() ? $customer->getCivility()->getName() : '',
                               ],
                               'partnerCategory' => [
                                   '@id' => "/api/get/partner-category/" . $customer->getId(),
                                   '@type' => "PartnerCategory",
                                   'id' => $customer->getPartnerCategory() ? $customer->getPartnerCategory()->getId() : '',
                                   'name' => $customer->getPartnerCategory() ? $customer->getPartnerCategory()->getName() : '',
                               ],
                               'bankAccount' => [
                                   '@id' => "/api/get/bank-account/" . $customer->getId(),
                                   '@type' => "BankAccount",
                                   'id' => $customer->getBankAccount() ? $customer->getBankAccount()->getId() : '',
                                   'accountName' => $customer->getBankAccount() ? $customer->getBankAccount()->getAccountName() : '',
                                   'accountNumber' => $customer->getBankAccount() ? $customer->getBankAccount()->getAccountNumber() : '',
                               ],
                               'branch' => [
                                   '@id' => "/api/get/branch/" . $customer->getId(),
                                   '@type' => "Branch",
                                   'id' => $customer->getBranch() ? $customer->getBranch()->getId() : '',
                                   'code' => $customer->getBranch() ? $customer->getBranch()->getCode() : '',
                                   'name' => $customer->getBranch() ? $customer->getBranch()->getName() : '',
                               ],
                           ];
                       }
                   }

               }
            }
        }


        return $this->json(['hydra:member' => $customerData]);
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
