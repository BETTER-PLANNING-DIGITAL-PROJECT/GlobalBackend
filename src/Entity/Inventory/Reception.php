<?php

namespace App\Entity\Inventory;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Inventory\Reception\GetReceptionController;
use App\Controller\Inventory\Reception\GetReceptionItemController;
use App\Controller\Inventory\Reception\ValidateReceptionController;
use App\Entity\Billing\Purchase\PurchaseInvoice;
use App\Entity\Billing\Sale\SaleReturnInvoice;
use App\Entity\Partner\Contact;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Inventory\OperationType;
use App\Repository\Inventory\ReceptionRepository;
use App\State\Processor\Inventory\Reception\CancelReceptionProcessor;
use App\State\Processor\Inventory\Reception\CreateReceptionItemProcessor;
use App\State\Processor\Inventory\Reception\DeleteReceptionProcessor;
use App\State\Processor\Inventory\Reception\GenerateReceptionProcessor;
use App\State\Processor\Inventory\Reception\PutReceptionProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReceptionRepository::class)]
#[ORM\Table(name: 'inventory_reception')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/reception/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Reception:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/receptions',
            controller: GetReceptionController::class,
            normalizationContext: [
                'groups' => ['get:Reception:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Post(
            uriTemplate: '/generate/reception',
            denormalizationContext: [
                'groups' => ['write:Reception'],
            ],
            processor: GenerateReceptionProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/reception/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Reception'],
            ],
            processor: PutReceptionProcessor::class,
        ),
        new Delete(
            uriTemplate: '/delete/reception/{id}',
            requirements: ['id' => '\d+'],
        ),

        // reception Item
        new Get(
            uriTemplate: '/get/reception/{id}/item',
            requirements: ['id' => '\d+'],
            controller: GetReceptionItemController::class,
            normalizationContext: [
                'groups' => ['get:Reception:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Put(
            uriTemplate: '/create/reception/{id}/item',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Reception'],
            ],
            processor: CreateReceptionItemProcessor::class,
        ),

        new Delete(
            uriTemplate: '/validate/reception/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidateReceptionController::class
        ),
        new Delete(
            uriTemplate: '/cancel/reception/{id}',
            requirements: ['id' => '\d+'],
            processor: CancelReceptionProcessor::class
        ),
        new Delete(
            uriTemplate: '/delete/reception/{id}',
            requirements: ['id' => '\d+'],
            processor: DeleteReceptionProcessor::class
        ),

    ]
)]
class Reception
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Reception:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Reception:collection', 'write:Reception'])]
    private ?SaleReturnInvoice $saleReturnInvoice = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Reception:collection','write:Reception'])]
    private ?Contact $contact = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Reception:collection','write:Reception'])]
    private ?PurchaseInvoice $purchaseInvoice = null;
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['get:Reception:collection','write:Reception'])]
    private ?\DateTimeInterface $receiveAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:Reception:collection','write:Reception'])]
    private ?string $reference = null;

    #[ORM\Column(length: 110, nullable: true)]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?string $otherReference = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:Reception:collection','write:Reception'])]
    private ?string $status = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?string $otherStatus = null;

//    #[ORM\Column(length: 100, nullable: true)]
//    #[Groups(['get:Reception:collection','write:Reception'])]
//    private ?string $shippingAddress = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Reception:collection','write:Reception'])]
    private ?OperationType $operationType = null;

    #[ORM\Column(length: 110, nullable: true)]
    #[Groups(['get:Reception:collection','write:Reception'])]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Reception:collection','write:Reception'])]
    private ?string $OriginalDocument = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Reception:collection','write:Reception'])]
    private ?\DateTimeImmutable $validateAt = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Reception:collection','write:Reception'])]
    private ?User $validateBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Reception:collection','write:Reception'])]
    private ?bool $isValidate = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Reception:collection','write:Reception'])]
    private ?Branch $branch;

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

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): self
    {
        $this->contact = $contact;

        return $this;
    }


    public function getReceiveAt(): ?\DateTimeInterface
    {
        return $this->receiveAt;
    }

    public function setReceiveAt(\DateTimeInterface $receiveAt): self
    {
        $this->receiveAt = $receiveAt;

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
    public function getPurchaseInvoice(): ?PurchaseInvoice
    {
        return $this->purchaseInvoice;
    }

    public function setPurchaseInvoice(?PurchaseInvoice $purchaseInvoice): self
    {
        $this->purchaseInvoice = $purchaseInvoice;

        return $this;
    }

    public function getOtherReference(): ?string
    {
        return $this->otherReference;
    }

    public function setOtherReference(?string $otherReference): self
    {
        $this->otherReference = $otherReference;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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
//    public function getShippingAddress(): ?string
//    {
//        return $this->shippingAddress;
//    }
//
//    public function setShippingAddress(?string $shippingAddress): self
//    {
//        $this->shippingAddress = $shippingAddress;
//
//        return $this;
//    }


    public function getOriginalDocument(): ?string
    {
        return $this->OriginalDocument;
    }

    public function setOriginalDocument(?string $OriginalDocument): self
    {
        $this->OriginalDocument = $OriginalDocument;

        return $this;
    }

    public function getOperationType(): ?OperationType
    {
        return $this->operationType;
    }

    public function setOperationType(?OperationType $operationType): self
    {
        $this->operationType = $operationType;

        return $this;
    }

    public function getValidateAt(): ?\DateTimeImmutable
    {
        return $this->validateAt;
    }

    public function setValidateAt(?\DateTimeImmutable $validateAt): self
    {
        $this->validateAt = $validateAt;

        return $this;
    }

    public function getValidateBy(): ?User
    {
        return $this->validateBy;
    }

    public function setValidateBy(?User $validateBy): self
    {
        $this->validateBy = $validateBy;

        return $this;
    }

    public function isIsValidate(): ?bool
    {
        return $this->isValidate;
    }

    public function setIsValidate(?bool $isValidate): self
    {
        $this->isValidate = $isValidate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getSaleReturnInvoice(): ?SaleReturnInvoice
    {
        return $this->saleReturnInvoice;
    }

    public function setSaleReturnInvoice(?SaleReturnInvoice $saleReturnInvoice): self
    {
        $this->saleReturnInvoice = $saleReturnInvoice;

        return $this;
    }

    public function getOtherStatus(): ?string
    {
        return $this->otherStatus;
    }

    public function setOtherStatus(?string $otherStatus): self
    {
        $this->otherStatus = $otherStatus;

        return $this;
    }
}
