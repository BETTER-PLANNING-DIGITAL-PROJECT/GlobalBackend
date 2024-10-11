<?php

namespace App\Entity\Inventory;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Package\Package;
use App\Entity\Product\Item;
use App\Entity\Product\Unit;
use App\Repository\Inventory\InventoryItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryItemRepository::class)]
#[ApiResource]
class InventoryItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $item = null;

    #[ORM\ManyToOne]
    private ?Batch $batch = null;

    #[ORM\ManyToOne]
    private ?Package $package = null;
	
	// ManyToOne with Inventory 
	
	// ManyToOne with Stock 

    #[ORM\ManyToOne]
    private ?Unit $unit = null;

    #[ORM\Column]
    private ?float $quantity = null;

    #[ORM\Column(nullable: true)]
    private ?float $quantityCounted = null;

    #[ORM\Column(nullable: true)]
    private ?float $difference = null;

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

    public function getQuantityCounted(): ?float
    {
        return $this->quantityCounted;
    }

    public function setQuantityCounted(?float $quantityCounted): self
    {
        $this->quantityCounted = $quantityCounted;

        return $this;
    }

    public function getDifference(): ?float
    {
        return $this->difference;
    }

    public function setDifference(?float $difference): self
    {
        $this->difference = $difference;

        return $this;
    }
}
