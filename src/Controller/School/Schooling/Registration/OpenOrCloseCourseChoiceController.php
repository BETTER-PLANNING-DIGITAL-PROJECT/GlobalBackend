<?php

namespace App\Controller\School\Schooling\Registration;

use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use App\Repository\Security\Session\YearRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OpenOrCloseCourseChoiceController extends AbstractController
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
    public function __construct(Request                                $req,
                                EntityManagerInterface                 $entityManager,
                                MarkRepository                         $markRepository,
                                ClassProgramRepository                 $classProgramRepository,
                                SequenceRepository                     $sequenceRepository,
                                StudentRegistrationRepository          $studentRegistrationRepository,
                                StudentCourseRegistrationRepository    $studCourseRegRepository,
                                SchoolClassRepository                  $schoolClassRepository, SchoolRepository $schoolRepository,
                                YearRepository                         $yearRepository,
                                StudentRepository                      $studentRepository,
                                InstitutionRepository                  $institutionRepository,
                                TeacherRepository                      $teacherRepository,
                                TeacherCourseRegistrationRepository    $teacherCourseRegistrationRepository)
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

    // Mark

    // Open Or Close Courses Choice
    #[Route('api/open/close/course/choice/per/class/{schoolClasses}', name: 'open_or_close_course_choice_per_class')]
    public function openOrCloseCourseChoicePerClass(mixed $schoolClasses): JsonResponse
    {
        $schoolClassData = json_decode($schoolClasses, true);
        extract($schoolClassData);

        foreach ($classIds as $classId) {
            $class = $this->schoolClassRepository->find($classId);
            $isChoiceStudentCourse = !$class->getIsChoiceStudentCourse();
            $class->setIsChoiceStudentCourse($isChoiceStudentCourse);

            // Retrieve the ClassProgram entities related to the current SchoolClass
            $classPrograms = $this->classProgramRepository->findBy(['class' => $class]);

            // Toggle the IsChoiceStudCourseOpen property for each ClassProgram
            foreach ($classPrograms as $classProgram) {
                $classProgram->setIsChoiceStudCourseOpen($isChoiceStudentCourse);
            }
        }

        $this->entityManager->flush();
        return $this->json([]);
    }

    #[Route('/api/open/close/course/choice/per/course/{classPrograms}', name: 'open_or_close_course_choice_per_course')]
    public function openOrCloseCourseChoicePerCourse(mixed $classPrograms): JsonResponse
    {
        $classProgramData = json_decode($classPrograms, true);
        extract($classProgramData);

        foreach ($classProgramsIds as $classProgramsId) {

            // Retrieve the ClassProgram entities related to the current SchoolClass
            $classProgram = $this->classProgramRepository->find($classProgramsId);

            // Toggle the IsChoiceStudCourseOpen property for each ClassProgram
                $classProgram->setIsChoiceStudCourseOpen(!$classProgram->getIsChoiceStudCourseOpen());
        }

        $this->entityManager->flush();
        return $this->json([]);
    }


}
