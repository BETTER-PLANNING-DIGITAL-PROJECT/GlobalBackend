<?php

namespace App\Entity\Billing\Purchase;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Inventory\Stock;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemStockRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PurchaseReturnInvoiceItemStockRepository::class)]
#[ORM\Table(name: 'purchase_return_invoice_item_stock')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/purchase-return-invoice-item-stock',
            normalizationContext: [
                'groups' => ['get:PurchaseReturnInvoiceItemStock:collection'],
            ],
        ),
    ]
)]
class PurchaseReturnInvoiceItemStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:PurchaseReturnInvoiceItemStock:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PurchaseReturnInvoiceItemStock:collection'])]
    private ?PurchaseReturnInvoiceItem $purchaseReturnInvoiceItem = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PurchaseReturnInvoiceItemStock:collection'])]
    private ?Stock $stock = null;

    #[ORM\Column]
    #[Groups(['get:PurchaseReturnInvoiceItemStock:collection', 'write:PurchaseReturnInvoiceItemStock'])]
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

    public function getPurchaseReturnInvoiceItem(): ?PurchaseReturnInvoiceItem
    {
        return $this->purchaseReturnInvoiceItem;
    }

    public function setPurchaseReturnInvoiceItem(?PurchaseReturnInvoiceItem $purchaseReturnInvoiceItem): self
    {
        $this->purchaseReturnInvoiceItem = $purchaseReturnInvoiceItem;

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
