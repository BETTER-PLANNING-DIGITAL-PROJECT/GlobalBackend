<?php

namespace App\Entity\School\Schooling\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Security\Institution\Institution;
use App\Repository\School\Schooling\Configuration\LevelRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LevelRepository::class)]
#[ORM\Table(name: 'school_level')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/level/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Level:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/level',
            normalizationContext: [
                'groups' => ['get:Level:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/level',
            denormalizationContext: [
                'groups' => ['write:Level'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/level/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Level'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/level/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/level',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This name already exist',
)]
class Level
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Level:collection', 'get:Option:collection', 'get:Diploma:collection','get:Concour:collection','get:TimeTableModel:collection','get:TimeTableModelDay:collection','get:Tuition:collection', 'get:NoteType:collection','get:Class:collection','get:Speciality:collection', 'get:RegistrationForm:collection', 'get:StudentOnline:collection'])]
    private ?int $id = null;

    #[ORM\Column(nullable: false)]
    #[Groups(['get:Level:collection', 'write:Level', 'get:Option:collection', 'get:Diploma:collection', 'get:Tuition:collection'])]
    private ?int $number = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank!')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Name cannot be smaller than {{ limit }} characters',
        maxMessage: 'Name cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:Level:collection', 'write:Level', 'get:Option:collection', 'get:Diploma:collection','get:Concour:collection','get:TimeTableModelDay:collection','get:TimeTableModel:collection', 'get:Tuition:collection', 'get:NoteType:collection','get:Class:collection','get:Speciality:collection', 'get:RegistrationForm:collection', 'get:StudentOnline:collection'])]
    private ?string $name = null;

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

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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
