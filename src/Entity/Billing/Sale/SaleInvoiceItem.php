<?php

namespace App\Entity\Billing\Sale;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Billing\Sale\Invoice\DeleteSaleInvoiceItemController;
use App\Entity\Partner\Customer;
use App\Entity\Product\Item;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Finance\Tax;
use App\Repository\Billing\Sale\SaleInvoiceItemRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SaleInvoiceItemRepository::class)]
#[ORM\Table(name: 'sale_invoice_item')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/sale-invoice-item',
            normalizationContext: [
                'groups' => ['get:SaleInvoiceItem:collection'],
            ],
        ),
        new Get(
            uriTemplate: '/get/sale-invoice/item/{id}',
            normalizationContext: [
                'groups' => ['get:SaleInvoiceItem:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/sale-invoice-item',
            denormalizationContext: [
                'groups' => ['write:SaleInvoiceItem'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/sale-invoice-item/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SaleInvoiceItem'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/sale-invoice-item/{id}',
            requirements: ['id' => '\d+'],
            controller: DeleteSaleInvoiceItemController::class
        ),
    ]
)]
class SaleInvoiceItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:SaleInvoiceItem:collection','get:SaleInvoiceItemTax:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleInvoiceItem:collection', 'write:SaleInvoiceItem'])]
    private ?SaleInvoice $saleInvoice = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleInvoiceItem:collection', 'write:SaleInvoiceItem'])]
    private ?Item $item = null;

    #[ORM\ManyToMany(targetEntity: Tax::class)]
    #[Groups(['get:SaleInvoiceItem:collection', 'write:SaleInvoiceItem'])]
    #[ORM\JoinTable(name: 'sale_invoice_item_taxes')]
    private Collection $taxes;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:SaleInvoiceItem:collection', 'write:SaleInvoiceItem','get:SaleInvoiceItemTax:collection'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['get:SaleInvoiceItem:collection', 'write:SaleInvoiceItem'])]
    private ?float $quantity = null;

    #[ORM\Column]
    #[Groups(['get:SaleInvoiceItem:collection', 'write:SaleInvoiceItem'])]
    private ?float $pu = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleInvoiceItem:collection', 'write:SaleInvoiceItem'])]
    private ?float $returnQuantity = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleInvoiceItem:collection', 'write:SaleInvoiceItem'])]
    private ?float $amount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleInvoiceItem:collection', 'write:SaleInvoiceItem'])]
    private ?float $amountTtc = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleInvoiceItem:collection', 'write:SaleInvoiceItem'])]
    private ?float $discount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleInvoiceItem:collection', 'write:SaleInvoiceItem'])]
    private ?float $discountAmount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleInvoiceItem:collection', 'write:SaleInvoiceItem'])]
    private ?float $amountWithTaxes = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleInvoiceItem:collection', 'write:SaleInvoiceItem'])]
    private ?float $amountPaid = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleInvoiceItem:collection', 'write:SaleInvoiceItem'])]
    private ?float $balance = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isTreat;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?StudentRegistration $studentRegistration = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?SchoolClass $class = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?Customer $customer = null;

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

        $this->isTreat = false;
        $this->is_enable = true;
        $this->taxes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSaleInvoice(): ?SaleInvoice
    {
        return $this->saleInvoice;
    }

    public function setSaleInvoice(?SaleInvoice $saleInvoice): self
    {
        $this->saleInvoice = $saleInvoice;

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

    public function isIsTreat(): ?bool
    {
        return $this->isTreat;
    }

    public function setIsTreat(?bool $isTreat): self
    {
        $this->isTreat = $isTreat;

        return $this;
    }

    public function getStudentRegistration(): ?StudentRegistration
    {
        return $this->studentRegistration;
    }

    public function setStudentRegistration(?StudentRegistration $studentRegistration): self
    {
        $this->studentRegistration = $studentRegistration;

        return $this;
    }

    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function setClass(?SchoolClass $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

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
