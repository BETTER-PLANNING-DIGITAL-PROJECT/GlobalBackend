<?php

namespace App\Entity\Billing\Purchase;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Billing\Purchase\Delivery\CreatePurchaseReturnInvoiceDeliveryController;
use App\Controller\Billing\Purchase\Delivery\CreatePurchaseReturnInvoiceDeliverySettlementStockOutValidateController;
use App\Controller\Billing\Purchase\Delivery\CreatePurchaseReturnInvoiceDeliveryValidateController;
use App\Controller\Billing\Purchase\Delivery\CreatePurchaseReturnInvoiceStockOutController;
use App\Controller\Billing\Purchase\GetPurchaseReturnInvoiceController;
use App\Controller\Billing\Purchase\Return\ClearPurchaseReturnInvoiceItemController;
use App\Controller\Billing\Purchase\Return\CreatePurchaseReturnInvoiceSettlementController;
use App\Controller\Billing\Purchase\Return\CreatePurchaseReturnInvoiceSettlementValidateController;
use App\Controller\Billing\Purchase\Return\GetPurchaseReturnInvoiceItemController;
use App\Controller\Billing\Purchase\Return\GetPurchaseReturnInvoiceTotalAmountController;
use App\Controller\Billing\Purchase\Return\ValidatePurchaseReturnInvoiceController;
use App\Entity\Partner\Supplier;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseReturnInvoiceRepository;
use App\State\Processor\Billing\Purchase\CancelPurchaseReturnInvoiceProcessor;
use App\State\Processor\Billing\Purchase\CreatePurchaseReturnInvoiceItemProcessor;
use App\State\Processor\Billing\Purchase\DeletePurchaseReturnInvoiceProcessor;
use App\State\Processor\Billing\Purchase\GeneratePurchaseReturnInvoiceProcessor;
use App\State\Processor\Billing\Purchase\PutPurchaseReturnInvoiceProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PurchaseReturnInvoiceRepository::class)]
#[ORM\Table(name: 'purchase_return_invoice')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/purchase-return-invoice/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:PurchaseReturnInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),

        new GetCollection(
            uriTemplate: '/get/purchase-return-invoice',
            controller: GetPurchaseReturnInvoiceController::class,
            normalizationContext: [
                'groups' => ['get:PurchaseReturnInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Post(
            uriTemplate: '/generate/purchase-return-invoice',
            denormalizationContext: [
                'groups' => ['write:PurchaseReturnInvoice'],
            ],
            processor: GeneratePurchaseReturnInvoiceProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/purchase-return-invoice/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:PurchaseReturnInvoice'],
            ],
            processor: PutPurchaseReturnInvoiceProcessor::class
        ),
        new Delete(
            uriTemplate: '/validate/purchase-return-invoice/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidatePurchaseReturnInvoiceController::class
        ),
        new Delete(
            uriTemplate: '/cancel/purchase-return-invoice/{id}',
            requirements: ['id' => '\d+'],
            processor: CancelPurchaseReturnInvoiceProcessor::class
        ),
        new Delete(
            uriTemplate: '/delete/purchase-return-invoice/{id}',
            requirements: ['id' => '\d+'],
            processor: DeletePurchaseReturnInvoiceProcessor::class
        ),

        // Purchase Return Invoice Item
        new Get(
            uriTemplate: '/get/purchase-return-invoice/{id}/item',
            requirements: ['id' => '\d+'],
            controller: GetPurchaseReturnInvoiceItemController::class,
            normalizationContext: [
                'groups' => ['get:PurchaseReturnInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Put(
            uriTemplate: '/create/purchase-return-invoice/{id}/item',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:PurchaseReturnInvoice'],
            ],
            processor: CreatePurchaseReturnInvoiceItemProcessor::class,
        ),
        new Delete(
            uriTemplate: '/clear/purchase-return-invoice/{id}/items',
            requirements: ['id' => '\d+'],
            controller: ClearPurchaseReturnInvoiceItemController::class,
            normalizationContext: [
                'groups' => ['get:PurchaseReturnInvoice:collection'],
            ],
        ),
        new Get(
            uriTemplate: '/get/purchase-return-invoice/{id}/total-amount',
            requirements: ['id' => '\d+'],
            controller: GetPurchaseReturnInvoiceTotalAmountController::class,
            normalizationContext: [
                'groups' => ['get:PurchaseReturnInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),

        // Settlement
        // Complete
        new Post(
            uriTemplate: '/create/purchase-return-invoice/{id}/settlement/delivery/stock/out/validate',
            requirements: ['id' => '\d+'],
            controller: CreatePurchaseReturnInvoiceDeliverySettlementStockOutValidateController::class,
            openapiContext: [
                "summary" => "Create custom settlement with that resources.",
                "requestBody" => [
                    "description" => "Customization of our endpoint.",
                    "required" => true,
                    "content"=>[
                        "multipart/form-data" => [
                            "schema" => [
                                "properties" => [
                                    "code" => [
                                        "description" => "The code of the institution",
                                        "type" => "string",
                                        "example" => "Clark Kent",
                                    ],
                                    "name" => [
                                        "description" => "The name of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "email" => [
                                        "description" => "The email of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "phone" => [
                                        "description" => "The phone of the institution",
                                        "type" => "integer",
                                        "example" => "superman",
                                    ],
                                    "address" => [
                                        "description" => "The username of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "website" => [
                                        "description" => "The password of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "postalCode" => [
                                        "description" => "The postalCode of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "city" => [
                                        "description" => "The city of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "region" => [
                                        "description" => "The region of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "manager" => [
                                        "description" => "The manager of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "managerType" => [
                                        "description" => "The password of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "picture" => [
                                        "type" => "string",
                                        "format" => "binary",
                                        "description" => "Upload a cover image of the institution",
                                    ],
                                ],
                            ],


                        ]
                    ],

                    "responses"=>[
                        "200" => ["description" => "MyEntity resources created successfully."],
                        "400" => ["description" => "Invalid request body."],
                        "404" => ["description" => "MyEntity resources not found."]
                    ]

                ]],
            denormalizationContext: [
                'groups' => ['write:PurchaseReturnInvoice'],
            ],
            deserialize: false
        ),

        // Draft Settlement
        new Post(
            uriTemplate: '/create/purchase-return-invoice/{id}/settlement',
            requirements: ['id' => '\d+'],
            controller: CreatePurchaseReturnInvoiceSettlementController::class,
            openapiContext: [
                "summary" => "Create custom settlement with that resources.",
                "requestBody" => [
                    "description" => "Customization of our endpoint.",
                    "required" => true,
                    "content"=>[
                        "multipart/form-data" => [
                            "schema" => [
                                "properties" => [
                                    "code" => [
                                        "description" => "The code of the institution",
                                        "type" => "string",
                                        "example" => "Clark Kent",
                                    ],
                                    "name" => [
                                        "description" => "The name of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "email" => [
                                        "description" => "The email of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "phone" => [
                                        "description" => "The phone of the institution",
                                        "type" => "integer",
                                        "example" => "superman",
                                    ],
                                    "address" => [
                                        "description" => "The username of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "website" => [
                                        "description" => "The password of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "postalCode" => [
                                        "description" => "The postalCode of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "city" => [
                                        "description" => "The city of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "region" => [
                                        "description" => "The region of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "manager" => [
                                        "description" => "The manager of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "managerType" => [
                                        "description" => "The password of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "picture" => [
                                        "type" => "string",
                                        "format" => "binary",
                                        "description" => "Upload a cover image of the institution",
                                    ],
                                ],
                            ],


                        ]
                    ],

                    "responses"=>[
                        "200" => ["description" => "MyEntity resources created successfully."],
                        "400" => ["description" => "Invalid request body."],
                        "404" => ["description" => "MyEntity resources not found."]
                    ]

                ]],
            denormalizationContext: [
                'groups' => ['write:PurchaseReturnInvoice'],
            ],
            deserialize: false
        ),

        // Validate settlement
        new Post(
            uriTemplate: '/create/validate/purchase-return-invoice/{id}/settlement',
            requirements: ['id' => '\d+'],
            controller: CreatePurchaseReturnInvoiceSettlementValidateController::class,
            openapiContext: [
                "summary" => "Create custom settlement with that resources.",
                "requestBody" => [
                    "description" => "Customization of our endpoint.",
                    "required" => true,
                    "content"=>[
                        "multipart/form-data" => [
                            "schema" => [
                                "properties" => [
                                    "code" => [
                                        "description" => "The code of the institution",
                                        "type" => "string",
                                        "example" => "Clark Kent",
                                    ],
                                    "name" => [
                                        "description" => "The name of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "email" => [
                                        "description" => "The email of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "phone" => [
                                        "description" => "The phone of the institution",
                                        "type" => "integer",
                                        "example" => "superman",
                                    ],
                                    "address" => [
                                        "description" => "The username of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "website" => [
                                        "description" => "The password of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "postalCode" => [
                                        "description" => "The postalCode of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "city" => [
                                        "description" => "The city of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "region" => [
                                        "description" => "The region of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "manager" => [
                                        "description" => "The manager of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "managerType" => [
                                        "description" => "The password of the institution",
                                        "type" => "string",
                                        "example" => "superman",
                                    ],
                                    "picture" => [
                                        "type" => "string",
                                        "format" => "binary",
                                        "description" => "Upload a cover image of the institution",
                                    ],
                                ],
                            ],


                        ]
                    ],

                    "responses"=>[
                        "200" => ["description" => "MyEntity resources created successfully."],
                        "400" => ["description" => "Invalid request body."],
                        "404" => ["description" => "MyEntity resources not found."]
                    ]

                ]],
            denormalizationContext: [
                'groups' => ['write:PurchaseReturnInvoice'],
            ],
            deserialize: false
        ),
        // Settlement  end

        // Delivery
        new Delete(
            uriTemplate: '/create/purchase-return-invoice/{id}/delivery',
            requirements: ['id' => '\d+'],
            controller: CreatePurchaseReturnInvoiceDeliveryController::class,
            normalizationContext: [
                'groups' => ['get:PurchaseReturnInvoice:collection'],
            ],
        ),
        new Delete(
            uriTemplate: '/create/purchase-return-invoice/{id}/validate/delivery',
            requirements: ['id' => '\d+'],
            controller: CreatePurchaseReturnInvoiceDeliveryValidateController::class,
            normalizationContext: [
                'groups' => ['get:PurchaseReturnInvoice:collection'],
            ],
        ),
        new Delete(
            uriTemplate: '/create/purchase-return-invoice/{id}/stock/out',
            requirements: ['id' => '\d+'],
            controller: CreatePurchaseReturnInvoiceStockOutController::class,
            normalizationContext: [
                'groups' => ['get:PurchaseReturnInvoice:collection'],
            ],
        ),
        // Delivery end

    ]
)]
class PurchaseReturnInvoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:PurchaseReturnInvoice:collection','get:PurchaseReturnInvoiceItem:collection','get:PurchaseSettlement:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PurchaseReturnInvoice:collection', 'write:PurchaseReturnInvoice'])]
    private ?PurchaseInvoice $purchaseInvoice = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:PurchaseReturnInvoice:collection', 'write:PurchaseReturnInvoice','get:PurchaseReturnInvoiceItem:collection','get:PurchaseSettlement:collection'])]
    private ?string $invoiceNumber = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PurchaseReturnInvoice:collection', 'write:PurchaseReturnInvoice'])]
    private ?Supplier $supplier = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseReturnInvoice:collection', 'write:PurchaseReturnInvoice'])]
    private ?\DateTimeImmutable $invoiceAt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:PurchaseReturnInvoice:collection', 'write:PurchaseReturnInvoice'])]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:PurchaseReturnInvoice:collection', 'write:PurchaseReturnInvoice'])]
    private ?string $amountPaid = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    #[Groups(['get:PurchaseReturnInvoice:collection', 'write:PurchaseReturnInvoice'])]
    private ?string $balance = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:PurchaseReturnInvoice:collection', 'write:PurchaseReturnInvoice'])]
    private ?string $shippingAddress = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:PurchaseReturnInvoice:collection', 'write:PurchaseReturnInvoice'])]
    private ?string $paymentReference = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseReturnInvoice:collection', 'write:PurchaseReturnInvoice'])]
    private ?\DateTimeImmutable $deadLine = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:PurchaseReturnInvoice:collection', 'write:PurchaseReturnInvoice'])]
    private ?string $status = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:PurchaseReturnInvoice:collection', 'write:PurchaseReturnInvoice', 'get:PurchaseReturnInvoiceItem:collection'])]
    private ?string $otherStatus = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:PurchaseReturnInvoice:collection', 'write:PurchaseReturnInvoice'])]
    private ?string $virtualBalance = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:PurchaseReturnInvoice:collection', 'write:PurchaseReturnInvoice'])]
    private ?string $ttc = null;

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
        $this->balance = 0;
        $this->ttc = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getInvoiceAt(): ?\DateTimeImmutable
    {
        return $this->invoiceAt;
    }

    public function setInvoiceAt(?\DateTimeImmutable $invoiceAt): self
    {
        $this->invoiceAt = $invoiceAt;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmountPaid(): ?string
    {
        return $this->amountPaid;
    }

    public function setAmountPaid(?string $amountPaid): self
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(string $balance): self
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

    public function getDeadLine(): ?\DateTimeImmutable
    {
        return $this->deadLine;
    }

    public function setDeadLine(?\DateTimeImmutable $deadLine): self
    {
        $this->deadLine = $deadLine;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

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

    public function getPaymentReference(): ?string
    {
        return $this->paymentReference;
    }

    public function setPaymentReference(?string $paymentReference): self
    {
        $this->paymentReference = $paymentReference;

        return $this;
    }

    public function getVirtualBalance(): ?string
    {
        return $this->virtualBalance;
    }

    public function setVirtualBalance(?string $virtualBalance): self
    {
        $this->virtualBalance = $virtualBalance;

        return $this;
    }

    public function getTtc(): ?string
    {
        return $this->ttc;
    }

    public function setTtc(?string $ttc): self
    {
        $this->ttc = $ttc;

        return $this;
    }
    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): self
    {
        $this->supplier = $supplier;

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
