<?php

namespace App\Entity\Inventory;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Product\Item;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Inventory\StockMovementRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StockMovementRepository::class)]
#[ORM\Table(name: 'inventory_stock_movement')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/stock-movement/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:StockMovement:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/stock-movement',
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:StockMovement:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Post(
            uriTemplate: '/create/stock-movement',
            denormalizationContext: [
                'groups' => ['write:StockMovement'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/stock-movement/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:StockMovement'],
            ],
        ),
    ]
)]
class StockMovement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:StockMovement:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:StockMovement:collection','write:StockMovement'])]
    private ?string $reference = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:StockMovement:collection','write:StockMovement'])]
    private ?Item $item = null;

    #[ORM\Column]
    #[Groups(['get:StockMovement:collection','write:StockMovement'])]
    private ?float $quantity = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:StockMovement:collection','write:StockMovement'])]
    private ?float $unitCost = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StockMovement:collection','write:StockMovement'])]
    private ?Warehouse $fromWarehouse = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StockMovement:collection','write:StockMovement'])]
    private ?Location $fromLocation = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StockMovement:collection','write:StockMovement'])]
    private ?Warehouse $toWarehouse = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StockMovement:collection','write:StockMovement'])]
    private ?Location $toLocation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:StockMovement:collection','write:StockMovement'])]
    private ?\DateTimeImmutable $stockAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:StockMovement:collection','write:StockMovement'])]
    private ?\DateTimeImmutable $loseAt = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Groups(['get:StockMovement:collection','write:StockMovement'])]
    private ?string $note = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Groups(['get:StockMovement:collection','write:StockMovement'])]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:StockMovement:collection','write:StockMovement'])]
    private ?bool $isOut = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StockMovement:collection','write:StockMovement'])]
    private ?Stock $stock = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    #[Groups(['get:StockMovement:collection','write:StockMovement'])]
    private ?Branch $branch = null;

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

    public function isIsOut(): ?bool
    {
        return $this->isOut;
    }

    public function setIsOut(?bool $isOut): self
    {
        $this->isOut = $isOut;

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
    public function getUnitCost(): ?float
    {
        return $this->unitCost;
    }

    public function setUnitCost(?float $unitCost): self
    {
        $this->unitCost = $unitCost;

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

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

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

    public function getFromWarehouse(): ?Warehouse
    {
        return $this->fromWarehouse;
    }

    public function setFromWarehouse(?Warehouse $fromWarehouse): self
    {
        $this->fromWarehouse = $fromWarehouse;

        return $this;
    }

    public function getFromLocation(): ?Location
    {
        return $this->fromLocation;
    }

    public function setFromLocation(?Location $fromLocation): self
    {
        $this->fromLocation = $fromLocation;

        return $this;
    }

    public function getToWarehouse(): ?Warehouse
    {
        return $this->toWarehouse;
    }

    public function setToWarehouse(?Warehouse $toWarehouse): self
    {
        $this->toWarehouse = $toWarehouse;

        return $this;
    }

    public function getToLocation(): ?Location
    {
        return $this->toLocation;
    }

    public function setToLocation(?Location $toLocation): self
    {
        $this->toLocation = $toLocation;

        return $this;
    }

    public function getStockAt(): ?\DateTimeImmutable
    {
        return $this->stockAt;
    }

    public function setStockAt(?\DateTimeImmutable $stockAt): self
    {
        $this->stockAt = $stockAt;

        return $this;
    }

    public function getLoseAt(): ?\DateTimeImmutable
    {
        return $this->loseAt;
    }

    public function setLoseAt(?\DateTimeImmutable $loseAt): self
    {
        $this->loseAt = $loseAt;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getBranch(): ?Branch
    {
        return $this->branch;
    }

    public function setBranch(?Branch $branch): self
    {
        $this->branch = $branch;

        return $this;
    }
}
