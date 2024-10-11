<?php

namespace App\Entity\School\Study\Teacher;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\School\Study\Teacher\CoursePermutation\GetCoursePermutationByTeacherController;
use App\Entity\School\Study\TimeTable\TimeTableModelDayCell;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Study\Teacher\CoursePermutationRepository;
use App\State\Processor\Global\SystemProcessor;
use App\State\Processor\School\Study\Teacher\CoursePermutation\UnValidateCoursePermutationProcessor;
use App\State\Processor\School\Study\Teacher\CoursePermutation\ValidateCoursePermutationProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CoursePermutationRepository::class)]
#[ORM\Table(name: 'school_course_permutation')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/course-permutation/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:CoursePermutation:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/course-permutation',
            normalizationContext: [
                'groups' => ['get:CoursePermutation:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/course-permutation/by/teacher',
            controller: GetCoursePermutationByTeacherController::class,
            normalizationContext: [
                'groups' => ['get:CoursePermutation:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/course-permutation',
            denormalizationContext: [
                'groups' => ['write:CoursePermutation'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/course-permutation/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:CoursePermutation'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/course-permutation/{id}',
            requirements: ['id' => '\d+'],
        ),

        // Validate begin
        new Delete(
            uriTemplate: '/validate/course-permutation/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModel'],
            ],
            processor: ValidateCoursePermutationProcessor::class,

        ),

        // Un Validate
        new Delete(
            uriTemplate: '/un-validate/course-permutation/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModel'],
            ],
            processor: UnValidateCoursePermutationProcessor::class,

        ),

    ]
)]
class CoursePermutation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:CoursePermutation:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:CoursePermutation:collection', 'write:CoursePermutation'])]
    private ?TimeTableModelDayCell $course = null;

    #[ORM\ManyToOne]
    #[Groups(['get:CoursePermutation:collection', 'write:CoursePermutation'])]
    private ?TimeTableModelDayCell $otherCourse = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:CoursePermutation:collection', 'write:CoursePermutation'])]
    private ?string $comment = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CoursePermutation:collection', 'write:CoursePermutation'])]
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

    public function getOtherCourse(): ?TimeTableModelDayCell
    {
        return $this->otherCourse;
    }

    public function setOtherCourse(?TimeTableModelDayCell $otherCourse): self
    {
        $this->otherCourse = $otherCourse;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

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
