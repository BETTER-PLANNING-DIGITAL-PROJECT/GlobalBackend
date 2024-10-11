<?php

namespace App\Controller\GetServices;

use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\NoteTypeRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
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

class GetClassProgramController extends AbstractController
{
    private Request $req;
    private EntityManagerInterface $entityManager;
    private ClassProgramRepository $classProgramRepository;
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
     * @param ClassProgramRepository $classProgramRepository
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
    public function __construct(Request $req, private readonly TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager, MarkRepository $markRepository, ClassProgramRepository $classProgramRepository, SequenceRepository $sequenceRepository, StudentRegistrationRepository $studentRegistrationRepository, StudentCourseRegistrationRepository $studCourseRegRepository, SchoolClassRepository $schoolClassRepository, SchoolRepository $schoolRepository, YearRepository $yearRepository, StudentRepository $studentRepository, InstitutionRepository $institutionRepository, TeacherRepository $teacherRepository, TeacherCourseRegistrationRepository $teacherCourseRegistrationRepository, private readonly NoteTypeRepository $noteTypeRepository, private readonly EvaluationPeriodRepository $evaluationPeriodRepository)
    {
        $this->req = $req;
        $this->entityManager = $entityManager;
        $this->classProgramRepository = $classProgramRepository;
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
    #[Route('/api/get/class-program/year/school/class/{classProgramData}', name: 'get_class_program_by_year_school_class')]
    public function getClassProgramByYearSchoolClassMark(string $classProgramData): JsonResponse
    {
        $institution = $this->getUser()->getInstitution();
        $classProgramData = json_decode($classProgramData, true);
        $yearId = $classProgramData['yearId'];
        $schoolId = $classProgramData['schoolId'];
        $classId = $classProgramData['classId'];
        $year = $this->yearRepository->find($yearId);
        $school = $this->schoolRepository->find($schoolId);
        $class = $this->schoolClassRepository->find($classId);
        $evaluationPeriod = isset($classProgramData['evaluationPeriodId']) ? $this->evaluationPeriodRepository->find($classProgramData['evaluationPeriodId']) : null;
        $currentTeacher = $this->teacherRepository->findOneBy(['operator' => $this->getUser()]);
        if (isset($currentTeacher)) {

            $teacherCoursesRegistration = $this->teacherCourseRegistrationRepository->findBy(['teacher' => $currentTeacher]);
            $classPrograms = [];
            $classProgramIds = [];

            if ($evaluationPeriod) {
                foreach ($teacherCoursesRegistration as $teacherCourseRegistration) {
                    $classProgramData = $teacherCourseRegistration->getCourse();
                    if ($classProgramData->getYear() === $year && $classProgramData->getClass() === $class && $classProgramData->getEvaluationPeriod() === $evaluationPeriod && !in_array($classProgramData->getId(), $classProgramIds, true)) {
                        $classPrograms[] = $classProgramData;
                        $classProgramIds[] = $classProgramData->getId();
                    }
                }
            } else {
                foreach ($teacherCoursesRegistration as $teacherCourseRegistration) {
                    $classProgramData = $teacherCourseRegistration->getCourse();
                    if ($classProgramData->getYear() === $year && $classProgramData->getClass() === $class && !in_array($classProgramData->getId(), $classProgramIds, true)) {
                        $classPrograms[] = $classProgramData;
                        $classProgramIds[] = $classProgramData->getId();
                    }
                }
            }
            return $this->json($classPrograms);
        }
        $criteria = ['institution' => $institution, 'year' => $year, 'school' => $school, 'class' => $class];
        if ($evaluationPeriod) $criteria['evaluationPeriod'] = $evaluationPeriod;

        $classProgramsAll = $this->classProgramRepository->findBy($criteria);
        if (isset($classProgramData['sequenceId'])) {
            $sequenceId = $classProgramData['sequenceId'];
            $sequence = $this->sequenceRepository->find($sequenceId);
            $noteType = isset($classProgramData['noteTypeId']) && $classProgramData['noteTypeId'] !== null ? $this->noteTypeRepository->find($classProgramData['noteTypeId']) : null;
            $classPrograms = [];
            $criteria['noteType'] = $noteType;
            $criteria['sequence'] = $sequence;

            $notStudents = $this->studentRegistrationRepository->findOneBy(['currentClass' => $class]) === null;
            foreach ($classProgramsAll as $classProgram) {
                $nameuvc = $classProgram->getNameuvc();
                $isOpen = true;
                $studentCourseRegistrations = $this->studCourseRegRepository->findBy(['classProgram' => $classProgram]);
                $classProgramStudCourseExists = !empty($studentCourseRegistrations);
                if ($classProgramStudCourseExists) {
                    $criteria['classProgram'] = $classProgram;
                    $j = 0;
                    // Il y a la possibilite de generer pour une course s'il existe un etudiant pour lequel la note n'a pas ete generee
                    while ($j < count($studentCourseRegistrations) && $isOpen) {
                        $studentCourseRegistration = $studentCourseRegistrations[$j];
                        $criteria['studentCourseRegistration'] = $studentCourseRegistration;
                        $schoolMark = $this->markRepository->findOneBy($criteria);
                        $isOpen = isset($schoolMark);
                        $j++;
                    }
                }
                $classPrograms[] = ['evaluationPeriodName' => $classProgram->getEvaluationPeriod()->getName(), 'nameuvc' => $nameuvc, 'id' => $classProgram->getId(), 'isOpen' => $isOpen
                    , 'classProgramStudCourseExists' => $classProgramStudCourseExists, 'notStudents' => $notStudents];
            }
            return $this->json($classPrograms);
        }
        return $this->json(array_map('self::bindClassProgram', $classProgramsAll));
    }

// Get class program : year - school - class
    #[Route('/api/get/class-program/exportation/year/school/class/{classProgramData}', name: 'get_class_program_by_year_school_class_exportation')]
    public function getClassProgramByYearSchoolClassMarkExportation(string $classProgramData): JsonResponse
    {
        $institution = $this->getUser()->getInstitution();
        $classProgramData = json_decode($classProgramData, true);
        $yearId = $classProgramData['yearId'];
        $schoolId = $classProgramData['schoolId'];
        $classId = $classProgramData['classId'];
        $year = $this->yearRepository->find($yearId);
        $school = $this->schoolRepository->find($schoolId);
        $class = $this->schoolClassRepository->find($classId);
        $evaluationPeriod = isset($classProgramData['evaluationPeriodId']) ? $this->evaluationPeriodRepository->find($classProgramData['evaluationPeriodId']) : null;
        $currentTeacher = $this->teacherRepository->findOneBy(['operator' => $this->getUser()]);

        $criteria = ['institution' => $institution, 'year' => $year, 'school' => $school, 'class' => $class];
        if ($evaluationPeriod) $criteria['evaluationPeriod'] = $evaluationPeriod;

        $classProgramsAll = $this->classProgramRepository->findBy($criteria);

        $sequenceId = $classProgramData['sequenceId'];
        $sequence = $this->sequenceRepository->find($sequenceId);
        $noteType = isset($classProgramData['noteTypeId']) && $classProgramData['noteTypeId'] !== null ? $this->noteTypeRepository->find($classProgramData['noteTypeId']) : null;
        $classPrograms = [];
        $criteria['noteType'] = $noteType;
        $criteria['sequence'] = $sequence;

        $notStudents = $this->studentRegistrationRepository->findOneBy(['currentClass' => $class]) === null;
        foreach ($classProgramsAll as $classProgram) {
            $nameuvc = $classProgram->getNameuvc();
            $isOpen = true;
            $studentCourseRegistrations = $this->studCourseRegRepository->findBy(['classProgram' => $classProgram]);
            $classProgramStudCourseExists = !empty($studentCourseRegistrations);
            if ($classProgramStudCourseExists) {
                $criteria['classProgram'] = $classProgram;

                // Il y a la possibilite d'exporter pour une matiere s'il existe une note generee
                $schoolMark = $this->markRepository->findOneBy($criteria);
                $isOpen = !isset($schoolMark);
            }
            $classPrograms[] = ['evaluationPeriodName' => $classProgram->getEvaluationPeriod()->getName(), 'nameuvc' => $nameuvc, 'id' => $classProgram->getId(), 'isOpen' => $isOpen
                , 'classProgramStudCourseExists' => $classProgramStudCourseExists, 'notStudents' => $notStudents];
        }
        return $this->json($classPrograms);

    }


    #[Route('/api/get/class-program/name', name: 'get_class_program_name')]
    public function getClassProgramName(Request $request): JsonResponse
    {
        $classProgram = json_decode($request->getContent(), true);

        $yearId = $classProgram['yearId'];
        $schoolId = $classProgram['schoolId'];
        $classId = $classProgram['classId'];

        $year = $this->yearRepository->find($yearId);
        $school = $this->schoolRepository->find($schoolId);
        $class = $this->schoolClassRepository->find($classId);

        $criteria = ['institution' => $this->getUser()->getInstitution(), 'year' => $year, 'school' => $school, 'class' => $class];
        $criteriaStudCourseReg = $criteria;
        $classProgramsAll = $this->classProgramRepository->findBy($criteria);
        $studCourseRegs = $this->studCourseRegRepository->findBy($criteriaStudCourseReg);

        // Convert ClassProgram entities to arrays with additional properties
        $classProgramsAll = array_map(function (ClassProgram $classProgram) {
            return [
                'id' => $classProgram->getId(),
                'subject' => $classProgram->getNameuvc(),
                'isChoiceStudCourseOpen' => $classProgram->getIsChoiceStudCourseOpen(),
            ];
        }, $classProgramsAll);

        $bindStudCourseReg = function (StudentCourseRegistration $studentCourseRegistration): array {
            $classProgram = $studentCourseRegistration->getClassProgram();
            return [
                'id' => $studentCourseRegistration->getId(),
                'fullName' => $classProgram->getCodeuvc() . ' : ' . $classProgram->getNameuvc(),
                'classProgramIsSubjectObligatory' => $classProgram->isIsSubjectObligatory(),
                'comesFromStudentCourseRegistration' => true,
                'evaluationPeriodName' => $studentCourseRegistration->getEvaluationPeriod()->getName(),
                'hasSchoolMark' => $this->markRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration]) !== null,
                'isChoiceStudCourseOpen' => $classProgram->getIsChoiceStudCourseOpen(),
            ];
        };

        return $this->json([
            'classPrograms' => $classProgramsAll,
            'studCourseRegs' => array_map($bindStudCourseReg, $studCourseRegs)
        ]);
    }

    #[Route('/api/get/class-program/by-year-school-class', name: 'app_get_class_program_by_year_school_class')]
    public function getClassProgramByYearSchoolClassStudentCourseRegistration(Request $request): JsonResponse
    {
        $classProgram = json_decode($request->getContent(), true);

        $yearId = $classProgram['yearId'];
        $schoolId = $classProgram['schoolId'];
        $classId = $classProgram['classId'];
        $evaluationPeriod = isset($classProgram['evaluationPeriodId']) ? $this->evaluationPeriodRepository->find($classProgram['evaluationPeriodId']) : null;


        $year = $this->yearRepository->find($yearId);
        $school = $this->schoolRepository->find($schoolId);
        $class = $this->schoolClassRepository->find($classId);

        $studentRegistration = null;
        if (isset($classProgram['studentRegistrationId'])) {
            $studentRegistrationId = $classProgram['studentRegistrationId'];
            $studentRegistration = $this->studentRegistrationRepository->find($studentRegistrationId);
        } else {
            $currentStudent = $this->studentRepository->findOneBy(['operator' => $this->getUser()]);
            $studentRegistration = $this->studentRegistrationRepository->findOneBy(['student' => $currentStudent, 'currentYear' => $year]);
        }

        $institution = $this->getUser()->getInstitution();
        $criteria = ['institution' => $institution, 'year' => $year, 'school' => $school, 'class' => $class];
        if ($evaluationPeriod) $criteria['evaluationPeriod'] = $evaluationPeriod;

        $criteriaStudCourseReg = $criteria;

        $criteriaStudCourseReg['StudRegistration'] = $studentRegistration;
        $classProgramsAll = $this->classProgramRepository->findBy($criteria);
        $studentCourseRegistrations = $this->studCourseRegRepository->findBy($criteriaStudCourseReg);

        $classPrograms = [];
        $find = false;

        foreach ($classProgramsAll as $classProgram) {

            foreach ($studentCourseRegistrations as $studentCourseRegistration) {

                if ($classProgram->getId() === $studentCourseRegistration->getClassProgram()->getId()) $find = true;
            }
            if (!$find) $classPrograms[] = $classProgram;
            $find = false;
        }

        $bindStudCourseReg = function (StudentCourseRegistration $studentCourseRegistration): array {
            $classProgram = $studentCourseRegistration->getClassProgram();
            return [
                'id' => $studentCourseRegistration->getId(),
                'fullName' => $classProgram->getCodeuvc() . ' : ' . $classProgram->getNameuvc(),
                'classProgramIsSubjectObligatory' => $classProgram->isIsSubjectObligatory(),
                'comesFromStudentCourseRegistration' => true,
                'evaluationPeriodName' => $studentCourseRegistration->getEvaluationPeriod()->getName(),
                'hasSchoolMark' => $this->markRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration]) !== null,
                'isChoiceStudCourseOpen' => $classProgram->getIsChoiceStudCourseOpen(),
            ];
        };
        return $this->json(['classPrograms' => array_map('self::bindClassProgram', $classPrograms), 'studCourseRegs' => array_map($bindStudCourseReg, $studentCourseRegistrations)]);
    }


    static function bindClassProgram(ClassProgram $classProgram): array
    {
        return [
            'id' => $classProgram->getId(),
            'codeuvc' => $classProgram->getCodeuvc(),
            'nameuvc' => $classProgram->getNameuvc(),
            'fullName' => $classProgram->getCodeuvc() . ' : ' . $classProgram->getNameuvc(),
            'comesFromStudentCourseRegistration' => false,
            'classProgramIsSubjectObligatory' => $classProgram->isIsSubjectObligatory(),
            'evaluationPeriodName' => $classProgram->getEvaluationPeriod()->getName(),
            'isChoiceStudCourseOpen' => $classProgram->getIsChoiceStudCourseOpen(),
        ];
    }

    // SECTION : BY CLASS

    #[Route('/api/get/class-program/by-year-school-class-course-status', name: 'app_get_class_program_by_year_school_class_course_status')]
    public function getClassProgramByYearSchoolClassCourseStatus(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $yearId = $data['yearId'];
        $schoolId = $data['schoolId'];
        $classId = $data['classId'];

        $year = $this->yearRepository->find($yearId);
        $school = $this->schoolRepository->find($schoolId);
        $class = $this->schoolClassRepository->find($classId);
        $evaluationPeriod = isset($data['evaluationPeriodId']) ? $this->evaluationPeriodRepository->find($data['evaluationPeriodId']) : null;

        $institution = $this->getUser()->getInstitution();
        $criteria = ['institution' => $institution, 'year' => $year, 'school' => $school, 'class' => $class];

        if ($evaluationPeriod) $criteria['evaluationPeriod'] = $evaluationPeriod;

        $classPrograms = $this->classProgramRepository->findBy($criteria);

        $obligatoryClassPrograms = [];
        $nonObligatoryClassPrograms = [];

        foreach ($classPrograms as $classProgram) {
            $isSubjectObligatory = $classProgram->isIsSubjectObligatory();

            if ($isSubjectObligatory) {
                // If the subject is obligatory, add it to the obligatory class programs array
                $obligatoryClassPrograms[] = $classProgram;
            } else {
                // If the subject is not obligatory, add it to the non-obligatory class programs array
                $nonObligatoryClassPrograms[] = $classProgram;
            }
        }

        return $this->json([
            'obligatoryClassPrograms' => array_map('self::bindClassProgram', $obligatoryClassPrograms),
            'nonObligatoryClassPrograms' => array_map('self::bindClassProgram', $nonObligatoryClassPrograms)
        ]);

    }

}
