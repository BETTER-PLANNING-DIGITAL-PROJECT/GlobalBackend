<?php

namespace App\Entity\Inventory;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Billing\Sale\SaleReturnInvoiceItem;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Inventory\ReceptionItemStockRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReceptionItemStockRepository::class)]
#[ORM\Table(name: 'inventory_reception_item_stock')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/reception-item-stock',
            normalizationContext: [
                'groups' => ['get:ReceptionItemStock:collection'],
            ],
        ),
    ]
)]
class ReceptionItemStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:ReceptionItemStock:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ReceptionItemStock:collection'])]
    private ?SaleReturnInvoiceItem $saleReturnInvoiceItem = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ReceptionItemStock:collection'])]
    private ?ReceptionItem $receptionItem = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ReceptionItemStock:collection'])]
    private ?Stock $stock = null;

    #[ORM\Column]
    #[Groups(['get:ReceptionItemStock:collection', 'write:ReceptionItemStock'])]
    private ?float $quantity = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Branch $branch = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSaleReturnInvoiceItem(): ?SaleReturnInvoiceItem
    {
        return $this->saleReturnInvoiceItem;
    }

    public function setSaleReturnInvoiceItem(?SaleReturnInvoiceItem $saleReturnInvoiceItem): self
    {
        $this->saleReturnInvoiceItem = $saleReturnInvoiceItem;

        return $this;
    }

    public function getReceptionItem(): ?ReceptionItem
    {
        return $this->receptionItem;
    }

    public function setReceptionItem(?ReceptionItem $receptionItem): self
    {
        $this->receptionItem = $receptionItem;

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

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

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
