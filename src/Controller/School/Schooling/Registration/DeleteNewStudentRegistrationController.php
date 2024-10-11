<?php

namespace App\Controller\School\Schooling\Registration;

use App\Entity\Partner\Customer;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\Security\RoleRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class DeleteNewStudentRegistrationController extends AbstractController
{
    public function __construct(EntityManagerInterface $manager,
                                private readonly EntityManagerInterface $entityManager,
                                private readonly FileUploader $fileUploader,
                                StudentRegistrationRepository $studentRegistrationRepository)
    {
        $this->manager = $manager;
        $this->studentRegistrationRepository = $studentRegistrationRepository;
    }

    public function __invoke(RoleRepository $roleRepository, Request $request, StudentRepository $studentRepo, StudentRegistrationRepository $studentRegistrationRepository)
    {
        $registration = $this->studentRegistrationRepository->find($request->get('id'));

        // Check if the id correspond to one registration
        if ($registration){

            // Check if the registration have a student: to be sure
            if ($registration->getStudent()){

                if ($registration->getStudent()->getOperator())
                {
                    return new JsonResponse(['hydra:description' =>
                        "An exception occurred while executing a query: SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row: a foreign key constraint fails (`erp`.`school_Credentials`, CONSTRAINT `FK_A4EDA9EC88B5F5A8` FOREIGN KEY (`class_program_id`) REFERENCES `school_class_program` (`id`))"
                    ], 400);
                }
                else
                {
                    if ($registration->getStudent()->getPicture()) {
                        // Remove attach picture
                        $this->fileUploader->deleteUpload($registration->getStudent()->getPicture());
                    }

                    $customer = $this->entityManager->getRepository(Customer::class)->findOneBy(['studentRegistration' => $registration]);
                    if ($customer){
                        // Remove customer object
                        $this->entityManager->remove($customer);
                    }

                    // Remove student object
                    $this->entityManager->remove($registration->getStudent());

                    // Remove student registration object
                    $this->entityManager->remove($registration);
                }

            }
            else
            {
                // Remove student registration itself
                $this->entityManager->remove($registration);
            }

            // Flush changes
            $this->entityManager->flush();

        }

        return new Response(null, Response::HTTP_NO_CONTENT);

    }



}
