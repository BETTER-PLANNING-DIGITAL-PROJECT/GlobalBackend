<?php

namespace App\Entity\Inventory;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Inventory\Delivery\GetDeliveryController;
use App\Controller\Inventory\Delivery\GetDeliveryItemController;
use App\Controller\Inventory\Delivery\StockOutDeliveryController;
use App\Controller\Inventory\Delivery\ValidateDeliveryController;
use App\Entity\Billing\Purchase\PurchaseReturnInvoice;
use App\Entity\Billing\Sale\SaleInvoice;
use App\Entity\Partner\Contact;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Inventory\OperationType;
use App\Repository\Inventory\DeliveryRepository;
use App\State\Processor\Inventory\Delivery\CancelDeliveryProcessor;
use App\State\Processor\Inventory\Delivery\CreateDeliveryItemProcessor;
use App\State\Processor\Inventory\Delivery\DeleteDeliveryProcessor;
use App\State\Processor\Inventory\Delivery\GenerateDeliveryProcessor;
use App\State\Processor\Inventory\Delivery\PutDeliveryProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DeliveryRepository::class)]
#[ORM\Table(name: 'inventory_delivery')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/delivery/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Delivery:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/delivery',
            controller: GetDeliveryController::class,
            normalizationContext: [
                'groups' => ['get:Delivery:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Post(
            uriTemplate: '/generate/delivery',
            denormalizationContext: [
                'groups' => ['write:Delivery'],
            ],
            processor: GenerateDeliveryProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/delivery/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Delivery'],
            ],
            processor: PutDeliveryProcessor::class,
        ),
        new Delete(
            uriTemplate: '/validate/delivery/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidateDeliveryController::class
        ),
        new Delete(
            uriTemplate: '/cancel/delivery/{id}',
            requirements: ['id' => '\d+'],
            processor: CancelDeliveryProcessor::class
        ),
        new Delete(
            uriTemplate: '/delivery/{id}/stock-out',
            requirements: ['id' => '\d+'],
            controller: StockOutDeliveryController::class,
            normalizationContext: [
                'groups' => ['get:Delivery:collection'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/delivery/{id}',
            requirements: ['id' => '\d+'],
            processor: DeleteDeliveryProcessor::class
        ),

        // delivery Item
        new Get(
            uriTemplate: '/get/delivery/{id}/item',
            requirements: ['id' => '\d+'],
            controller: GetDeliveryItemController::class,
            normalizationContext: [
                'groups' => ['get:Delivery:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Put(
            uriTemplate: '/create/delivery/{id}/item',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Delivery'],
            ],
            processor: CreateDeliveryItemProcessor::class,
        ),

    ]
)]

class Delivery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Delivery:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Delivery:collection', 'write:Delivery'])]
    private ?PurchaseReturnInvoice $purchaseReturnInvoice = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Delivery:collection', 'write:Delivery'])]
    private ?SaleInvoice $saleInvoice = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?Contact $contact = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?string $shippingAddress = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?OperationType $operationType = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?string $OriginalDocument = null;

    #[ORM\Column(length: 110)]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?string $reference = null;

    #[ORM\Column(length: 110, nullable: true)]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?string $otherReference = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?string $serialNumber = null;

    #[ORM\Column]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?\DateTimeImmutable $deliveryAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?string $picture = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?\DateTimeImmutable $validateAt = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?User $validateBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?bool $isValidate = null;

    #[ORM\Column(length: 100)]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?string $status = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?string $otherStatus = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Delivery:collection','write:Delivery'])]
    private ?Branch $branch;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $isEnable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->isEnable = true;
    }

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

    public function getOtherReference(): ?string
    {
        return $this->otherReference;
    }

    public function setOtherReference(?string $otherReference): self
    {
        $this->otherReference = $otherReference;

        return $this;
    }

    public function getDeliveryAt(): ?\DateTimeImmutable
    {
        return $this->deliveryAt;
    }

    public function setDeliveryAt(\DateTimeImmutable $deliveryAt): self
    {
        $this->deliveryAt = $deliveryAt;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): self
    {
        $this->institution = $institution;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function isIsEnable(): ?bool
    {
        return $this->isEnable;
    }

    public function setIsEnable(bool $isEnable): self
    {
        $this->isEnable = $isEnable;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getShippingAddress(): ?string
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(?string $shippingAddress): self
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

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

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): self
    {
        $this->contact = $contact;

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

    public function getSaleInvoice(): ?SaleInvoice
    {
        return $this->saleInvoice;
    }

    public function setSaleInvoice(?SaleInvoice $saleInvoice): self
    {
        $this->saleInvoice = $saleInvoice;

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