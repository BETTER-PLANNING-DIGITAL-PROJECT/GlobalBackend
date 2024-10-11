<?php

namespace App\Entity\Setting\Inventory;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Setting\Inventory\StockStrategyRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StockStrategyRepository::class)]
#[ORM\Table(name: 'setting_stock_strategy')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/stock-strategy/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:StockStrategy:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/stock-strategy',
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:StockStrategy:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/stock-strategy',
            denormalizationContext: [
                'groups' => ['write:StockStrategy'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/stock-strategy/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:StockStrategy'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/stock-strategy/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/stock-strategy',
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
#[ApiResource]
class StockStrategy
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:StockStrategy:collection','get:ItemCategory:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    #[Groups(['get:StockStrategy:collection','write:StockStrategy'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:StockStrategy:collection','write:StockStrategy','get:ItemCategory:collection'])]
    private ?string $name = null;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
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
