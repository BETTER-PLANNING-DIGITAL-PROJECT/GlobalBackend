<?php

namespace App\Entity\Billing\Purchase;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Billing\Purchase\Invoice\ClearPurchaseInvoiceItemController;
use App\Controller\Billing\Purchase\Invoice\CreatePurchaseInvoiceReceptionSettlementStockInValidateController;
use App\Controller\Billing\Purchase\Invoice\CreatePurchaseInvoiceSettlementValidateController;
use App\Controller\Billing\Purchase\Invoice\CreatePurchaseSettlementController;
use App\Controller\Billing\Purchase\Invoice\GeneratePurchaseReturnInvoiceController;
use App\Controller\Billing\Purchase\Invoice\GetPurchaseInvoiceController;
use App\Controller\Billing\Purchase\Invoice\GetPurchaseInvoiceItemController;
use App\Controller\Billing\Purchase\Invoice\GetPurchaseInvoiceTotalAmountController;
use App\Controller\Billing\Purchase\Invoice\ValidatePurchaseInvoiceController;
use App\Controller\Billing\Purchase\Reception\CreatePurchaseInvoiceReceptionController;
use App\Controller\Billing\Purchase\Reception\CreatePurchaseInvoiceReceptionValidateController;
use App\Controller\Billing\Purchase\Reception\CreatePurchaseInvoiceStockInController;
use App\Entity\Partner\Supplier;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Billing\Purchase\PurchaseInvoiceRepository;
use App\State\Processor\Billing\Purchase\CancelPurchaseInvoiceProcessor;
use App\State\Processor\Billing\Purchase\CreatePurchaseInvoiceItemProcessor;
use App\State\Processor\Billing\Purchase\DeletePurchaseInvoiceProcessor;
use App\State\Processor\Billing\Purchase\GeneratePurchaseInvoiceProcessor;
use App\State\Processor\Billing\Purchase\PutPurchaseInvoiceProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PurchaseInvoiceRepository::class)]
#[ORM\Table(name: 'purchase_invoice')]
#[ApiResource(
    operations:[

        new Get(
            uriTemplate: '/get/purchase-invoice/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:PurchaseInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/purchase-invoice',
            controller: GetPurchaseInvoiceController::class,
            normalizationContext: [
                'groups' => ['get:PurchaseInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Post(
            uriTemplate: '/generate/purchase-invoice',
            denormalizationContext: [
                'groups' => ['write:PurchaseInvoice'],
            ],
            processor: GeneratePurchaseInvoiceProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/purchase-invoice/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:PurchaseInvoice'],
            ],
            processor: PutPurchaseInvoiceProcessor::class
        ),
        new Delete(
            uriTemplate: '/validate/purchase-invoice/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidatePurchaseInvoiceController::class
        ),
        new Delete(
            uriTemplate: '/cancel/purchase-invoice/{id}',
            requirements: ['id' => '\d+'],
            processor: CancelPurchaseInvoiceProcessor::class
        ),
        new Delete(
            uriTemplate: '/delete/purchase-invoice/{id}',
            requirements: ['id' => '\d+'],
            processor: DeletePurchaseInvoiceProcessor::class
        ),

        // Purchase Invoice Item
        new Get(
            uriTemplate: '/get/purchase-invoice/{id}/item',
            requirements: ['id' => '\d+'],
            controller: GetPurchaseInvoiceItemController::class,
            normalizationContext: [
                'groups' => ['get:PurchaseInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Put(
            uriTemplate: '/create/purchase-invoice/{id}/item',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:PurchaseInvoice'],
            ],
            processor: CreatePurchaseInvoiceItemProcessor::class,
        ),
        new Delete(
            uriTemplate: '/clear/purchase-invoice/{id}/items',
            requirements: ['id' => '\d+'],
            controller: ClearPurchaseInvoiceItemController::class,
            normalizationContext: [
                'groups' => ['get:PurchaseInvoice:collection'],
            ],
        ),
        new Get(
            uriTemplate: '/get/purchase-invoice/{id}/total-amount',
            requirements: ['id' => '\d+'],
            controller: GetPurchaseInvoiceTotalAmountController::class,
            normalizationContext: [
                'groups' => ['get:PurchaseInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),

        // complete
        new Post(
            uriTemplate: '/create/purchase-invoice/{id}/settlement/reception/stock/in/validate',
            requirements: ['id' => '\d+'],
            controller: CreatePurchaseInvoiceReceptionSettlementStockInValidateController::class,
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
                'groups' => ['write:PurchaseInvoice'],
            ],
            deserialize: false
        ),

        // Draft Settlement
        new Post(
            uriTemplate: '/create/purchase-invoice/{id}/settlement',
            requirements: ['id' => '\d+'],
            controller: CreatePurchaseSettlementController::class,
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
                'groups' => ['write:PurchaseInvoice'],
            ],
            deserialize: false
        ),

       // Validate settlement
        new Post(
            uriTemplate: '/create/validate/purchase-invoice/{id}/settlement',
            requirements: ['id' => '\d+'],
            controller: CreatePurchaseInvoiceSettlementValidateController::class,
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
                'groups' => ['write:PurchaseInvoice'],
            ],
            deserialize: false
        ),
        // Settlement  end

        // Reception
        new Delete(
            uriTemplate: '/create/purchase-invoice/{id}/reception',
            requirements: ['id' => '\d+'],
            controller: CreatePurchaseInvoiceReceptionController::class,
            normalizationContext: [
                'groups' => ['get:PurchaseInvoice:collection'],
            ],
        ),
        new Delete(
            uriTemplate: '/create/purchase-invoice/{id}/validate/reception',
            requirements: ['id' => '\d+'],
            controller: CreatePurchaseInvoiceReceptionValidateController::class,
            normalizationContext: [
                'groups' => ['get:PurchaseInvoice:collection'],
            ],
        ),
        // Reception end

        // stock in
        new Post(
            uriTemplate: '/create/purchase-invoice/{id}/stock/in',
            requirements: ['id' => '\d+'],
            controller: CreatePurchaseInvoiceStockInController::class,
            denormalizationContext: [
                'groups' => ['write:PurchaseInvoice'],
            ],
            deserialize: false
        ),

        // Return Purchase Invoice
        new Delete(
            uriTemplate: '/generate/return/purchase-invoice/{id}',
            requirements: ['id' => '\d+'],
            controller: GeneratePurchaseReturnInvoiceController::class
        ),

    ]
)]
class PurchaseInvoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:PurchaseInvoice:collection','get:PurchaseInvoiceItem:collection','get:PurchaseSettlement:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:PurchaseInvoice:collection', 'write:PurchaseInvoice','get:PurchaseInvoiceItem:collection','get:PurchaseSettlement:collection'])]
    private ?string $invoiceNumber = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PurchaseInvoice:collection', 'write:PurchaseInvoice'])]
    private ?Supplier $supplier = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseInvoice:collection', 'write:PurchaseInvoice'])]
    private ?\DateTimeImmutable $invoiceAt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:PurchaseInvoice:collection', 'write:PurchaseInvoice'])]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:PurchaseInvoice:collection', 'write:PurchaseInvoice'])]
    private ?string $amountPaid = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    #[Groups(['get:PurchaseInvoice:collection', 'write:PurchaseInvoice'])]
    private ?string $balance = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:PurchaseInvoice:collection', 'write:PurchaseInvoice'])]
    private ?string $shippingAddress = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:PurchaseInvoice:collection', 'write:PurchaseInvoice'])]
    private ?string $paymentReference = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseInvoice:collection', 'write:PurchaseInvoice'])]
    private ?\DateTimeImmutable $deadLine = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:PurchaseInvoice:collection', 'write:PurchaseInvoice'])]
    private ?string $status = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:PurchaseInvoice:collection', 'write:PurchaseInvoice'])]
    private ?string $otherStatus = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:PurchaseInvoice:collection', 'write:PurchaseInvoice'])]
    private ?string $virtualBalance = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:PurchaseInvoice:collection', 'write:PurchaseInvoice'])]
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
        $this->amountPaid = 0;
        $this->ttc = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
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
