<?php

namespace App\Entity\School\Schooling\Discipline;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\School\Schooling\Discipline\GetSummonParentsController;
use App\Controller\School\Schooling\Discipline\PostSummonParentsController;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Discipline\SummonParentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SummonParentRepository::class)]
#[ORM\Table(name: 'school_summon_parent')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/summon-parent/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:SummonParent:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/summon-parent',
            controller: GetSummonParentsController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:SummonParent:collection'],
            ]
        ),
        new Post(
            uriTemplate: '/create/summon-parent',
            controller: PostSummonParentsController::class,
            denormalizationContext: [
                'groups' => ['write:SummonParent'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/summon-parent/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SummonParent'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/summon-parent/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]

class SummonParent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([ 'get:SummonParent:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:SummonParent:collection', 'write:SummonParent'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:SummonParent:collection', 'write:SummonParent'])]
    private ?SchoolClass $schoolClass = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:SummonParent:collection', 'write:SummonParent'])]
    private ?Sequence $sequence = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    #[Groups(['get:SummonParent:collection', 'write:SummonParent'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: false)]
    #[Groups(['get:SummonParent:collection', 'write:SummonParent'])]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: false)]
    #[Groups(['get:SummonParent:collection', 'write:SummonParent'])]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SummonParent:collection', 'write:SummonParent'])]
    private ?bool $isJustified = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:SummonParent:collection', 'write:SummonParent'])]
    private ?string $observations = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:SummonParent:collection', 'write:SummonParent'])]
    private ?Reason $reason = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:SummonParent:collection', 'write:SummonParent'])]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SummonParent:collection', 'write:SummonParent'])]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SummonParent:collection', 'write:SummonParent'])]
    private ?Year $year = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: StudentRegistration::class, inversedBy: 'summonParents')]
    #[ORM\JoinTable(name: 'school_summon_parent_student_registration')]
    #[Groups(['get:SummonParent:collection', 'write:SummonParent'])]
    private Collection $studentRegistrations;


    public function __construct(){

        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
        $this->studentRegistrations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSchoolClass(): ?SchoolClass
    {
        return $this->schoolClass;
    }

    public function setSchoolClass(?SchoolClass $schoolClass): self
    {
        $this->schoolClass = $schoolClass;

        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getSequence(): ?Sequence
    {
        return $this->sequence;
    }

    public function setSequence(?Sequence $sequence): self
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function getReason(): ?Reason
    {
        return $this->reason;
    }

    public function setReason(?Reason $reason): self
    {
        $this->reason = $reason;

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

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function isIsJustified(): ?bool
    {
        return $this->isJustified;
    }

    public function setIsJustified(?bool $isJustified): self
    {
        $this->isJustified = $isJustified;

        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): self
    {
        $this->observations = $observations;

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

    /**
     * @return Collection<int, StudentRegistration>
     */
    public function getStudentRegistrations(): Collection
    {
        return $this->studentRegistrations;
    }

    public function addStudentRegistration(StudentRegistration $studentRegistration): self
    {
        if (!$this->studentRegistrations->contains($studentRegistration)) {
            $this->studentRegistrations->add($studentRegistration);
        }

        return $this;
    }

    public function removeStudentRegistration(StudentRegistration $studentRegistration): self
    {
        $this->studentRegistrations->removeElement($studentRegistration);

        return $this;
    }

}
