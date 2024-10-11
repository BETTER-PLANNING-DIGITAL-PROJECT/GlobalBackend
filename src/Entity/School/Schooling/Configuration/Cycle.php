<?php

namespace App\Entity\School\Schooling\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Controller\School\Schooling\Configuration\ImportCycleController;
use App\Entity\Security\Session\Year;
use App\Entity\Setting\Institution\Ministry;
use App\Entity\Security\User;
use App\Entity\Security\Institution\Institution;
use App\Repository\School\Schooling\Configuration\CycleRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CycleRepository::class)]
#[ORM\Table(name: 'school_cycle')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/cycle/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Cycle:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/cycle',
//            controller: CycleController::class,
            normalizationContext: [
                'groups' => ['get:Cycle:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/cycle',
            denormalizationContext: [
                'groups' => ['write:Cycle'],
            ],
            processor: SystemProcessor::class,
        ),
        new Post(
            uriTemplate: '/import/cycle',
            controller: ImportCycleController::class,
            openapiContext: [
                "summary" => "Add multiple Cycle resources.",
            ],
            denormalizationContext: [
                'groups' => ['post:Cycle']
            ],
        ),
        new Put(
            uriTemplate: '/edit/cycle/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Cycle'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/cycle/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/cycle',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['code'],
    message: 'This code already exist',
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This name already exist',
)]
class Cycle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Cycle:collection','get:Speciality:collection','get:CycleYearlyQuota:collection','get:Institution:collection','get:TimeTableModelDay:collection', 'get:TimeTableModel:collection','get:Tuition:collection','get:CycleWeighting:collection', 'get:StudentOnline:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank!')]
    #[Groups(['get:Cycle:collection', 'write:Cycle', 'get:Tuition:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Cycle:collection', 'write:Cycle','get:Speciality:collection','get:CycleYearlyQuota:collection','get:Institution:collection','get:TimeTableModelDay:collection','get:TimeTableModel:collection', 'get:Tuition:collection','get:CycleWeighting:collection', 'get:StudentOnline:collection'])]
    private ?string $name = null;

    #[ORM\Column]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Cycle:collection', 'write:Cycle'])]
    private ?int $position = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Cycle:collection', 'write:Cycle'])]
    private ?Ministry $ministry = null;

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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getMinistry(): ?Ministry
    {
        return $this->ministry;
    }

    public function setMinistry(?Ministry $ministry): static
    {
        $this->ministry = $ministry;

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
