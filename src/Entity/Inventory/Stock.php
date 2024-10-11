<?php

namespace App\Entity\Inventory;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\Inventory\CreateStockController;
use App\Controller\Inventory\GetStockController;
use App\Entity\Package\Package;
use App\Entity\Product\Item;
use App\Entity\Product\Unit;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Inventory\StockRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StockRepository::class)]
#[ORM\Table(name: 'inventory_stock')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/stock/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Stock:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/stock',
            controller: GetStockController::class,
            normalizationContext: [
                'groups' => ['get:Stock:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Post(
            uriTemplate: '/create/stock',
            controller: CreateStockController::class,
            denormalizationContext: [
                'groups' => ['write:Stock'],

            ],

        ),
        new Delete(
            uriTemplate: '/delete/stock/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Stock:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Stock:collection','write:Stock'])]
    private ?Item $item = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Stock:collection','write:Stock'])]
    private ?Batch $batch = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Stock:collection','write:Stock'])]
    private ?Package $package = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Stock:collection','write:Stock'])]
    private ?Unit $unit = null;

    #[ORM\Column]
    #[Groups(['get:Stock:collection','write:Stock'])]
    private ?float $quantity = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Stock:collection','write:Stock'])]
    private ?string $reference = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Stock:collection','write:Stock'])]
    private ?float $unitCost = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Stock:collection','write:Stock'])]
    private ?float $totalValue = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Stock:collection','write:Stock'])]
    private ?int $availableQte = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Stock:collection','write:Stock'])]
    private ?Location $location = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Stock:collection','write:Stock'])]
    private ?Warehouse $warehouse = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Stock:collection','write:Stock'])]
    private ?\DateTimeImmutable $stockAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Stock:collection','write:Stock'])]
    private ?\DateTimeImmutable $loseAt = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Groups(['get:Stock:collection','write:Stock'])]
    private ?string $note = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Stock:collection','write:Stock'])]
    private ?float $reserveQte = null;

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

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getBatch(): ?Batch
    {
        return $this->batch;
    }

    public function setBatch(?Batch $batch): self
    {
        $this->batch = $batch;

        return $this;
    }

    public function getPackage(): ?Package
    {
        return $this->package;
    }

    public function setPackage(?Package $package): self
    {
        $this->package = $package;

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

    public function getUnitCost(): ?float
    {
        return $this->unitCost;
    }

    public function setUnitCost(?float $unitCost): self
    {
        $this->unitCost = $unitCost;

        return $this;
    }

    public function getTotalValue(): ?float
    {
        return $this->totalValue;
    }

    public function setTotalValue(?float $totalValue): self
    {
        $this->totalValue = $totalValue;

        return $this;
    }

    public function getAvailableQte(): ?int
    {
        return $this->availableQte;
    }

    public function setAvailableQte(?int $availableQte): self
    {
        $this->availableQte = $availableQte;

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

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(?Warehouse $warehouse): self
    {
        $this->warehouse = $warehouse;

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

    public function getReserveQte(): ?float
    {
        return $this->reserveQte;
    }

    public function setReserveQte(?float $reserveQte): self
    {
        $this->reserveQte = $reserveQte;

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
