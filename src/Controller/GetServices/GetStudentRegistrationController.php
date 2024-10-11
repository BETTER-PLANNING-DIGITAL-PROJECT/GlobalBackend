<?php

namespace App\Controller\GetServices;

use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\NoteTypeRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Security\Session\YearRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GetStudentRegistrationController extends AbstractController
{
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

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MarkRepository $markRepository,
        private readonly SequenceRepository $sequenceRepository,
        private readonly EvaluationPeriodRepository $evaluationPeriodRepository,
        private readonly StudentRegistrationRepository $studentRegistrationRepository,
        private readonly StudentCourseRegistrationRepository $studentCourseRegistrationRepository,
        private readonly SchoolClassRepository $classRepository,
        private readonly ClassProgramRepository $classProgramRepository,
        private readonly NoteTypeRepository $noteTypeRepository,
        private readonly SchoolRepository $schoolRepository,
        private readonly YearRepository $yearRepository
    )
    {
    }

    // Get student registration

    static function bindStudentRegistration(StudentRegistration $studentRegistration): array
    {
        $student = $studentRegistration->getStudent();
        return ['fullName' => $student->getFirstName().' '.$student->getName().' '.$student->getMatricule() ,'name' => $student->getFirstName().' '.$student->getName() ,'id' => $studentRegistration->getId()];
    }

    #[Route('/api/stud-registration/get-by-year-school-class', name: 'app_stud_registration_get_by_year_school_class')]
    public function getStudentRegistrationByYearSchoolClass(Request $request): JsonResponse
    {
//        $institution = $this->getUser()->getInstitution();
        $studentRegistrationData = json_decode($request->getContent(), true);

        $yearId = $studentRegistrationData['yearId'];
        $schoolId = $studentRegistrationData['schoolId'];
        $classId = $studentRegistrationData['classId'];

        $year = $this->yearRepository->find($yearId);
        $school = $this->schoolRepository->find($schoolId);
        $class = $this->classRepository->find($classId);
        $institution = $class->getInstitution();

        $studentRegistrations = $this->studentRegistrationRepository->findBy(['institution' => $institution, 'currentYear' => $year, 'school' => $school, 'currentClass' => $class]);

        if (isset($studentRegistrationData['sequenceId'])) {
            $sequenceId = $studentRegistrationData['sequenceId'];
            $noteType = isset($studentRegistrationData['noteTypeId']) && $studentRegistrationData['noteTypeId'] !== null ? $this->noteTypeRepository->find($studentRegistrationData['noteTypeId']) : null;
            $sequence = $this->sequenceRepository->find($sequenceId);

            $students = [];
            $criteria = ['institution' => $institution, 'year' => $year, 'school' => $school, 'class' => $class];
            $criteria['noteType'] = $noteType;
            $criteria['sequence'] = $sequence;

            $criteriaStudentCourseRegistration = [];
            $criteriaClassProgram = ['class'=>$class];

            if (isset($studentRegistrationData['evaluationPeriodId'])){
                $evaluationPeriod = $this->evaluationPeriodRepository->find($studentRegistrationData['evaluationPeriodId']);
                $criteria['evaluationPeriod'] = $evaluationPeriod;
                $criteriaStudentCourseRegistration['evaluationPeriod'] = $evaluationPeriod;
                $criteriaClassProgram['evaluationPeriod'] = $evaluationPeriod;
            }

            $notClassPrograms = $this->classProgramRepository->findOneBy($criteriaClassProgram) === null;

            foreach ($studentRegistrations as $studentRegistration) {
                $criteria['student'] = $studentRegistration;
                $criteriaStudentCourseRegistration['StudRegistration'] = $studentRegistration;
                $studentCourseRegistrations = $this->studentCourseRegistrationRepository->findBy($criteriaStudentCourseRegistration);
                $j = 0;
                $isOpen = true;
                $count = count($studentCourseRegistrations);
                $notStudentCourseRegistrations = $count === 0;
                while ($j < $count && $isOpen) {
                    $studentCourseRegistration = $studentCourseRegistrations[$j];
                    $criteria['studentCourseRegistration'] = $studentCourseRegistration;
                    $mark = $this->markRepository->findOneBy($criteria);
                    $isOpen = isset($mark);
                    $j++;
                }
                $student = $studentRegistration->getStudent();
                $fullName = $student->getFirstName() . ' ' . $student->getName();
                $students[] = ['name' => $fullName,'fullName' => $fullName .' '.$student->getMatricule(), 'id' => $studentRegistration->getId(), 'isOpen' => $isOpen
                    ,'notStudentCourseRegistrations'=>$notStudentCourseRegistrations,'notClassPrograms'=>$notClassPrograms];
            }
            return $this->json($students);
        }
        return $this->json(array_map('self::bindStudentRegistration', $studentRegistrations));
    }
}
