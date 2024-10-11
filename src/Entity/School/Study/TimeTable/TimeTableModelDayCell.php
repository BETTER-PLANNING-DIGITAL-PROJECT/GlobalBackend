<?php

namespace App\Entity\School\Study\TimeTable;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use App\Controller\School\Study\Score\TeacherCoursePlannedController;
use App\Controller\School\Study\Score\TeacherCoursePlannedTodayController;
use App\Controller\School\Study\Teacher\PlannedCoursesNotForCurrentTeacherController;
use App\Entity\School\Schooling\Configuration\Room;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\School\Study\Teacher\Teacher;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Study\TimeTable\TimeTableModelDayCellRepository;
use App\State\Processor\School\Study\Score\EndTeacherCourseProcessor;
use App\State\Processor\School\Study\Score\StartTeacherCourseProcessor;
use App\State\Processor\School\Study\Score\UnValidateTeacherCourseScoreProcessor;
use App\State\Processor\School\Study\Score\ValidateTeacherCourseScoreProcessor;
use App\State\Processor\School\Study\Timetable\SwapTimeTableModelDayCellProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TimeTableModelDayCellRepository::class)]
#[ORM\Table(name: 'school_time_table_model_day_cell')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/timetable-model/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:TimeTableModel:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/timetable-model-day-cell',
            normalizationContext: [
                'groups' => ['get:TimeTableModelDayCell:collection'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/timetable-model-day-cell/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModelDayCell'],
            ],
        ),
        new Put(
            uriTemplate: '/swap/timetable-model-day-cell/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModelDayCell'],
            ],
            processor: SwapTimeTableModelDayCellProcessor::class,
        ),
        new Delete(
            uriTemplate: '/delete/timetable-model-day-cell/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/get/current/teacher/planned/courses/timetable-model-day-cell/{id}',
            requirements: ['id' => '\d+'],
            controller: PlannedCoursesNotForCurrentTeacherController::class,
            denormalizationContext: [
                'groups' => ['write:TimeTableModelDayCell'],
            ],
        ),






        // Teacher courses planned : all
        new GetCollection(
            uriTemplate: '/get/teacher-course-planned',
            controller: TeacherCoursePlannedController::class,
            normalizationContext: [
                'groups' => ['get:TimeTableModelDayCell:collection'],
            ],
        ),

        // Teacher courses planned : today
        new GetCollection(
            uriTemplate: '/get/teacher-course-planned-today',
            controller: TeacherCoursePlannedTodayController::class,
            normalizationContext: [
                'groups' => ['get:TimeTableModelDayCell:collection'],
            ],
        ),

        // Course start
        new Delete(
            uriTemplate: '/start-time/teacher-course/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModelDayCell'],
            ],
            processor: StartTeacherCourseProcessor::class,
        ),

        // Course end
        new Delete(
            uriTemplate: '/end-time/teacher-course/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModelDayCell'],
            ],
            processor: EndTeacherCourseProcessor::class,
        ),

        // Validate course scoring
        new Delete(
            uriTemplate: '/validate/teacher-course/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModelDayCell'],
            ],
            processor: ValidateTeacherCourseScoreProcessor::class,

        ),

        // Un validate course scoring
        new Delete(
            uriTemplate: '/un-validate/teacher-course/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModelDayCell'],
            ],
            processor: UnValidateTeacherCourseScoreProcessor::class,

        ),

        /*new Post(
            uriTemplate: '/create/timetable-model-day-cell',
            denormalizationContext: [
                'groups' => ['write:TimeTableModelDayCell'],
            ],

            processor: TimeTableModelDayCellPostState::class,
        ),
        */

    ]
)]
class TimeTableModelDayCell
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:TimeTableModelDayCell:collection', 'get:CoursePostponement:collection', 'get:CoursePermutation:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:TimeTableModelDayCell:collection', 'write:TimeTableModelDayCell'])]
    private ?TimeTableModelDay $modelDay = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModelDayCell:collection', 'write:TimeTableModelDayCell'])]
    private ?\DateTimeInterface $startAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModelDayCell:collection', 'write:TimeTableModelDayCell'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModelDayCell:collection', 'write:TimeTableModelDayCell'])]
    private ?\DateTimeInterface $endAt = null;

    #[ORM\ManyToOne]
    #[Groups(['get:TimeTableModelDayCell:collection', 'write:TimeTableModelDayCell', 'get:CoursePostponement:collection', 'get:CoursePermutation:collection'])]
    private ?ClassProgram $course = null;

    #[ORM\ManyToOne]
    #[Groups(['get:TimeTableModelDayCell:collection', 'write:TimeTableModelDayCell', 'get:CoursePostponement:collection', 'get:CoursePermutation:collection'])]
    private ?Teacher $teacher = null;

    #[ORM\ManyToOne]
    #[Groups(['get:TimeTableModelDayCell:collection', 'write:TimeTableModelDayCell'])]
    private ?Room $room = null;

    #[ORM\ManyToOne]
    #[Groups(['get:TimeTableModelDayCell:collection', 'write:TimeTableModelDayCell'])]
    private ?TimeTableModel $model = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModelDayCell:collection', 'write:TimeTableModelDayCell'])]
    private ?\DateTimeInterface $courseStartTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModelDayCell:collection', 'write:TimeTableModelDayCell'])]
    private ?\DateTimeInterface $courseEndTime = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModelDayCell:collection', 'write:TimeTableModelDayCell'])]
    private ?bool $isValidated = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModelDayCell:collection', 'write:TimeTableModelDayCell'])]
    private ?bool $isScoreValidated = null;
    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModelDayCell:collection', 'write:TimeTableModelDayCell'])]
    private ?bool $isScoreNotValidated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isEnable = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->isEnable = true;
        $this->isValidated = false;
        $this->isScoreValidated = false;
        $this->isScoreNotValidated = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isIsEnable(): ?bool
    {
        return $this->isEnable;
    }

    public function setIsEnable(?bool $isEnable): self
    {
        $this->isEnable = $isEnable;

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


    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): static
    {
        $this->institution = $institution;

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


    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getModelDay(): ?TimeTableModelDay
    {
        return $this->modelDay;
    }

    public function setModelDay(?TimeTableModelDay $modelDay): self
    {
        $this->modelDay = $modelDay;

        return $this;
    }

    public function getCourse(): ?ClassProgram
    {
        return $this->course;
    }

    public function setCourse(?ClassProgram $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): self
    {
        $this->teacher = $teacher;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): self
    {
        $this->room = $room;

        return $this;
    }

    public function getModel(): ?TimeTableModel
    {
        return $this->model;
    }

    public function setModel(?TimeTableModel $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function isIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): self
    {
        $this->isValidated = $isValidated;

        return $this;
    }
    public function isIsScoreValidated(): ?bool
    {
        return $this->isScoreValidated;
    }

    public function setIsScoreValidated(?bool $isScoreValidated): self
    {
        $this->isScoreValidated = $isScoreValidated;

        return $this;
    }
    public function isIsScoreNotValidated(): ?bool
    {
        return $this->isScoreNotValidated;
    }

    public function setIsScoreNotValidated(?bool $isScoreNotValidated): self
    {
        $this->isScoreNotValidated = $isScoreNotValidated;

        return $this;
    }

    public function getCourseStartTime(): ?\DateTimeInterface
    {
        return $this->courseStartTime;
    }

    public function setCourseStartTime(\DateTimeInterface $courseStartTime): self
    {
        $this->courseStartTime = $courseStartTime;

        return $this;
    }

    public function getCourseEndTime(): ?\DateTimeInterface
    {
        return $this->courseEndTime;
    }

    public function setCourseEndTime(\DateTimeInterface $courseEndTime): self
    {
        $this->courseEndTime = $courseEndTime;

        return $this;
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

    public function getTimeTableModelCellSerialize()
    {
        return [
            '@id' => "/api/timetable_model_day_cells/".$this->getId(),
            '@type' => "TimeTableModelDayCell",
            'id'=> $this->getId(),
            'course'=> $this->getCourse(),
        ];
    }


}
