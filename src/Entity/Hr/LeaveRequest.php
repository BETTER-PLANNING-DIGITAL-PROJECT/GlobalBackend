<?php

namespace App\Entity\Hr;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\School\Study\Teacher\Teacher;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Hr\LeaveCharacter;
use App\Entity\Setting\Hr\LeaveType;
use App\Repository\Hr\LeaveRequestRepository;
use App\State\Processor\Global\SystemProcessor;
use App\State\Processor\Hr\UnValidateLeaveRequestProcessor;
use App\State\Processor\Hr\ValidateLeaveRequestProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LeaveRequestRepository::class)]
#[ORM\Table(name: 'hr_leave_request')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/leave-request/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:LeaveRequest:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/leave-request',
            normalizationContext: [
                'groups' => ['get:LeaveRequest:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Post(
            uriTemplate: '/create/leave-request',
            denormalizationContext: [
                'groups' => ['write:LeaveRequest'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/leave-request/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:LeaveRequest'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Delete(
            uriTemplate: '/delete/leave-request/{id}',
            requirements: ['id' => '\d+'],
        ),

        // Validate begin
        new Delete(
            uriTemplate: '/validate/leave-request/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:LeaveRequest'],
            ],
            processor: ValidateLeaveRequestProcessor::class,

        ),

        // Un Validate
        new Delete(
            uriTemplate: '/un-validate/leave-request/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:LeaveRequest'],
            ],
            processor: UnValidateLeaveRequestProcessor::class,

        ),
    ]
)]
class LeaveRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:LeaveRequest:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:LeaveRequest:collection', 'write:LeaveRequest'])]
    private ?leaveType $leaveType = null;

    #[ORM\ManyToOne]
    #[Groups(['get:LeaveRequest:collection', 'write:LeaveRequest'])]
    private ?Teacher $teacher = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:LeaveRequest:collection', 'write:LeaveRequest'])]
    private ?LeaveCharacter $leaveCharacter = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(['get:LeaveRequest:collection', 'write:LeaveRequest'])]
    private ?\DateTimeInterface $startAt = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(['get:LeaveRequest:collection', 'write:LeaveRequest'])]
    private ?\DateTimeInterface $endAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:LeaveRequest:collection', 'write:LeaveRequest'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:LeaveRequest:collection', 'write:LeaveRequest'])]
    private ?bool $isValidated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    private ?User $user = null;

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

    public function getLeaveType(): ?leaveType
    {
        return $this->leaveType;
    }

    public function setLeaveType(?leaveType $leaveType): self
    {
        $this->leaveType = $leaveType;

        return $this;
    }

    public function getLeaveCharacter(): ?LeaveCharacter
    {
        return $this->leaveCharacter;
    }

    public function setLeaveCharacter(?LeaveCharacter $leaveCharacter): self
    {
        $this->leaveCharacter = $leaveCharacter;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): self
    {
        $this->teacher = $teacher;

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

    public function isIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): self
    {
        $this->isValidated = $isValidated;

        return $this;
    }
}
