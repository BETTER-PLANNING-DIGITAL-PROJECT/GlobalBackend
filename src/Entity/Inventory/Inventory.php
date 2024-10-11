<?php

namespace App\Entity\Inventory;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Inventory\InventoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
#[ApiResource]
class Inventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;
	
	// ManyToOne with Warehouse
	
	// inventoryAt
	
	// adjustmentAt
	// adjustBy
	// isAdjust

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }
}
