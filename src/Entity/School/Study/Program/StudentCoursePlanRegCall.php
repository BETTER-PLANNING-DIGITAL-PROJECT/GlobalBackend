<?php

namespace App\Entity\School\Study\Program;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\User;
use App\Repository\School\Study\Program\StudentCoursePlanRegCallRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StudentCoursePlanRegCallRepository::class)]
#[ORM\Table(name: 'school_student_course_plan_reg_call')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/student-course-plan-reg-call/get',
            normalizationContext: [
                'groups' => ['get:StudentCoursePlanRegistrationCall:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/student-course-plan-reg-call/create',
            denormalizationContext: [
                'groups' => ['write:StudentCoursePlanRegistrationCall'],
            ],
        ),
        new Put(
            uriTemplate: '/student-course-plan-reg-call/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:StudentCoursePlanRegistrationCall'],
            ],
        ),
        new Delete(
            uriTemplate: '/student-course-plan-reg-call/delete/{id}',
            requirements: ['id' => '\d+'],
        ),

    ]
)]
class StudentCoursePlanRegCall
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:StudentCoursePlanRegistrationCall:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentCoursePlanRegistrationCall:collection','write:StudentCoursePlanRegistrationCall'])]
    private ?ClassProgram $course = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentCoursePlanRegistrationCall:collection','write:StudentCoursePlanRegistrationCall'])]
    private ?StudentRegistration $student = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:StudentCoursePlanRegistrationCall:collection','write:StudentCoursePlanRegistrationCall'])]
    private ?bool $isPresent = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:StudentCoursePlanRegistrationCall:collection','write:StudentCoursePlanRegistrationCall'])]
    private ?float $hourlyRateVolume = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?bool $is_archive = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;

        $this->isPresent = false;
    }
    
    public function getId(): ?int
    {
        return $this->id;
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

    public function getStudent(): ?StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(?StudentRegistration $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function isIsPresent(): ?bool
    {
        return $this->isPresent;
    }

    public function setIsPresent(?bool $isPresent): self
    {
        $this->isPresent = $isPresent;

        return $this;
    }

    public function getHourlyRateVolume(): ?float
    {
        return $this->hourlyRateVolume;
    }

    public function setHourlyRateVolume(?float $hourlyRateVolume): self
    {
        $this->hourlyRateVolume = $hourlyRateVolume;

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

    public function isIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(bool $is_enable): static
    {
        $this->is_enable = $is_enable;

        return $this;
    }

    public function isIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(bool $is_archive): static
    {
        $this->is_archive = $is_archive;

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
}
