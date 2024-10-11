<?php

namespace App\Controller\Dashboard;

use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\FeeRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class SchoolingDashboardController extends AbstractController
{
    private EntityManagerInterface $manager;
    private ClassProgramRepository $classProgramRepository;
    private SchoolClassRepository $schoolClassRepository;
    private StudentCourseRegistrationRepository $studentCourseRegistrationRepository;
    private StudentRegistrationRepository $studentRegistrationRepository;
    private StudentRepository $studentRepository;
    private FeeRepository $feeRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                Request                                $req, EntityManagerInterface $manager,
                                StudentCourseRegistrationRepository    $studentCourseRegistrationRepository,
                                StudentRegistrationRepository          $studentRegistrationRepository,
                                ClassProgramRepository                 $classProgramRepository,
                                SchoolClassRepository                  $schoolClassRepository,
                                StudentRepository                      $studentRepository,
                                FeeRepository                      $feeRepository)
    {
        $this->manager = $manager;
        $this->studentCourseRegistrationRepository = $studentCourseRegistrationRepository;
        $this->studentRegistrationRepository = $studentRegistrationRepository;
        $this->classProgramRepository = $classProgramRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->studentRepository = $studentRepository;
        $this->feeRepository = $feeRepository;
    }


    #[Route('/api/schooling/dashboard/registered/students', name: 'app_schooling_dashboard_registered_students')]
    public function RegisteredStudentsForCurrentYear(): JsonResponse
    {
        $currentYear = $this->getUser()->getCurrentYear();
        $registeredStudents = $this->studentRegistrationRepository->countRegisteredStudentsForCurrentYear($currentYear);
        return new JsonResponse(['hydra:description' => $registeredStudents]);

    }

    #[Route('/api/schooling/dashboard/dismissed/students', name: 'app_schooling_dashboard_dismissed_students')]
    public function DismissedStudentsForCurrentYear(): JsonResponse
    {
        $currentYear = $this->getUser()->getCurrentYear();
        $dismissedStudents = $this->studentRegistrationRepository->countDismissedStudentsForCurrentYear($currentYear);
        return new JsonResponse(['hydra:description' => $dismissedStudents]);

    }

    #[Route('/api/schooling/dashboard/resigned/students', name: 'app_schooling_dashboard_resigned_students')]
    public function ResignedStudentsForCurrentYear(): JsonResponse
    {
        $currentYear = $this->getUser()->getCurrentYear();
        $resignedStudents = $this->studentRegistrationRepository->countResignedStudentsForCurrentYear($currentYear);
        return new JsonResponse(['hydra:description' => $resignedStudents]);

    }

    #[Route('/api/schooling/dashboard/students', name: 'app_schooling_dashboard_students')]
    public function StudentsForCurrentYear(): JsonResponse
    {
        $currentYear = $this->getUser()->getCurrentYear();
        $students = $this->studentRepository->countStudentsForCurrentYear($currentYear);
        return new JsonResponse(['hydra:description' => $students]);

    }

    #[Route('/api/schooling/dashboard/recent/registrations', name: 'app_schooling_dashboard_recent_registrations')]
    public function RecentRegistrations(): JsonResponse
    {
        $recentRegistrations = $this->studentRegistrationRepository->findBy([], ['id' => 'DESC'], 5);

        $registrations = [];
        foreach($recentRegistrations as $recentRegistration){
            $registrations[] = [
                'id'=> $recentRegistration ->getId(),
                'currentYear'=> $recentRegistration ->getCurrentYear(),
                'school'=> $recentRegistration->getSchool(),
                'currentClass'=> $recentRegistration->getCurrentClass(),
                'status'=> $recentRegistration->getStatus(),
                'student' => [
                    '@id' => "/api/students/".$recentRegistration->getStudent()->getId(),
                    '@type' => "Student",
                    'id' => $recentRegistration->getStudent()->getId(),
                    'year' => $recentRegistration->getStudent()->getYear(),
                    'matricule' => $recentRegistration->getStudent()->getMatricule(),
                    'name' => $recentRegistration->getStudent()->getName(),
                    'firstName' => $recentRegistration->getStudent()->getFirstName(),
                    'status' => $recentRegistration->getStudent()->getStatus(),
                    'picture' => $recentRegistration->getStudent()->getPicture(),
                ],
            ];
        }
        return new JsonResponse(['hydra:description' => $registrations]);
    }


    #[Route('/api/schooling/dashboard/recent/fee', name: 'app_schooling_dashboard_recent_fee')]
    public function RecentFee(): JsonResponse
    {
        $recentFees = $this->feeRepository->findBy([], ['id' => 'DESC'], 5);

        $fees = [];
        foreach($recentFees as $recentFee){
            $fees[] = [
                'id'=> $recentFee ->getId(),
                'registrationFee'=> $recentFee->getAmount(),
                'costarea' => [
                    '@id' => "/api/cost_area/".$recentFee->getCostArea()->getId(),
                    '@type' => "CostArea",
                    'id' => $recentFee->getCostArea()->getId(),
                    'name' => $recentFee->getCostArea()->getName(),
                ],
                'school' => [
                    '@id' => "/api/schools/".$recentFee->getSchool()->getId(),
                    '@type' => "School",
                    'id' => $recentFee->getSchool()->getId(),
                    'code' => $recentFee->getSchool()->getCode(),
                    'name' => $recentFee->getSchool()->getName(),
                ],
            ];
        }
        return new JsonResponse(['hydra:description' => $fees]);
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
