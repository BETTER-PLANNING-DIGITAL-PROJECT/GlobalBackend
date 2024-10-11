<?php

namespace App\Entity\School\Schooling\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Controller\School\Schooling\Configuration\ImportSpecialityController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SpecialityRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SpecialityRepository::class)]
#[ORM\Table(name: 'school_speciality')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/speciality/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Speciality:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/speciality',
            normalizationContext: [
                'groups' => ['get:Speciality:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/speciality',
            denormalizationContext: [
                'groups' => ['write:Speciality'],
            ],
            processor: SystemProcessor::class,

        ),
        new Put(
            uriTemplate: '/edit/speciality/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Speciality'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/speciality/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Post(
            uriTemplate: '/import/speciality',
            controller: ImportSpecialityController::class,
            openapiContext: [
                "summary" => "Add multiple Speciality resources.",

            ],
            denormalizationContext: [
                'groups' => ['post:Speciality']
            ],
        ),
        new Delete(
            uriTemplate: '/delete/selected/speciality',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['code'],
    message: 'this code already exist',
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'this name already exist',
)]
class Speciality
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Speciality:collection','get:Class:collection','get:Concour:collection','get:RegistrationForm:collection','get:TimeTableModelDay:collection','get:TimeTableModel:collection','get:Tuition:collection','get:NationalExam:collection','get:SpecialityYearlyQuota:collection','get:Option:collection','get:NoteType:collection','get:SpecialityWeighting:collection', 'get:StudentOnline:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Speciality:collection', 'write:Speciality'])]
    private ?Program $program = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Speciality:collection', 'write:Speciality'])]
    private ?Cycle $cycle = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Speciality:collection', 'write:Speciality'])]
    private ?Level $maximumLevel = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Speciality:collection', 'write:Speciality'])]
    private ?float $simpleHourlyRate = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:Speciality:collection', 'write:Speciality', 'get:Tuition:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Name cannot be smaller than {{ limit }} characters',
        maxMessage: 'Name cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:Speciality:collection', 'write:Speciality','get:NationalExam:collection','get:RegistrationForm:collection','get:TimeTableModelDay:collection','get:TimeTableModel:collection','get:Concour:collection','get:Tuition:collection','get:Class:collection','get:SpecialityYearlyQuota:collection','get:Option:collection','get:NoteType:collection','get:SpecialityWeighting:collection', 'get:StudentOnline:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Speciality:collection', 'write:Speciality'])]
    private ?Level $minimumLevel = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Speciality:collection', 'write:Speciality'])]
    private ?float $multipleHourlyRate = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

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

    public function getProgram(): ?Program
    {
        return $this->program;
    }

    public function setProgram(?Program $program): static
    {
        $this->program = $program;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCycle(): ?Cycle
    {
        return $this->cycle;
    }

    public function setCycle(?Cycle $cycle): static
    {
        $this->cycle = $cycle;

        return $this;
    }

    public function getMinimumLevel(): ?Level
    {
        return $this->minimumLevel;
    }

    public function setMinimumLevel(?Level $minimumLevel): static
    {
        $this->minimumLevel = $minimumLevel;

        return $this;
    }

    public function getMaximumLevel(): ?Level
    {
        return $this->maximumLevel;
    }

    public function setMaximumLevel(?Level $maximumLevel): static
    {
        $this->maximumLevel = $maximumLevel;

        return $this;
    }

    public function getSimpleHourlyRate(): ?float
    {
        return $this->simpleHourlyRate;
    }

    public function setSimpleHourlyRate(?float $simpleHourlyRate): static
    {
        $this->simpleHourlyRate = $simpleHourlyRate;

        return $this;
    }

    public function getMultipleHourlyRate(): ?float
    {
        return $this->multipleHourlyRate;
    }

    public function setMultipleHourlyRate(?float $multipleHourlyRate): static
    {
        $this->multipleHourlyRate = $multipleHourlyRate;

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
