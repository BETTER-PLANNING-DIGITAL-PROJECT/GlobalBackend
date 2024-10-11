<?php

namespace App\Controller\Billing\Sale;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class GetRegistrationByCustomerUsingClassController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(SchoolClassRepository $schoolClassRepository,
                             StudentRegistrationRepository $studentRegistrationRepository,
                             Request $request): JsonResponse
    {

        $id = $request->get('id');
        $class = $schoolClassRepository->find($id);

        if (!$class){

            return new JsonResponse(['hydra:description' => 'This class is not found.'], 404);
        }

        $items = [];

        $studentRegistrations = $studentRegistrationRepository->findStudentRegistrationByClass($class);
        foreach ($studentRegistrations as $studentRegistration){
            $items[] = [
                'id' => $studentRegistration->getId(),
                '@id' => '/api/get/student/registration/'. $studentRegistration->getId(),
                'center' => $studentRegistration->getCenter(),
                'student' => $studentRegistration->getStudent()
//                'studentRegistration' => [
//                    'id' => $customer->getStudentRegistration() ? $customer->getStudentRegistration()->getId() : '',
//                    //'@id' => '/api/get/student/registration/'. $invoice->getStudentRegistration()->getId(),
//                    'type' => 'StudentRegistration',
//                    'center' => $customer->getStudentRegistration() ? $customer->getStudentRegistration()->getCenter() : '',
//                    'student' => [
//                        'id' => $customer->getStudentRegistration() ? $customer->getStudentRegistration()->getStudent()->getId() : '',
//                        //'@id' => '/api/students/'. $invoice->getStudentRegistration()->getStudent()->getId(),
//                        'type' => 'Student',
//                        'name' => $customer->getStudentRegistration() ? $customer->getStudentRegistration()->getStudent()->getName() : '',
//                        'studentemail' => $customer->getStudentRegistration() ? $customer->getStudentRegistration()->getStudent()->getStudentemail() : '',
//                        'studentphone' => $customer->getStudentRegistration() ? $customer->getStudentRegistration()->getStudent()->getStudentphone() : '',
//
//                    ],
//                ],

            ];
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
}
