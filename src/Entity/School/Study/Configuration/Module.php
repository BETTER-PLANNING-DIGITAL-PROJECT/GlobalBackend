<?php

namespace App\Entity\School\Study\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Study\Configuration\ModuleRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ModuleRepository::class)]
#[ORM\Table(name: 'school_module')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/course-module/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Module:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/course-module',
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:Module:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/course-module',
            denormalizationContext: [
                'groups' => ['write:Module'],
            ],
             processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/course-module/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Module'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/course-module/{id}',
            requirements: ['id' => '\d+'],
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
class Module
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([ 'get:Module:collection','get:ClassProgram:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:Module:collection', 'write:Module'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Module:collection', 'write:Module','get:ClassProgram:collection'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Module:collection','write:Module'])]
    private ?int $position = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Module:collection','write:Module'])]
    private ?ModuleCategory $moduleCategory = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Module:collection','write:Module'])]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Module:collection', 'write:Module'])]
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
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

    public function getModuleCategory(): ?ModuleCategory
    {
        return $this->moduleCategory;
    }

    public function setModuleCategory(?ModuleCategory $moduleCategory): static
    {
        $this->moduleCategory = $moduleCategory;

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
