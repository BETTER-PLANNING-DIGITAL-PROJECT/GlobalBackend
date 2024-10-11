<?php

namespace App\Entity\Billing\School;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\School\Schooling\Configuration\Cycle;
use App\Entity\School\Schooling\Configuration\Level;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Configuration\Speciality;
use App\Entity\School\Schooling\Configuration\Fee;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\User;
use App\Repository\Billing\School\MoratoriumRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MoratoriumRepository::class)]
#[ORM\Table(name: 'billing_school_moratorium')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/moratorium',
            normalizationContext: [
                'groups' => ['get:Moratorium:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Post(
            uriTemplate: '/create/moratorium',
            denormalizationContext: [
                'groups' => ['write:Moratorium'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/moratorium/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Moratorium'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Delete(
            uriTemplate: '/delete/moratorium/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class Moratorium
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Moratorium:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Moratorium:collection', 'write:Moratorium'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Moratorium:collection', 'write:Moratorium'])]
    private ?Cycle $cycle = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Moratorium:collection', 'write:Moratorium'])]
    private ?Level $level = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Moratorium:collection', 'write:Moratorium'])]
    private ?SchoolClass $class = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Moratorium:collection', 'write:Moratorium'])]
    private ?StudentRegistration $registration = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Moratorium:collection', 'write:Moratorium'])]
    private ?Speciality $speciality = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Moratorium:collection', 'write:Moratorium'])]
    private ?int $dueDate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Moratorium:collection', 'write:Moratorium'])]
    private ?\DateTimeImmutable $deadLineAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Moratorium:collection', 'write:Moratorium'])]
    private ?\DateTimeImmutable $signatureDateAt = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Moratorium:collection', 'write:Moratorium'])]
    private ?MoratoriumSignatory $moratoriumSignatory = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Moratorium:collection', 'write:Moratorium'])]
    private ?Fee $fee = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Moratorium:collection', 'write:Moratorium'])]
    private ?float $amount = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $is_archive = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
        $this->is_archive = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDueDate(): ?int
    {
        return $this->dueDate;
    }

    public function setDueDate(?int $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getDeadLineAt(): ?\DateTimeImmutable
    {
        return $this->deadLineAt;
    }

    public function setDeadLineAt(?\DateTimeImmutable $deadLineAt): self
    {
        $this->deadLineAt = $deadLineAt;

        return $this;
    }

    public function getSignatureDateAt(): ?\DateTimeImmutable
    {
        return $this->signatureDateAt;
    }

    public function setSignatureDateAt(?\DateTimeImmutable $signatureDateAt): self
    {
        $this->signatureDateAt = $signatureDateAt;

        return $this;
    }

    public function getMoratoriumSignatory(): ?MoratoriumSignatory
    {
        return $this->moratoriumSignatory;
    }

    public function setMoratoriumSignatory(?MoratoriumSignatory $moratoriumSignatory): self
    {
        $this->moratoriumSignatory = $moratoriumSignatory;

        return $this;
    }

    public function getFee(): ?Fee
    {
        return $this->fee;
    }

    public function setFee(?Fee $fee): self
    {
        $this->fee = $fee;

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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

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

    public function getCycle(): ?Cycle
    {
        return $this->cycle;
    }

    public function setCycle(?Cycle $cycle): self
    {
        $this->cycle = $cycle;

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): self
    {
        $this->level = $level;

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

    public function getRegistration(): ?StudentRegistration
    {
        return $this->registration;
    }

    public function setRegistration(?StudentRegistration $registration): self
    {
        $this->registration = $registration;

        return $this;
    }

    public function getSpeciality(): ?Speciality
    {
        return $this->speciality;
    }

    public function setSpeciality(?Speciality $speciality): self
    {
        $this->speciality = $speciality;

        return $this;
    }

}
