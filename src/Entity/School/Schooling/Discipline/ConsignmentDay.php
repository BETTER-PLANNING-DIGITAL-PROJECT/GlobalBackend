<?php

namespace App\Entity\School\Schooling\Discipline;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\School\Schooling\Discipline\GetConsignmentDaysController;
use App\Controller\School\Schooling\Discipline\PostConsignmentDaysController;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Discipline\ConsignmentDayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ConsignmentDayRepository::class)]
#[ORM\Table(name: 'school_consignment_day')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/consignment-day/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:ConsignmentDay:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/consignment-day',
            controller: GetConsignmentDaysController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:ConsignmentDay:collection'],
            ]
        ),
        new Post(
            uriTemplate: '/create/consignment-day',
            controller: PostConsignmentDaysController::class,
            denormalizationContext: [
                'groups' => ['write:ConsignmentDay'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/consignment-day/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:ConsignmentDay'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/consignment-day/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]

class ConsignmentDay
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([ 'get:ConsignmentDay:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:ConsignmentDay:collection', 'write:ConsignmentDay'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:ConsignmentDay:collection', 'write:ConsignmentDay'])]
    private ?SchoolClass $schoolClass = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:ConsignmentDay:collection', 'write:ConsignmentDay'])]
    private ?Sequence $sequence = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    #[Groups(['get:ConsignmentDay:collection', 'write:ConsignmentDay'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    #[Groups(['get:ConsignmentDay:collection', 'write:ConsignmentDay'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ConsignmentDay:collection', 'write:ConsignmentDay'])]
    private ?bool $isJustified = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:ConsignmentDay:collection', 'write:ConsignmentDay'])]
    private ?string $observations = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:ConsignmentDay:collection', 'write:ConsignmentDay'])]
    private ?Motif $motif = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:ConsignmentDay:collection', 'write:ConsignmentDay'])]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ConsignmentDay:collection', 'write:ConsignmentDay'])]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ConsignmentDay:collection', 'write:ConsignmentDay'])]
    private ?Year $year = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: StudentRegistration::class, inversedBy: 'consignmentDays')]
    #[ORM\JoinTable(name: 'school_consignment_day_student_registration')]
    #[Groups(['get:ConsignmentDay:collection', 'write:ConsignmentDay'])]
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
