<?php

namespace App\Controller\School\Schooling\Registration;

use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\Security\Institution\InstitutionRepository;
use App\Repository\Security\Session\YearRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
class ChooseCoursesController extends AbstractController
{
    private EntityManagerInterface $manager;
    private ClassProgramRepository $classProgramRepository;
    private SchoolClassRepository $schoolClassRepository;
    private StudentCourseRegistrationRepository $studentCourseRegistrationRepository;
    private StudentRegistrationRepository $studentRegistrationRepository;
    private YearRepository $yearRepository;
    private StudentRepository $studentRepository;
    private InstitutionRepository $institutionRepository;

    public function __construct(private readonly TokenStorageInterface $tokenStorage,
                                Request                                $req, EntityManagerInterface $manager,
                                StudentCourseRegistrationRepository    $studentCourseRegistrationRepository,
                                StudentRegistrationRepository          $studentRegistrationRepository,
                                ClassProgramRepository                 $classProgramRepository,
                                SchoolClassRepository                  $schoolClassRepository,
                                YearRepository                         $yearRepository,
                                StudentRepository                      $studentRepository,
                                InstitutionRepository                  $institutionRepository,
                                private readonly SchoolRepository      $schoolRepository,
                                private readonly MarkRepository        $markRepository)
    {
        $this->manager = $manager;
        $this->studentCourseRegistrationRepository = $studentCourseRegistrationRepository;
        $this->studentRegistrationRepository = $studentRegistrationRepository;
        $this->classProgramRepository = $classProgramRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->yearRepository = $yearRepository;
        $this->studentRepository = $studentRepository;
        $this->institutionRepository = $institutionRepository;
    }

    // BY STUDENT
    #[Route('/api/student-course-registration-by-student/{data}', name: 'app_student_course_registration_by_student')]
    public function chooseCourseByStudent(string $data): JsonResponse
    {
        $studentCourseRegistrationData = json_decode($data, true);
        extract($studentCourseRegistrationData);

        /*$criteria = ['class' => $class, 'year' => $year, 'institution' => $institution, 'school' => $school, 'StudRegistration'=>$studentRegistration];

        $studentCourseRegistration = $this->studentCourseRegistrationRepository->findOneBy($criteria);
        $isOpen = $studentCourseRegistration ? $studentCourseRegistration->isIsOpen() : false;*/

        if ($classProgramIds !== []) {
            $year = $this->yearRepository->find($yearId);
            $class = $this->schoolClassRepository->find($classId);
            $school = $this->schoolRepository->find($schoolId);
            $user = $this->getUser();
            $institution = $user->getInstitution();

            $studentRegistration = null;
            if (isset($studentRegistrationId)) {
                $studentRegistration = $this->studentRegistrationRepository->find($studentRegistrationId);
            } else {
                $currentStudent = $this->studentRepository->findOneBy(['operator' => $this->getUser()]);
                $studentRegistration = $this->studentRegistrationRepository->findOneBy(['student' => $currentStudent, 'currentYear' => $year , 'currentClass'=>$class]);
            }

            foreach ($classProgramIds as $id) {
                $classProgram = $this->classProgramRepository->find($id);

                $existingCourseRegistration = $this->studentCourseRegistrationRepository->findOneBy([
                    'class' => $class,
                    'classProgram' => $classProgram,
                    'StudRegistration' => $studentRegistration,
                ]);

                if (!$existingCourseRegistration) {
                    // Create a new course registration for the student and course
                    $newCourseRegistration = new StudentCourseRegistration();
                    $newCourseRegistration->setYear($year);
                    $newCourseRegistration->setClass($class);
                    $newCourseRegistration->setInstitution($institution);
                    $newCourseRegistration->setSchool($school);
                    $newCourseRegistration->setStudRegistration($studentRegistration);
                    $newCourseRegistration->setClassProgram($classProgram);
                    $newCourseRegistration->setEvaluationPeriod($classProgram->getEvaluationPeriod());
                    $newCourseRegistration->setModule($classProgram->getModule());
                    $newCourseRegistration->setUser($user);
                    $this->manager->persist($newCourseRegistration);
                }
            }
        }

        if ($studentCourseRegistrationIds !== []) {
            foreach ($studentCourseRegistrationIds as $id) {
                $studentCourseRegistration = $this->studentCourseRegistrationRepository->find($id);
                if (!$this->markRepository->findOneBy(['studentCourseRegistration' => $studentCourseRegistration])) $this->manager->remove($studentCourseRegistration);
            }
        }

        $this->manager->flush();

        return $this->json([]);
    }


    // BY CLASS

    #[Route('/api/student-course-registration-by-class/{data}', name: 'app_student_course_registration_by_class')]
    public function chooseCourseByClass(string $data): JsonResponse
    {
        $studentCourseRegistrationData = json_decode($data, true);
        extract($studentCourseRegistrationData);

        $year = $this->yearRepository->find($yearId);
        $class = $this->schoolClassRepository->find($classId);
        $school = $this->schoolRepository->find($schoolId);

        $user = $this->getUser();
        $institution = $user->getInstitution();

        // Use the selected data to update the database
        // For each selected class program (course)

        $obligatoryClassPrograms = array_map(fn(int $id) => $this->classProgramRepository->find($id), $obligatoryClassProgramIds);
        $optionalClassPrograms = array_map(fn(int $id) => $this->classProgramRepository->find($id), $optionalClassProgramIds);
        $studentRegistrations = !empty($studentRegistrationIds) ? array_map(fn(int $id) => $this->studentRegistrationRepository->find($id), $studentRegistrationIds) : $this->studentRegistrationRepository->findBy(['currentClass' => $class]);


        if (!empty($studentRegistrations)) {
            $this->createAndCheckStudentCourseRegistration($studentRegistrations, $optionalClassPrograms, $obligatoryClassPrograms, $institution, $user, $year, $school, $class);
            $this->manager->flush();
        }

        return $this->json([]);
    }

    function createAndCheckStudentCourseRegistration(array $studentRegistrations, array $optionalClassPrograms, array $obligatoryClassPrograms, Institution $institution, User $user, Year $year, School $school, SchoolClass $class): void
    {
        foreach ($optionalClassPrograms as $optionalClassProgram) {
            foreach ($studentRegistrations as $studentRegistration) {

                if ($studentRegistration->getStatus() == 'registered') {
                    // Check if student already has this course
                    $existingCourseRegistration = $this->studentCourseRegistrationRepository->findOneBy([
                        'class' => $class,
                        'classProgram' => $optionalClassProgram,
                        'StudRegistration' => $studentRegistration,
                    ]);

                    if (!$existingCourseRegistration) {
                        // Create a new course registration for the student and course
                        $newCourseRegistration = new StudentCourseRegistration();
                        $newCourseRegistration->setYear($year);
                        $newCourseRegistration->setClass($class);
                        $newCourseRegistration->setInstitution($institution);
                        $newCourseRegistration->setSchool($school);
                        $newCourseRegistration->setStudRegistration($studentRegistration);
                        $newCourseRegistration->setClassProgram($optionalClassProgram);
                        $newCourseRegistration->setEvaluationPeriod($optionalClassProgram->getEvaluationPeriod());
                        $newCourseRegistration->setModule($optionalClassProgram->getModule());
                        $newCourseRegistration->setUser($user);

                        $this->manager->persist($newCourseRegistration);
                    }
                }
            }
        }

        foreach ($obligatoryClassPrograms as $obligatoryClassProgram) {
            foreach ($studentRegistrations as $studentRegistration) {
                if ($studentRegistration->getStatus() == 'registered') {
                    // Check if student already has this course
                    $existingCourseRegistration = $this->studentCourseRegistrationRepository->findOneBy([
                        'class' => $class,
                        'classProgram' => $obligatoryClassProgram,
                        'StudRegistration' => $studentRegistration,
                    ]);

                    if ($existingCourseRegistration && !$this->markRepository->findOneBy(['studentCourseRegistration' => $existingCourseRegistration])) $this->manager->remove($existingCourseRegistration);
                }
            }
        }
    }

    #[Route('/api/student-course-registration/chooseCourses/by-student/{studCourseRegistrations}', name: 'app_student_course_registration_chooseCourses_by_student')]
    public function chooseCoursesByStudent(string $studCourseRegistrations): JsonResponse
    {
        $studCourseRegData = json_decode($studCourseRegistrations, true);
        extract($studCourseRegData);

        if ($studCourseRegistrationIds !== []) {
            foreach ($studCourseRegistrationIds as $id) {
                $studentCourseRegistration = $this->studentCourseRegistrationRepository->find($id);
                if ($studentCourseRegistration->getHasSchoolMark() === true || $studentCourseRegistration->getClassProgram()->isIsSubjectObligatory() === true || $studentCourseRegistration->getIsSubjectObligatory() === false)
                    $studentCourseRegistration->setIsSubjectObligatory(!$studentCourseRegistration->getIsSubjectObligatory());
                else $this->manager->remove($studentCourseRegistration);
            }
            $this->manager->flush();
        }

        if ($classProgramIds !== []) {
            $class = $this->schoolClassRepository->find($classId);
            $year = $this->yearRepository->find($yearId);
            $user = $this->getUser();
            $institution = $user->getInstitution();
            $school = $class->getSchool();
            $criteria = ['class' => $class, 'year' => $year, 'institution' => $institution, 'school' => $school];
            $studentCourseRegistration = $this->studentCourseRegistrationRepository->findOneBy($criteria);
            if (isset($studentRegistrationIds)) {
                echo $studentRegistrationIds->id;
                foreach ($studentRegistrationIds as $studentRegistrationId) {
                    $studentRegistration = $this->studentRegistrationRepository->find($studentRegistrationId);
                    foreach ($classProgramIds as $id) {
                        $classProgram = $this->classProgramRepository->find($id);
                        $studentCourseRegistration = new StudentCourseRegistration();
                        $studentCourseRegistration->setClass($class);
                        $studentCourseRegistration->setYear($year);
                        $studentCourseRegistration->setInstitution($institution);
                        $studentCourseRegistration->setSchool($school);
                        $studentCourseRegistration->setStudRegistration($studentRegistration);

                        $studentCourseRegistration->setClassProgram($classProgram);
                        $studentCourseRegistration->setEvaluationPeriod($classProgram->getEvaluationPeriod());
                        $studentCourseRegistration->setModule($classProgram->getModule());
                        $studentCourseRegistration->setUser($classProgram->getModule());
                        $this->manager->persist($studentCourseRegistration);
                    }
                }
            } else {
                $student = $this->studentRepository->findOneBy(['operator' => $user]);
                $studentRegistration = $this->studentRegistrationRepository->findOneByStudentMatricule($student->getId(), $class, $year, $school);
                foreach ($classProgramIds as $id) {
                    $classProgram = $this->classProgramRepository->find($id);
                    $studentCourseRegistration = new StudentCourseRegistration();
                    $studentCourseRegistration->setClass($class);
                    $studentCourseRegistration->setYear($year);
                    $studentCourseRegistration->setInstitution($institution);
                    $studentCourseRegistration->setSchool($school);
                    $studentCourseRegistration->setStudRegistration($studentRegistration);

                    $studentCourseRegistration->setClassProgram($classProgram);
                    $studentCourseRegistration->setEvaluationPeriod($classProgram->getEvaluationPeriod());
                    $studentCourseRegistration->setModule($classProgram->getModule());
                    $studentCourseRegistration->setUser($classProgram->getModule());
                    $this->manager->persist($studentCourseRegistration);
                }
            }
            $this->manager->flush();
        }

        return $this->json([]);
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
