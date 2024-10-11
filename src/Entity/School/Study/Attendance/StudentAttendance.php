<?php

namespace App\Entity\School\Study\Attendance;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\School\Schooling\Attendance\CreateStudentAttendancePerClassController;
use App\Controller\School\Schooling\Attendance\CreateStudentAttendancePerCourseController;
use App\Controller\School\Schooling\Attendance\GetStudentAttendanceDetailController;
use App\Controller\School\Schooling\Attendance\GetStudentAttendancePerClassController;
use App\Controller\School\Schooling\Attendance\GetStudentAttendancePerCourseController;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\School\Study\Program\TeacherCourseRegistration;
use App\Entity\School\Study\Teacher\Teacher;
use App\Entity\School\Study\TimeTable\TimeTableModelDayCell;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Study\Program\StudentAttendanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StudentAttendanceRepository::class)]
#[ORM\Table(name: 'school_student_attendance')]
#[ApiResource(
    operations:[

        // Student attendance per course
        new GetCollection(
            uriTemplate: '/get/student-attendance-per-course',
            controller: GetStudentAttendancePerCourseController :: class,
            normalizationContext: [
                'groups' => ['get:StudentAttendance:collection'],
            ],
        ),

        new Post(
            uriTemplate: '/create/student-attendance-per-course',
            controller: CreateStudentAttendancePerCourseController :: class,
            denormalizationContext: [
                'groups' => ['write:StudentAttendance'],
            ],
        ),

        // Student attendance per class
        new GetCollection(
            uriTemplate: '/get/student-attendance-per-class',
            controller: GetStudentAttendancePerClassController :: class,
            normalizationContext: [
                'groups' => ['get:StudentAttendance:collection'],
            ],
        ),

        new Post(
            uriTemplate: '/create/student-attendance-per-class',
            controller: CreateStudentAttendancePerClassController :: class,
            denormalizationContext: [
                'groups' => ['write:StudentAttendance'],
            ],
        ),

        // Get student attendance detail
        new Get(
            uriTemplate: '/get/detail/student-attendance/{id}',
            requirements: ['id' => '\d+'],
            controller: GetStudentAttendanceDetailController :: class,
            normalizationContext: [
                'groups' => ['get:StudentAttendance:item'],
            ],
        ),

        /*new GetCollection(
            uriTemplate: '/get/student-attendance',
            normalizationContext: [
                'groups' => ['get:StudentAttendance:collection'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/student-attendance/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:StudentAttendance'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/student-attendance/{id}',
            requirements: ['id' => '\d+'],
        ),*/

    ]
)]
class StudentAttendance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:StudentAttendance:collection','get:StudentAttendanceDetail:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentAttendance:collection','write:StudentAttendance'])]
    private ?Teacher $teacher = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentAttendance:collection','write:StudentAttendance'])]
    private ?SchoolClass $class = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentAttendance:collection','write:StudentAttendance'])]
    private ?ClassProgram $classProgram = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentAttendance:collection','write:StudentAttendance'])]
    private ?TeacherCourseRegistration $teacherCourseReg = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentAttendance:collection','write:StudentAttendance'])]
    private ?TimeTableModelDayCell $timeTableModelDayCell = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:StudentAttendance:collection','write:StudentAttendance'])]
    private ?string $course = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:StudentAttendance:collection','write:StudentAttendance'])]
    private ?string $callerName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['get:StudentAttendance:collection','write:StudentAttendance'])]
    private ?\DateTimeInterface $attendanceDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:StudentAttendance:collection','write:StudentAttendance'])]
    private ?\DateTimeInterface $attendanceTime = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): self
    {
        $this->institution = $institution;

        return $this;
    }


    public function setTeacher(?Teacher $teacher): self
    {
        $this->teacher = $teacher;

        return $this;
    }

    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function setClass(?SchoolClass $class): self
    {
        $this->class = $class;

        return $this;
    }
    public function getClassProgram(): ?ClassProgram
    {
        return $this->classProgram;
    }

    public function setClassProgram(?ClassProgram $classProgram): self
    {
        $this->classProgram = $classProgram;

        return $this;
    }

    public function getTeacherCourseReg(): ?TeacherCourseRegistration
    {
        return $this->teacherCourseReg;
    }

    public function setTeacherCourseReg(?TeacherCourseRegistration $teacherCourseReg): self
    {
        $this->teacherCourseReg = $teacherCourseReg;

        return $this;
    }

    public function getCourse(): ?string
    {
        return $this->course;
    }

    public function setCourse(?string $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getCallerName(): ?string
    {
        return $this->callerName;
    }

    public function setCallerName(?string $callerName): self
    {
        $this->callerName = $callerName;

        return $this;
    }

    public function getAttendanceDate(): ?\DateTimeInterface
    {
        return $this->attendanceDate;
    }

    public function setAttendanceDate(\DateTimeInterface $attendanceDate): self
    {
        $this->attendanceDate = $attendanceDate;

        return $this;
    }

    public function getAttendanceTime(): ?\DateTimeInterface
    {
        return $this->attendanceTime;
    }

    public function setAttendanceTime(\DateTimeInterface $attendanceTime): self
    {
        $this->attendanceTime = $attendanceTime;

        return $this;
    }
    public function isIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(bool $is_enable): static
    {
        $this->is_enable = $is_enable;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getTimeTableModelDayCell(): ?TimeTableModelDayCell
    {
        return $this->timeTableModelDayCell;
    }

    public function setTimeTableModelDayCell(?TimeTableModelDayCell $timeTableModelDayCell): self
    {
        $this->timeTableModelDayCell = $timeTableModelDayCell;

        return $this;
    }

}
