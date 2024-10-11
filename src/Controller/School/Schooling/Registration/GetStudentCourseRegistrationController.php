<?php

namespace App\Controller\School\Schooling\Registration;

use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use App\Repository\Security\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
class GetStudentCourseRegistrationController extends AbstractController
{
    public function __construct(EntityManagerInterface $manager,
                                private readonly StudentCourseRegistrationRepository $studentCourseRegistrationRepository,
                                private readonly ClassProgramRepository $classProgramRepository,
    )
    {
    }

    public function __invoke(Request $request, RoleRepository $roleRepository,
                             InstitutionRepository $institutionRepository, UserPasswordHasherInterface $passwordHasher): array
    {
        $classProgramId = $request->attributes->get('id');
        //dd($subjectId);
        $classProgram = $this->classProgramRepository->findOneBy(['id' => $classProgramId]);
        //dd($data);
        $studentRegistrations = $this->studentCourseRegistrationRepository->findBy(['classProgram' => $classProgram]);

        $studentsInCourse = [];
        foreach ($studentRegistrations as $registration) {
            $studentsInCourse[] = [
                'id' => $registration->getStudRegistration() ? $registration->getStudRegistration()->getStudent()->getId() : '',
                'name' => $registration->getStudRegistration() ? $registration->getStudRegistration()->getStudent()->getName() : '',
                'firstname' => $registration->getStudRegistration() ? $registration->getStudRegistration()->getStudent()->getFirstName() : '',
                'matricule' => $registration->getStudRegistration() ? $registration->getStudRegistration()->getStudent()->getMatricule() : '',
            ];
        }

        return $studentsInCourse;
    }

}
