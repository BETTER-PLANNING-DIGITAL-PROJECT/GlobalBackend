<?php

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Product\GetItemController;
use App\Controller\Product\ImportItemController;
use App\Controller\Product\PostItemController;
use App\Controller\Product\PutItemController;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Product\ItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
#[ORM\Table(name: 'product_item')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/item/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Item:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/item',
            controller: GetItemController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:Item:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Post(
            uriTemplate: '/create/item',
            controller: PostItemController::class,
            denormalizationContext: [
                'groups' => ['write:Item'],
            ],
        ),
        new Post(
            uriTemplate: '/import/item',
            controller: ImportItemController::class,
            openapiContext: [
                "summary" => "Add multiple Class resources.",
            ],
            denormalizationContext: [
                'groups' => ['write:Fee']
            ],
        ),
        new Put(
            uriTemplate: '/edit/item/{id}',
            requirements: ['id' => '\d+'],
            controller: PutItemController::class,
            denormalizationContext: [
                'groups' => ['write:Item'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/item/{id}',
            requirements: ['id' => '\d+'],
        )
    ]
)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Item:collection','get:SaleInvoiceItem:collection', 'get:PurchaseInvoiceItem:collection','get:SaleReturnInvoiceItem:collection','get:PurchaseReturnInvoiceItem:collection', 'get:Packaging:collection','get:Stock:collection','get:StockMovement:collection','get:DeliveryItem:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:Item:collection','write:Item','get:Stock:collection','get:DeliveryItem:collection'])]
    private ?string $reference = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:Item:collection','write:Item','get:SaleInvoiceItem:collection','get:PurchaseInvoiceItem:collection', 'get:SaleReturnInvoiceItem:collection', 'get:PurchaseReturnInvoiceItem:collection', 'get:Packaging:collection','get:Stock:collection','get:StockMovement:collection','get:DeliveryItem:collection'])]
    private ?string $name = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['get:Item:collection','write:Item','get:DeliveryItem:collection'])]
    private ?string $barcode = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?ItemType $itemType = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?ItemCategory $itemCategory = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?Unit $unit = null;

    #[ORM\ManyToOne]
    private ?Attribute $attribute = null;

    #[ORM\ManyToOne]
    private ?AttributeValue $attributeValue = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?bool $isPurchase = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?bool $isSale = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?bool $isRent = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?BillingPolicy $billingPolicy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?float $price = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?float $salePrice = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?float $cost = null;

    // End of General Information

    // Sale

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?bool $is_auto = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?bool $is_pos = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?string $sale_description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?string $purchase_description = null;

    // End of Sale

    // Inventory

    #[ORM\ManyToOne]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?User $manager = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?int $delivery_delay = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?string $reception_description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?string $delivery_description = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?string $uniqueNumber = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?bool $is_batch = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?string $batchNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?\DateTimeInterface $expirationAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?\DateTimeInterface $removeAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?\DateTimeInterface $alertAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?bool $is_tracking = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?int $position = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?float $weight = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?float $volume = null;

    // End of Inventory

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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Item:collection','write:Item'])]
    private ?Branch $branch;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;

        $this->isPurchase = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(?Attribute $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getAttributeValue(): ?AttributeValue
    {
        return $this->attributeValue;
    }

    public function setAttributeValue(?AttributeValue $attributeValue): self
    {
        $this->attributeValue = $attributeValue;

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

    public function getItemType(): ?ItemType
    {
        return $this->itemType;
    }

    public function setItemType(?ItemType $itemType): self
    {
        $this->itemType = $itemType;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getSalePrice(): ?float
    {
        return $this->salePrice;
    }

    public function setSalePrice(?float $salePrice): self
    {
        $this->salePrice = $salePrice;

        return $this;
    }

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function setCost(?float $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function getItemCategory(): ?ItemCategory
    {
        return $this->itemCategory;
    }

    public function setItemCategory(?ItemCategory $itemCategory): self
    {
        $this->itemCategory = $itemCategory;

        return $this;
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

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(?string $barcode): self
    {
        $this->barcode = $barcode;

        return $this;
    }

    public function isIsAuto(): ?bool
    {
        return $this->is_auto;
    }

    public function setIsAuto(?bool $is_auto): self
    {
        $this->is_auto = $is_auto;

        return $this;
    }

    public function isIsPos(): ?bool
    {
        return $this->is_pos;
    }

    public function setIsPos(?bool $is_pos): self
    {
        $this->is_pos = $is_pos;

        return $this;
    }

    public function getSaleDescription(): ?string
    {
        return $this->sale_description;
    }

    public function setSaleDescription(?string $sale_description): self
    {
        $this->sale_description = $sale_description;

        return $this;
    }

    public function getPurchaseDescription(): ?string
    {
        return $this->purchase_description;
    }

    public function setPurchaseDescription(?string $purchase_description): self
    {
        $this->purchase_description = $purchase_description;

        return $this;
    }

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function getDeliveryDelay(): ?int
    {
        return $this->delivery_delay;
    }

    public function setDeliveryDelay(?int $delivery_delay): self
    {
        $this->delivery_delay = $delivery_delay;

        return $this;
    }

    public function getReceptionDescription(): ?string
    {
        return $this->reception_description;
    }

    public function setReceptionDescription(?string $reception_description): self
    {
        $this->reception_description = $reception_description;

        return $this;
    }

    public function getDeliveryDescription(): ?string
    {
        return $this->delivery_description;
    }

    public function setDeliveryDescription(?string $delivery_description): self
    {
        $this->delivery_description = $delivery_description;

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

    public function getBillingPolicy(): ?BillingPolicy
    {
        return $this->billingPolicy;
    }

    public function setBillingPolicy(?BillingPolicy $billingPolicy): self
    {
        $this->billingPolicy = $billingPolicy;

        return $this;
    }

    public function getUniqueNumber(): ?string
    {
        return $this->uniqueNumber;
    }

    public function setUniqueNumber(?string $uniqueNumber): self
    {
        $this->uniqueNumber = $uniqueNumber;

        return $this;
    }

    public function isIsBatch(): ?bool
    {
        return $this->is_batch;
    }

    public function setIsBatch(?bool $is_batch): self
    {
        $this->is_batch = $is_batch;

        return $this;
    }

    public function getBatchNumber(): ?string
    {
        return $this->batchNumber;
    }

    public function setBatchNumber(?string $batchNumber): self
    {
        $this->batchNumber = $batchNumber;

        return $this;
    }

    public function getExpirationAt(): ?\DateTimeInterface
    {
        return $this->expirationAt;
    }

    public function setExpirationAt(?\DateTimeInterface $expirationAt): self
    {
        $this->expirationAt = $expirationAt;

        return $this;
    }

    public function getRemoveAt(): ?\DateTimeInterface
    {
        return $this->removeAt;
    }

    public function setRemoveAt(?\DateTimeInterface $removeAt): self
    {
        $this->removeAt = $removeAt;

        return $this;
    }

    public function getAlertAt(): ?\DateTimeInterface
    {
        return $this->alertAt;
    }

    public function setAlertAt(?\DateTimeInterface $alertAt): self
    {
        $this->alertAt = $alertAt;

        return $this;
    }

    public function isIsTracking(): ?bool
    {
        return $this->is_tracking;
    }

    public function setIsTracking(?bool $is_tracking): self
    {
        $this->is_tracking = $is_tracking;

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
    public function isIsPurchase(): ?bool
    {
        return $this->isPurchase;
    }

    public function setIsPurchase(?bool $isPurchase): self
    {
        $this->isPurchase = $isPurchase;

        return $this;
    }

    public function isIsSale(): ?bool
    {
        return $this->isSale;
    }

    public function setIsSale(?bool $isSale): self
    {
        $this->isSale = $isSale;

        return $this;
    }
    public function isIsRent(): ?bool
    {
        return $this->isRent;
    }

    public function setIsRent(?bool $isRent): self
    {
        $this->isRent = $isRent;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getVolume(): ?float
    {
        return $this->volume;
    }

    public function setVolume(?float $volume): self
    {
        $this->volume = $volume;

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
