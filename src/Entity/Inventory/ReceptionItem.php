<?php

namespace App\Entity\Inventory;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Product\Item;
use App\Entity\Product\Packaging;
use App\Entity\Product\Unit;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Inventory\ReceptionItemRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReceptionItemRepository::class)]
#[ORM\Table(name: 'inventory_reception_item')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/reception-item/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:ReceptionItem:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/reception-item',
            normalizationContext: [
                'groups' => ['get:ReceptionItem:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/reception-item',
            denormalizationContext: [
                'groups' => ['write:ReceptionItem'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/reception-item/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:ReceptionItem'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/reception-item/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class ReceptionItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:ReceptionItem:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:ReceptionItem:collection','write:ReceptionItem'])]
    private ?Reception $reception = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ReceptionItem:collection','write:ReceptionItem'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Item $item = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ReceptionItem:collection','write:ReceptionItem'])]
    private ?Packaging $packaging = null;

    #[ORM\Column]
    #[Groups(['get:ReceptionItem:collection','write:ReceptionItem'])]
    private ?float $quantity = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ReceptionItem:collection','write:ReceptionItem'])]
    private ?Unit $unit = null;

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

    public function getReception(): ?Reception
    {
        return $this->reception;
    }

    public function setReception(?Reception $reception): self
    {
        $this->reception = $reception;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getPackaging(): ?Packaging
    {
        return $this->packaging;
    }

    public function setPackaging(?Packaging $packaging): self
    {
        $this->packaging = $packaging;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit): self
    {
        $this->unit = $unit;

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