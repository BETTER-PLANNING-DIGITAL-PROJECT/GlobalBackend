<?php

namespace App\Controller;

use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\Security\User;
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

class StudentRegistrationController extends AbstractController
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
                                private StudentRepository              $studentRepo,
                                private readonly TokenStorageInterface $tokenStorage,
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

    #[Route('/api/stud-course-reg/open-or-close-course/{schoolClasses}', name: 'app_stud_course_reg_open_or_close_course')]
    public function openOrCloseCourse(mixed $schoolClasses): JsonResponse
    {
        $schoolClassData = json_decode($schoolClasses, true);
        extract($schoolClassData);

        foreach ($classIds as $classId) {

            // Retrieve the ClassProgram entities related to the current SchoolClass
            $classPrograms = $this->classProgramRepository->findBy(['id' => $classId]);

            // Toggle the IsChoiceStudCourseOpen property for each ClassProgram
            foreach ($classPrograms as $classProgram) {
                $classProgram->setIsChoiceStudCourseOpen(!$classProgram->getIsChoiceStudCourseOpen());
            }
        }

        $this->entityManager->flush();
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

    #[Route('/api/delete/student/registration/{id}', name: 'app_student_registration_delete')]
    public function delete(int $id): JsonResponse
    {
        $studentRegistration = $this->studentRegistrationRepository->find($id);
        $hasSchoolMark = $studentRegistration->getHasSchoolMark();
        if (!$hasSchoolMark) {

            $studCourseRegs = $this->studCourseRegRepository->findBy(['StudRegistration' => $studentRegistration]);

            foreach ($studCourseRegs as $studCourseReg) $this->entityManager->remove($studCourseReg);
            $this->entityManager->remove($studentRegistration);
            $this->entityManager->flush();
        }
        return $this->json(['deleted' => !$hasSchoolMark]);
    }

    public function deleteStudCourseReg(int $id): JsonResponse
    {
        $studentRegistration = $this->studentRegistrationRepository->find($id);
        $studCourseRegs = $this->studCourseRegRepository->findBy(['StudRegistration' => $studentRegistration]);

        foreach ($studCourseRegs as $studCourseReg) $this->entityManager->remove($studCourseReg);
        //$this->manager-> remove($studentRegistration);
        $this->entityManager->flush();
        return $this->json([]);
    }

//    #[Route('/stud-course-reg/chooseCourses/{studCourseRegistrations}', name: 'app_stud_course_reg_chooseCourses')]
//    public function chooseCourses(string $studCourseRegistrations): JsonResponse
//    {
//        $studCourseRegData = json_decode($studCourseRegistrations, true);
////        dd($studCourseRegData);
////        $studCourseRegData = $studCourseRegData['studCourseRegistrations'];
////        $class = $studCourseRegData['classId'];
//        extract($studCourseRegData);
//
//        if ($studCourseRegistrationIds !== []) {
//            foreach ($studCourseRegistrationIds as $id) {
//                $studCourseReg = $this->studCourseRegRepository->find($id);
//                if ($studCourseReg->getHasSchoolMark() === true  || $studCourseReg->getClassProgram()->isIsSubjectObligatory() === true || $studCourseReg->getIsSubjectObligatory() === false)
//                    $studCourseReg->setIsSubjectObligatory(!$studCourseReg->getIsSubjectObligatory());
//                else $this->entityManager->remove($studCourseReg);
//            }
//            $this->entityManager->flush();
//        }
//
//        if ($classProgramIds !== []) {
//            $class = $this->schoolClassRepository->find($classId);
//            $year = $this->yearRepository->find($yearId);
//            $institution = $this->institutionRepository->find(1);
//            $school = $class->getSchool();
//            $criteria = ['class' => $class, 'year' => $year, 'institution' => $institution, 'school' => $school];
//            $studCourseReg = $this->studCourseRegRepository->findOneBy($criteria);
//            $isOpen = $studCourseReg ? $studCourseReg->isIsOpen() : false;
//            if (isset($studregistrationIds))
//                foreach ($studregistrationIds as $studregistrationId) {
//                    $studentRegistration = $this->studentRegistrationRepository->find($studregistrationId);
//                    foreach ($classProgramIds as $id) {
//                        $classProgram = $this->classProgramRepository->find($id);
//                        $subject = $classProgram->getSubject();
//                        $studCourseReg = new StudentCourseRegistration();
//                        $studCourseReg->setClass($class);
//                        $studCourseReg->setYear($year);
//                        $studCourseReg->setInstitution($institution);
//                        $studCourseReg->setSchool($school);
//                        $studCourseReg->setStudRegistration($studentRegistration);
//                        $studCourseReg->setClassProgram($classProgram);
//                        $studCourseReg->setSubject($subject);
//                        $studCourseReg->setIsOpen($isOpen);
//                        $this->entityManager->persist($studCourseReg);
//                    }
//                }
//            else {
//                $studentRegistration = $this->studentRegistrationRepository->findOneByStudentMatricule($studentMatricule, $class, $year, $school);
//                foreach ($classProgramIds as $id) {
//                    $classProgram = $this->classProgramRepository->find($id);
//                    $subject = $classProgram->getSubject();
//                    $studCourseReg = new StudentCourseRegistration();
//                    $studCourseReg->setClass($class);
//                    $studCourseReg->setYear($year);
//                    $studCourseReg->setInstitution($institution);
//                    $studCourseReg->setSchool($school);
//                    $studCourseReg->setStudRegistration($studentRegistration);
//                    $studCourseReg->setClassProgram($classProgram);
//                    $studCourseReg->setSubject($subject);
//                    $studCourseReg->setIsOpen($isOpen);
//                    $this->entityManager->persist($studCourseReg);
//                }
//            }
//            $this->entityManager->flush();
//        }
//
//        return $this->json([]);
//    }


}
