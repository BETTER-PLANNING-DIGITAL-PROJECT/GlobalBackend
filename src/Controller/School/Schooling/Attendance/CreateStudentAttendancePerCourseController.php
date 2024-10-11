<?php

namespace App\Controller\School\Schooling\Attendance;

use App\Entity\School\Schooling\Attendance\StudentAttendance;
use App\Entity\School\Schooling\Attendance\StudentAttendanceDetail;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\Repository\School\Study\Teacher\TeacherRepository;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use App\Repository\Security\Session\YearRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsController]
final class CreateStudentAttendancePerCourseController extends AbstractController
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function jsondecode()
    {
        try {
            return file_get_contents('php://input') ?
                json_decode(file_get_contents('php://input'), false) :
                [];
        }catch (\Exception $ex)
        {
            return [];
        }

    }

    public function __invoke(Request $request,
                             EntityManagerInterface $manager,
                             StudentRepository $studentRepository,
                             StudentRegistrationRepository $studentRegistrationRepository,
                             YearRepository $yearRepository,
                             TimeTableModelDayCellRepository $timeTableModelDayCellRepository,
                             TeacherRepository $teacherRepository,
                             ClassProgramRepository $classProgramRepository)
    {
        $data = $this->jsondecode();

        $attendances = $data->attendanceids;

        // create a new entity and set its values
        $attendance = new StudentAttendance();

        $currentTeacher = $teacherRepository->findOneBy(['operator'=> $this->getUser()]);

        $attendance->setTeacher($currentTeacher);

        $filterCourse = preg_replace("/[^0-9]/", '', $data->course);
        $branchId = intval($filterCourse);

        //filtrer les cours dans timeTable
        $attendance->setClassProgram($timeTableModelDayCellRepository->find($branchId)->getCourse());
        $attendance->setClass($timeTableModelDayCellRepository->find($branchId)->getCourse()->getClass());

        $attendance->setUser($this->getUser());
        $attendance->setInstitution($this->getUser()->getInstitution());
        $attendance->setYear($this->getUser()->getCurrentYear());

        $manager->persist($attendance);

        foreach ($attendances as $item){

            $student = $studentRepository->findOneBy(['id' => $item->id]);

            $studRegistration = $studentRegistrationRepository->findOneBy(['student' => $student, 'currentYear' => $this->getUser()->getCurrentYear()]);

            $attendanceDetail = new StudentAttendanceDetail();

            $attendanceDetail->setStudentAttendance($attendance);
            $attendanceDetail->setStudent($studRegistration);
            $attendanceDetail->setIsPresent($item->presence);

            $attendanceDetail->setUser($this->getUser());
            $attendanceDetail->setInstitution($this->getUser()->getInstitution());
            $attendanceDetail->setYear($this->getUser()->getCurrentYear());

            $manager->persist($attendanceDetail);
        }

        // Flush the changes to the database
        $manager->flush();

        return $attendance;

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



