<?php

namespace App\Entity\School\Schooling\Discipline;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\School\Schooling\Discipline\GetSuspensionController;
use App\Controller\School\Schooling\Discipline\PostSuspensionController;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Discipline\SuspensionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SuspensionRepository::class)]
#[ORM\Table(name: 'school_suspension')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/suspension/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Suspension:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/suspension',
            controller: GetSuspensionController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:Suspension:collection'],
            ]
        ),
        new Post(
            uriTemplate: '/create/suspension',
            controller: PostSuspensionController::class,
            denormalizationContext: [
                'groups' => ['write:Suspension'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/suspension/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Suspension'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/suspension/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]

class Suspension
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([ 'get:Suspension:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Suspension:collection', 'write:Suspension'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Suspension:collection', 'write:Suspension'])]
    private ?SchoolClass $schoolClass = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Suspension:collection', 'write:Suspension'])]
    private ?Sequence $sequence = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    #[Groups(['get:Suspension:collection', 'write:Suspension'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    #[Groups(['get:Suspension:collection', 'write:Suspension'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Suspension:collection', 'write:Suspension'])]
    private ?bool $isJustified = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Suspension:collection', 'write:Suspension'])]
    private ?string $observations = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Suspension:collection', 'write:Suspension'])]
    private ?Motif $motif = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Suspension:collection', 'write:Suspension'])]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Suspension:collection', 'write:Suspension'])]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Suspension:collection', 'write:Suspension'])]
    private ?Year $year = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: StudentRegistration::class, inversedBy: 'suspensions')]
    #[ORM\JoinTable(name: 'school_suspension_student_registration')]
    #[Groups(['get:Suspension:collection', 'write:Suspension'])]
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

    public function getMotif(): ?Motif
    {
        return $this->motif;
    }

    public function setMotif(?Motif $motif): self
    {
        $this->motif = $motif;

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
