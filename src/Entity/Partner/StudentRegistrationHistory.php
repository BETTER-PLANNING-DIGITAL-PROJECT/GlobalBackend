<?php

namespace App\Entity\Partner;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Partner\StudentRegistrationHistoryRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StudentRegistrationHistoryRepository::class)]
#[ORM\Table(name: 'school_student_registration_history')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/student-registration-history/{id}/',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:StudentRegistrationHistory:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/student-registration-history',
            normalizationContext: [
                'groups' => ['get:StudentRegistrationHistory:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/student-registration-history',
            denormalizationContext: [
                'groups' => ['write:StudentRegistrationHistory'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/student-registration-history/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:StudentRegistrationHistory'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/student-registration-history/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]

)]
class StudentRegistrationHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:StudentRegistrationHistory:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentRegistrationHistory:collection', 'write:StudentRegistrationHistory'])]
    private ?StudentRegistration $studentRegistration = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:StudentRegistrationHistory:collection', 'write:StudentRegistrationHistory'])]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:StudentRegistrationHistory:collection', 'write:StudentRegistrationHistory'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:StudentRegistrationHistory:collection', 'write:StudentRegistrationHistory'])]
    private ?float $debit = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:StudentRegistrationHistory:collection', 'write:StudentRegistrationHistory'])]
    private ?float $credit = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:StudentRegistrationHistory:collection', 'write:StudentRegistrationHistory'])]
    private ?float $balance = null;

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

    public function getStudentRegistration(): ?StudentRegistration
    {
        return $this->studentRegistration;
    }

    public function setStudentRegistration(?StudentRegistration $studentRegistration): self
    {
        $this->studentRegistration = $studentRegistration;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDebit(): ?float
    {
        return $this->debit;
    }

    public function setDebit(?float $debit): self
    {
        $this->debit = $debit;

        return $this;
    }

    public function getCredit(): ?float
    {
        return $this->credit;
    }

    public function setCredit(?float $credit): self
    {
        $this->credit = $credit;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(?float $balance): self
    {
        $this->balance = $balance;

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
