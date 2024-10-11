<?php

namespace App\Entity\Billing\Purchase;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Billing\Purchase\Return\DeletePurchaseReturnInvoiceItemController;
use App\Controller\Billing\Purchase\Return\EditPurchaseReturnInvoiceItemController;
use App\Entity\Product\Item;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Finance\Tax;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceItemRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PurchaseReturnInvoiceItemRepository::class)]
#[ORM\Table(name: 'purchase_return_invoice_item')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/purchase-return-invoice-item',
            normalizationContext: [
                'groups' => ['get:PurchaseReturnInvoiceItem:collection'],
            ],
        ),
        new Get(
            uriTemplate: '/get/purchase-return-invoice-item/{id}',
            normalizationContext: [
                'groups' => ['get:PurchaseReturnInvoiceItem:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/purchase-return-invoice-item',
            denormalizationContext: [
                'groups' => ['write:PurchaseReturnInvoiceItem'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/purchase-return-invoice-item/{id}',
            requirements: ['id' => '\d+'],
            controller: EditPurchaseReturnInvoiceItemController::class,
            denormalizationContext: [
                'groups' => ['write:PurchaseReturnInvoiceItem'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/purchase-return-invoice-item/{id}',
            requirements: ['id' => '\d+'],
            controller: DeletePurchaseReturnInvoiceItemController::class
        ),
    ]
)]
class PurchaseReturnInvoiceItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection', 'write:PurchaseReturnInvoiceItem'])]
    private ?PurchaseReturnInvoice $purchaseReturnInvoice = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection'])]
    private ?PurchaseInvoiceItem $purchaseInvoiceItem = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection', 'write:PurchaseReturnInvoiceItem'])]
    private ?Item $item = null;

    #[ORM\ManyToMany(targetEntity: Tax::class)]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection', 'write:PurchaseReturnInvoiceItem'])]
    #[ORM\JoinTable(name: 'purchase_return_invoice_item_taxes')]
    private Collection $taxes;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection', 'write:PurchaseReturnInvoiceItem'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection', 'write:PurchaseReturnInvoiceItem'])]
    private ?float $quantity = null;

    #[ORM\Column]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection', 'write:PurchaseReturnInvoiceItem'])]
    private ?float $pu = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection', 'write:PurchaseReturnInvoiceItem'])]
    private ?float $returnQuantity = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection', 'write:PurchaseReturnInvoiceItem'])]
    private ?float $amount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection', 'write:PurchaseReturnInvoiceItem'])]
    private ?float $discount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection', 'write:PurchaseReturnInvoiceItem'])]
    private ?float $discountAmount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection', 'write:PurchaseReturnInvoiceItem'])]
    private ?float $amountTtc = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection', 'write:PurchaseReturnInvoiceItem'])]
    private ?float $amountWithTva = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection', 'write:PurchaseReturnInvoiceItem'])]
    private ?float $amountWithTaxes = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection', 'write:PurchaseReturnInvoiceItem'])]
    private ?float $amountPaid = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseReturnInvoiceItem:collection', 'write:PurchaseReturnInvoiceItem'])]
    private ?float $balance = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Branch $branch = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
        $this->taxes = new ArrayCollection();
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

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPu(): ?float
    {
        return $this->pu;
    }

    public function setPu(float $pu): self
    {
        $this->pu = $pu;

        return $this;
    }

    public function getReturnQuantity(): ?float
    {
        return $this->returnQuantity;
    }

    public function setReturnQuantity(?float $returnQuantity): self
    {
        $this->returnQuantity = $returnQuantity;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
    public function getAmountTtc(): ?float
    {
        return $this->amountTtc;
    }

    public function setAmountTtc(?float $amountTtc): self
    {
        $this->amountTtc = $amountTtc;

        return $this;
    }
    public function getAmountWithTva(): ?float
    {
        return $this->amountWithTva;
    }

    public function setAmountWithTva(?float $amountWithTva): self
    {
        $this->amountWithTva = $amountWithTva;

        return $this;
    }

    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function setDiscount(?float $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function getDiscountAmount(): ?float
    {
        return $this->discountAmount;
    }

    public function setDiscountAmount(?float $discountAmount): self
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    public function getAmountWithTaxes(): ?float
    {
        return $this->amountWithTaxes;
    }

    public function setAmountWithTaxes(?float $amountWithTaxes): self
    {
        $this->amountWithTaxes = $amountWithTaxes;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Tax>
     */
    public function getTaxes(): Collection
    {
        return $this->taxes;
    }

    public function addTax(Tax $tax): self
    {
        if (!$this->taxes->contains($tax)) {
            $this->taxes->add($tax);
        }

        return $this;
    }

    public function removeTax(Tax $tax): self
    {
        $this->taxes->removeElement($tax);

        return $this;
    }

    public function getPurchaseReturnInvoice(): ?PurchaseReturnInvoice
    {
        return $this->purchaseReturnInvoice;
    }

    public function setPurchaseReturnInvoice(?PurchaseReturnInvoice $purchaseReturnInvoice): self
    {
        $this->purchaseReturnInvoice = $purchaseReturnInvoice;

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

    public function getAmountPaid(): ?float
    {
        return $this->amountPaid;
    }

    public function setAmountPaid(?float $amountPaid): self
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(?float $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getPurchaseInvoiceItem(): ?PurchaseInvoiceItem
    {
        return $this->purchaseInvoiceItem;
    }

    public function setPurchaseInvoiceItem(?PurchaseInvoiceItem $purchaseInvoiceItem): self
    {
        $this->purchaseInvoiceItem = $purchaseInvoiceItem;

        return $this;
    }


}
