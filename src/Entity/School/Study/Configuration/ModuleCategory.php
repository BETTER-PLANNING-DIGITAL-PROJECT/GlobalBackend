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
use App\Repository\School\Study\Configuration\ModuleCategoryRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ModuleCategoryRepository::class)]
#[ORM\Table(name: 'school_module_category')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/module-category/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:ModuleCategory:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/module-category',
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:ModuleCategory:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/module-category',
            denormalizationContext: [
                'groups' => ['write:ModuleCategory'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/module-category/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:ModuleCategory'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/module-category/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'this name already exist',
)]
class ModuleCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:ModuleCategory:collection','get:Module:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:ModuleCategory:collection', 'get:Module:collection', 'write:ModuleCategory'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'position may not be blank')]
    #[Groups(['get:ModuleCategory:collection', 'write:ModuleCategory'])]
    private ?int $position = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    #[Groups(['get:ModuleCategory:collection', 'write:ModuleCategory'])]
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

    public function setName(string $name): static
    {
        $this->name = $name;

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
