<?php

namespace App\Entity\School\Study\Teacher;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\School\Study\Teacher\CoursePostponement\GetCoursePostponementByTeacherController;
use App\Controller\School\Study\Teacher\GetCoursePostponementController;
use App\Entity\School\Study\TimeTable\TimeTableModelDayCell;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Study\Teacher\CoursePostponementRepository;
use App\State\Processor\School\Study\Teacher\CoursePostponement\PostCoursePostponementProcessor;
use App\State\Processor\School\Study\Teacher\CoursePostponement\UnValidateCoursePostponementProcessor;
use App\State\Processor\School\Study\Teacher\CoursePostponement\ValidateCoursePostponementProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CoursePostponementRepository::class)]
#[ORM\Table(name: 'school_course_postponement')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/course-postponement/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:CoursePostponement:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/course-postponement/by/teacher',
            controller: GetCoursePostponementByTeacherController::class,
            normalizationContext: [
                'groups' => ['get:CoursePostponement:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/course-postponement',
            controller: GetCoursePostponementController::class,
            normalizationContext: [
                'groups' => ['get:CoursePostponement:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/course-postponement',
            denormalizationContext: [
                'groups' => ['write:CoursePostponement'],
            ],
            processor: PostCoursePostponementProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/course-postponement/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:CoursePostponement'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/course-postponement/{id}',
            requirements: ['id' => '\d+'],
        ),

        // Validate
        new Delete(
            uriTemplate: '/validate/course-postponement/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModel'],
            ],
            processor: ValidateCoursePostponementProcessor::class,

        ),

        // Un Validate
        new Delete(
            uriTemplate: '/reject/course-postponement/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModel'],
            ],
            processor: UnValidateCoursePostponementProcessor::class,

        ),

    ]
)]
class CoursePostponement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:CoursePostponement:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:CoursePostponement:collection', 'write:CoursePostponement'])]
    private ?TimeTableModelDayCell $course = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['get:CoursePostponement:collection', 'write:CoursePostponement'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:CoursePostponement:collection', 'write:CoursePostponement'])]
    private ?\DateTimeInterface $startAt = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:CoursePostponement:collection', 'write:CoursePostponement'])]
    private ?\DateTimeInterface $endAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:CoursePostponement:collection', 'write:CoursePostponement'])]
    private ?string $comment = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CoursePostponement:collection', 'write:CoursePostponement'])]
    private ?bool $isValidated = null;

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

    #[ORM\Column]
    private ?bool $is_enable = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourse(): ?TimeTableModelDayCell
    {
        return $this->course;
    }

    public function setCourse(?TimeTableModelDayCell $course): self
    {
        $this->course = $course;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

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

    public function isIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(bool $is_enable): self
    {
        $this->is_enable = $is_enable;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

}
