<?php

namespace App\Entity\Security\Interface;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\Security\Interface\ModuleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ModuleRepository::class)]
#[ORM\Table(name: 'security_module')]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/get/module/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Module:item'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/modules',
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:Module:collection'],
            ]
        ),
        new Post(
            uriTemplate: '/create/module',
            denormalizationContext: [
                'groups' => ['write:Module']
            ]
        ),
        new Put(
            uriTemplate: '/edit/module/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Module']
            ]
        ),
        new Delete(
            uriTemplate: '/delete/module/{id}',
            requirements: ['id' => '\d+'],
        ),
//        new Delete(
//            uriTemplate: '/delete/selected/modules',
//            controller: DeleteSelectedResourceController::class,
//            openapiContext: [
//                "summary" => "Restore collections of api resource",
//            ],
//        ),

    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This name already exist',
)]
#[UniqueEntity(
    fields: ['position'],
    message: 'This position already exist',
)]
class Module
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Module:collection', 'get:Menu:collection', 'get:Role:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 110)]
    #[Assert\NotBlank]
    #[Groups(['get:Module:collection','write:Module', 'get:Menu:collection', 'get:Role:collection'])]
    private ?string $name = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['get:Module:collection','write:Module', 'get:Menu:collection', 'get:Role:collection'])]
    private ?string $color = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Module:collection','write:Module', 'get:Menu:collection', 'get:Role:collection'])]
    private ?string $icon = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Module:collection','write:Module', 'get:Menu:collection', 'get:Role:collection'])]
    private ?int $position = null;

    #[ORM\Column(length: 110, nullable: true)]
    #[Groups(['get:Module:collection','write:Module', 'get:Menu:collection', 'get:Role:collection'])]
    private ?string $path = null;

    #[ORM\Column(length: 110, nullable: true)]
    #[Groups(['get:Module:collection','write:Module', 'get:Menu:collection', 'get:Role:collection'])]
    private ?string $layout = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    #[Groups(['get:Module:collection','write:Module', 'get:Menu:collection', 'get:Role:collection'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(nullable: false)]
    #[Groups(['get:Module:collection','write:Module', 'get:Menu:collection', 'get:Role:collection'])]
    private ?bool $is_enable = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

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

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getLayout(): ?string
    {
        return $this->layout;
    }

    public function setLayout(?string $layout): self
    {
        $this->layout = $layout;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(?bool $is_enable): self
    {
        $this->is_enable = $is_enable;

        return $this;
    }

}
