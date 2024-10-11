<?php

namespace App\Controller\Billing\Sale;

use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\Repository\Partner\CustomerRepository;
use App\Repository\Product\ItemRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationFeeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetCustomerRegistrationFeeController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                )
    {
    }

    public function __invoke(SaleInvoiceItemRepository $saleInvoiceItemRepository,
                             SaleInvoiceRepository $saleInvoiceRepository,
                             CustomerRepository $customerRepository,
                             ItemRepository $itemRepository,
                             StudentRegistrationFeeRepository $studentRegistrationFeeRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $customer = $customerRepository->find($id);
        if (!$customer){
            return new JsonResponse(['hydra:description' => 'This customer is not found.'], 404);
        }

        $items = [];

        if ($customer->getStudentRegistration()){
            $studentRegistrationFees = $studentRegistrationFeeRepository->findBy(['studentRegistration' => $customer->getStudentRegistration()]);

            foreach ($studentRegistrationFees as $studentRegistrationFee){
                $itemFee = $itemRepository->findOneBy(['fee' => $studentRegistrationFee->getFee()]);
                if ($itemFee){
                    $items[] = [
                        'id' => $itemRepository->findOneBy(['fee' => $studentRegistrationFee->getFee()])?->getId(),
                        '@id' => '/api/get/item/'. $itemRepository->findOneBy(['fee' => $studentRegistrationFee->getFee()])?->getId(),
                        'fee' => [
                            'id' => $studentRegistrationFee->getFee() ? $studentRegistrationFee->getFee()->getId() : '',
                            '@id' => '/api/get/student/registration/fee/'. $studentRegistrationFee->getFee()->getId(),
                            'code' => $studentRegistrationFee->getFee() ? $studentRegistrationFee->getFee()->getCode() : '',
                            'name' => $studentRegistrationFee->getFee() ? $studentRegistrationFee->getFee()->getName() : '',
                            'amount' => $studentRegistrationFee->getFee() ? $studentRegistrationFee->getFee()->getAmount() : '',
                        ],
                        'studentRegistration' => [
                            'id' => $studentRegistrationFee->getStudentRegistration() ? $studentRegistrationFee->getStudentRegistration()->getId() : '',
                            //'@id' => '/api/get/student/registration/'. $invoice->getStudentRegistration()->getId(),
                            'type' => 'StudentRegistration',
                            'center' => $studentRegistrationFee->getStudentRegistration() ? $studentRegistrationFee->getStudentRegistration()->getCenter() : '',
                            'student' => [
                                'id' => $studentRegistrationFee->getStudentRegistration() ? $studentRegistrationFee->getStudentRegistration()->getStudent()->getId() : '',
                                //'@id' => '/api/students/'. $invoice->getStudentRegistration()->getStudent()->getId(),
                                'type' => 'Student',
                                'name' => $studentRegistrationFee->getStudentRegistration() ? $studentRegistrationFee->getStudentRegistration()->getStudent()->getName() : '',
                                'studentemail' => $studentRegistrationFee->getStudentRegistration() ? $studentRegistrationFee->getStudentRegistration()->getStudent()->getStudentemail() : '',
                                'studentphone' => $studentRegistrationFee->getStudentRegistration() ? $studentRegistrationFee->getStudentRegistration()->getStudent()->getStudentphone() : '',

                            ],
                        ],

                    ];
                }

            }
        }

        return $this->json(['hydra:member' => $items]);
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

    public function taxes(SaleInvoiceItem $saleInvoiceItem){
        $taxes = [];

        foreach ($saleInvoiceItem->getTaxes() as $tax){
            $taxes[] = [
                'id' => $tax->getId(),
                'name' => $tax->getName(),
                'rate' => $tax->getRate(),
                'label' => $tax->getLabel(),
            ];
        }
        return $taxes;
    }
}
