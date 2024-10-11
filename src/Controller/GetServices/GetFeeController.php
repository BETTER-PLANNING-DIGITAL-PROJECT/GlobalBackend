<?php

namespace App\Controller\GetServices;

use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\NoteTypeRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Schooling\Configuration\FeeRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use App\Repository\Security\Session\YearRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GetFeeController extends AbstractController
{
    private Request $req;
    private EntityManagerInterface $entityManager;
    private FeeRepository $feeRepository;
    private StudentRegistrationRepository $studentRegistrationRepository;
    private StudentCourseRegistrationRepository $studCourseRegRepository;
    private SchoolClassRepository $schoolClassRepository;
    private SchoolRepository $schoolRepository;
    private SequenceRepository $sequenceRepository;
    private MarkRepository $markRepository;
    private YearRepository $yearRepository;
    private StudentRepository $studentRepository;
    private TeacherRepository $teacherRepository;
    private TeacherCourseRegistrationRepository $teacherCourseRegistrationRepository;
    private InstitutionRepository $institutionRepository;

    /**
     * @param Request $req
     * @param EntityManagerInterface $entityManager
     * @param FeeRepository $feeRepository
     * @param StudentRegistrationRepository $studentRegistrationRepository
     * @param StudentCourseRegistrationRepository $studCourseRegRepository
     * @param MarkRepository $markRepository
     * @param SchoolClassRepository $schoolClassRepository
     * @param SchoolRepository $schoolRepository
     * @param YearRepository $yearRepository
     * @param StudentRepository $studentRepository
     * @param TeacherRepository $teacherRepository
     * @param TeacherCourseRegistrationRepository $teacherCourseRegistrationRepository
     * @param SequenceRepository $sequenceRepository
     * @param InstitutionRepository $institutionRepository
     */
    public function __construct(Request $req, private readonly TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager, MarkRepository $markRepository, FeeRepository $feeRepository, SequenceRepository $sequenceRepository, StudentRegistrationRepository $studentRegistrationRepository, StudentCourseRegistrationRepository $studCourseRegRepository, SchoolClassRepository $schoolClassRepository, SchoolRepository $schoolRepository, YearRepository $yearRepository, StudentRepository $studentRepository, InstitutionRepository $institutionRepository, TeacherRepository $teacherRepository, TeacherCourseRegistrationRepository $teacherCourseRegistrationRepository, private readonly NoteTypeRepository $noteTypeRepository, private readonly EvaluationPeriodRepository $evaluationPeriodRepository)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->feeRepository = $feeRepository;
        $this->studentRegistrationRepository = $studentRegistrationRepository;
        $this->studCourseRegRepository = $studCourseRegRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->schoolRepository = $schoolRepository;
        $this->markRepository = $markRepository;
        $this->yearRepository = $yearRepository;
        $this->studentRepository = $studentRepository;
        $this->sequenceRepository = $sequenceRepository;
        $this->teacherRepository = $teacherRepository;
        $this->teacherCourseRegistrationRepository = $teacherCourseRegistrationRepository;
        $this->institutionRepository = $institutionRepository;
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

    // Get class program : year - school - class
    #[Route('/api/get/fee/year/school/class/{classProgramData}', name: 'get_fee_by_year_school_class')]
    public function getFeeByYearSchoolClass(string $classProgramData): JsonResponse
    {
        $institution = $this->getUser()->getInstitution();
        $classProgramData = json_decode($classProgramData, true);
        $yearId = $classProgramData['yearId'];
        $schoolId = $classProgramData['schoolId']; 
        $classId = $classProgramData['classId'];
        $year = $this->yearRepository->find($yearId);
        $school = $this->schoolRepository->find($schoolId);
        $class = $this->schoolClassRepository->find($classId);

        $criteria = ['institution' => $institution, 'year' => $year, 'school' => $school, 'class' => $class];

        $feesAll = $this->feeRepository->findBy($criteria);

        return $this->json($feesAll);
    }

}
