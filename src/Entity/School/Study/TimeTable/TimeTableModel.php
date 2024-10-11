<?php

namespace App\Entity\School\Study\TimeTable;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Entity\School\Schooling\Configuration\Campus;
use App\Entity\School\Schooling\Configuration\Cycle;
use App\Entity\School\Schooling\Configuration\Level;
use App\Entity\School\Schooling\Configuration\Room;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Configuration\Speciality;
use App\Entity\School\Schooling\Configuration\TrainingType;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Study\TimeTable\TimeTableModelRepository;
use App\State\Processor\School\Study\Timetable\DeleteTimeTableModelProcessor;
use App\State\Processor\School\Study\Timetable\DuplicateTimeTableModelProcessor;
use App\State\Processor\School\Study\Timetable\GenerateTimeTableModelProcessor;
use App\State\Processor\School\Study\Timetable\PublishTimeTableModelProcessor;
use App\State\Processor\School\Study\Timetable\UnPublishTimeTableModelProcessor;
use App\State\Processor\School\Study\Timetable\UnValidateTimeTableModelProcessor;
use App\State\Processor\School\Study\Timetable\ValidateTimeTableModelProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TimeTableModelRepository::class)]
#[ORM\Table(name: 'school_time_table_model')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/timetable-model/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:TimeTableModel:collection'],
            ],
        ),
        // Get Collection
        new GetCollection(
            uriTemplate: '/get/timetable-model',
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:TimeTableModel:collection'],
            ],
        ),

        // Generate
        new Post(
            uriTemplate: '/generate/timetable-model',
            denormalizationContext: [
                'groups' => ['write:TimeTableModel'],
            ],
            processor: GenerateTimeTableModelProcessor::class,
        ),

        // Delete
        new Delete(
            uriTemplate: '/delete/timetable-model/{id}',
            requirements: ['id' => '\d+'],
            processor: DeleteTimeTableModelProcessor::class,
        ),

        // Validate
        new Delete(
            uriTemplate: '/validate/timetable-model/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModel'],
            ],
            processor: ValidateTimeTableModelProcessor::class,

        ),

        // Un Validate
        new Delete(
            uriTemplate: '/un-validate/timetable-model/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModel'],
            ],
            processor: UnValidateTimeTableModelProcessor::class,

        ),

        //  Publish
        new Delete(
            uriTemplate: '/publish/timetable-model/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModel'],
            ],
            processor: PublishTimeTableModelProcessor::class,

        ),

        //  Un Publish
        new Delete(
            uriTemplate: '/un-publish/timetable-model/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModel'],
            ],
            processor: UnPublishTimeTableModelProcessor::class,

        ),

        // Duplicate
        new Delete(
            uriTemplate: '/duplicate/timetable-model/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModel'],
            ],
            processor: DuplicateTimeTableModelProcessor::class,

        ),

    ],
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'this Time Table Model already exist',
)]
class TimeTableModel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:TimeTableModel:collection','get:TimeTableModelDay:collection','get:TimeTableModelDayCell:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel', 'get:TimeTableModelDayCell:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Name may not be blank!')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Name cannot be smaller than {{ limit }} characters',
        maxMessage: 'Name cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel','get:TimeTableModelDay:collection','get:TimeTableModelDayCell:collection'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?bool $isTeacherAvailable = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?bool $isRoomAvailable = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?bool $isHourlyVolumeAvailable = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?string $status = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?Speciality $speciality = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?Level $level = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?TrainingType $trainingType = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?Room $mainRoom = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?Cycle $cycle = null;

    #[ORM\ManyToOne]
    #[Groups(['get:TimeTableModel:collection','write:TimeTableModel'])]
    private ?SchoolClass $class = null;

    #[ORM\ManyToOne]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel', 'get:TimeTableModelDay:collection'])]
    private ?TimeTablePeriod $timeTablePeriod = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel', 'get:TimeTableModelDayCell:collection'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel', 'get:TimeTableModelDayCell:collection'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?Campus $campus = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?\DateTimeInterface $mondayStart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?\DateTimeInterface $mondayEnd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?\DateTimeInterface $tuesdayStart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?\DateTimeInterface $tuesdayEnd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?\DateTimeInterface $wednesdayStart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?\DateTimeInterface $wednesdayEnd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?\DateTimeInterface $thursdayStart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?\DateTimeInterface $thursdayEnd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?\DateTimeInterface $fridayStart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?\DateTimeInterface $fridayEnd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?\DateTimeInterface $saturdayStart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?\DateTimeInterface $saturdayEnd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?\DateTimeInterface $sundayStart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?\DateTimeInterface $sundayEnd = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?bool $monday = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?bool $tuesday = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?bool $wednesday = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?bool $thursday = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?bool $friday = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?bool $saturday = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?bool $sunday = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?bool $isValidated = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModel:collection', 'write:TimeTableModel'])]
    private ?bool $isPublished = null;

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
        $this->isPublished = false;

    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isIsTeacherAvailable(): ?bool
    {
        return $this->isTeacherAvailable;
    }

    public function setIsTeacherAvailable(?bool $isTeacherAvailable): self
    {
        $this->isTeacherAvailable = $isTeacherAvailable;

        return $this;
    }

    public function isIsRoomAvailable(): ?bool
    {
        return $this->isRoomAvailable;
    }

    public function setIsRoomAvailable(?bool $isRoomAvailable): self
    {
        $this->isRoomAvailable = $isRoomAvailable;

        return $this;
    }

    public function isIsHourlyVolumeAvailable(): ?bool
    {
        return $this->isHourlyVolumeAvailable;
    }

    public function setIsHourlyVolumeAvailable(?bool $isHourlyVolumeAvailable): self
    {
        $this->isHourlyVolumeAvailable = $isHourlyVolumeAvailable;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSpeciality(): ?Speciality
    {
        return $this->speciality;
    }

    public function setSpeciality(?Speciality $speciality): static
    {
        $this->speciality = $speciality;

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getTrainingType(): ?TrainingType
    {
        return $this->trainingType;
    }

    public function setTrainingType(?TrainingType $trainingType): static
    {
        $this->trainingType = $trainingType;

        return $this;
    }

    public function getMainRoom(): ?Room
    {
        return $this->mainRoom;
    }

    public function setMainRoom(?Room $mainRoom): static
    {
        $this->mainRoom = $mainRoom;

        return $this;
    }

    public function getCycle(): ?Cycle
    {
        return $this->cycle;
    }

    public function setCycle(?Cycle $cycle): static
    {
        $this->cycle = $cycle;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
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

    public function getMondayStart(): ?\DateTimeInterface
    {
        return $this->mondayStart;
    }

    public function setMondayStart(?\DateTimeInterface $mondayStart): self
    {
        $this->mondayStart = $mondayStart;

        return $this;
    }

    public function getMondayEnd(): ?\DateTimeInterface
    {
        return $this->mondayEnd;
    }

    public function setMondayEnd(?\DateTimeInterface $mondayEnd): self
    {
        $this->mondayEnd = $mondayEnd;

        return $this;
    }

    public function getTuesdayStart(): ?\DateTimeInterface
    {
        return $this->tuesdayStart;
    }

    public function setTuesdayStart(?\DateTimeInterface $tuesdayStart): self
    {
        $this->tuesdayStart = $tuesdayStart;

        return $this;
    }

    public function getTuesdayEnd(): ?\DateTimeInterface
    {
        return $this->tuesdayEnd;
    }

    public function setTuesdayEnd(?\DateTimeInterface $tuesdayEnd): self
    {
        $this->tuesdayEnd = $tuesdayEnd;

        return $this;
    }


    public function getWednesdayStart(): ?\DateTimeInterface
    {
        return $this->wednesdayStart;
    }

    public function setWednesdayStart(?\DateTimeInterface $wednesdayStart): self
    {
        $this->wednesdayStart = $wednesdayStart;

        return $this;
    }

    public function getWednesdayEnd(): ?\DateTimeInterface
    {
        return $this->wednesdayEnd;
    }

    public function setWednesdayEnd(?\DateTimeInterface $wednesdayEnd): self
    {
        $this->wednesdayEnd = $wednesdayEnd;

        return $this;
    }

    public function getThursdayStart(): ?\DateTimeInterface
    {
        return $this->thursdayStart;
    }

    public function setThursdayStart(?\DateTimeInterface $thursdayStart): self
    {
        $this->thursdayStart = $thursdayStart;

        return $this;
    }

    public function getThursdayEnd(): ?\DateTimeInterface
    {
        return $this->thursdayEnd;
    }

    public function setThursdayEnd(?\DateTimeInterface $thursdayEnd): self
    {
        $this->thursdayEnd = $thursdayEnd;

        return $this;
    }

    public function getFridayStart(): ?\DateTimeInterface
    {
        return $this->fridayStart;
    }

    public function setFridayStart(?\DateTimeInterface $fridayStart): self
    {
        $this->fridayStart = $fridayStart;

        return $this;
    }

    public function getFridayEnd(): ?\DateTimeInterface
    {
        return $this->fridayEnd;
    }

    public function setFridayEnd(?\DateTimeInterface $fridayEnd): self
    {
        $this->fridayEnd = $fridayEnd;

        return $this;
    }

    public function getSaturdayStart(): ?\DateTimeInterface
    {
        return $this->saturdayStart;
    }

    public function setSaturdayStart(?\DateTimeInterface $saturdayStart): self
    {
        $this->saturdayStart = $saturdayStart;

        return $this;
    }

    public function getSaturdayEnd(): ?\DateTimeInterface
    {
        return $this->saturdayEnd;
    }

    public function setSaturdayEnd(?\DateTimeInterface $saturdayEnd): self
    {
        $this->saturdayEnd = $saturdayEnd;

        return $this;
    }

    public function getSundayStart(): ?\DateTimeInterface
    {
        return $this->sundayStart;
    }

    public function setSundayStart(?\DateTimeInterface $sundayStart): self
    {
        $this->sundayStart = $sundayStart;

        return $this;
    }

    public function getSundayEnd(): ?\DateTimeInterface
    {
        return $this->sundayEnd;
    }

    public function setSundayEnd(?\DateTimeInterface $sundayEnd): self
    {
        $this->sundayEnd = $sundayEnd;

        return $this;
    }

    public function isMonday(): ?bool
    {
        return $this->monday;
    }

    public function setMonday(?bool $monday): self
    {
        $this->monday = $monday;

        return $this;
    }

    public function isTuesday(): ?bool
    {
        return $this->tuesday;
    }

    public function setTuesday(?bool $tuesday): self
    {
        $this->tuesday = $tuesday;

        return $this;
    }

    public function isWednesday(): ?bool
    {
        return $this->wednesday;
    }

    public function setWednesday(?bool $wednesday): self
    {
        $this->wednesday = $wednesday;

        return $this;
    }

    public function isThursday(): ?bool
    {
        return $this->thursday;
    }

    public function setThursday(?bool $thursday): self
    {
        $this->thursday = $thursday;

        return $this;
    }

    public function isFriday(): ?bool
    {
        return $this->friday;
    }

    public function setFriday(?bool $friday): self
    {
        $this->friday = $friday;

        return $this;
    }

    public function isSaturday(): ?bool
    {
        return $this->saturday;
    }

    public function setSaturday(?bool $saturday): self
    {
        $this->saturday = $saturday;

        return $this;
    }

    public function isSunday(): ?bool
    {
        return $this->sunday;
    }

    public function setSunday(?bool $sunday): self
    {
        $this->sunday = $sunday;

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

    public function isIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(?bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getTimeTablePeriod(): ?TimeTablePeriod
    {
        return $this->timeTablePeriod;
    }

    public function setTimeTablePeriod(?TimeTablePeriod $timeTablePeriod): self
    {
        $this->timeTablePeriod = $timeTablePeriod;

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

    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function setClass(?SchoolClass $class): self
    {
        $this->class = $class;

        return $this;
    }

}
