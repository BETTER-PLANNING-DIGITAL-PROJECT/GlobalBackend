<?php

namespace App\Entity\School\Study\Attendance;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Study\Attendance\StudentAttendanceDetailRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StudentAttendanceDetailRepository::class)]
#[ORM\Table(name: 'school_student_attendance_detail')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/student-attendance-detail',
            normalizationContext: [
                'groups' => ['get:StudentAttendanceDetails:collection'],
            ],
        ),

    ]
)]
class StudentAttendanceDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:StudentAttendanceDetail:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentAttendanceDetail:collection','write:StudentAttendanceDetail'])]
    private ?StudentAttendance $studentAttendance = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentAttendanceDetail:collection','write:StudentAttendanceDetail'])]
    private ?StudentRegistration $student = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:StudentAttendanceDetail:collection','write:StudentAttendanceDetail'])]
    private ?bool $isPresent = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->isPresent = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudentAttendance(): ?StudentAttendance
    {
        return $this->studentAttendance;
    }

    public function setStudentAttendance(?StudentAttendance $studentAttendance): self
    {
        $this->studentAttendance = $studentAttendance;

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

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): self
    {
        $this->institution = $institution;

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


    public function getStudent(): ?StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(?StudentRegistration $student): self
    {
        $this->student = $student;

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
