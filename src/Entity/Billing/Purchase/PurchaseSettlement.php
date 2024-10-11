<?php

namespace App\Entity\Billing\Purchase;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Billing\Purchase\DeletePurchaseSettlementController;
use App\Controller\Billing\Purchase\EditPurchaseInvoiceSettlementController;
use App\Controller\Billing\Purchase\GetPurchaseSettlementController;
use App\Controller\Billing\Purchase\PostPurchaseSettlementController;
use App\Controller\Billing\Purchase\PutPurchaseSettlementController;
use App\Controller\Billing\Purchase\ValidatePurchaseReturnSettlementController;
use App\Controller\Billing\Purchase\ValidatePurchaseSettlementController;
use App\Entity\Partner\Supplier;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Finance\PaymentGateway;
use App\Entity\Setting\Finance\PaymentMethod;
use App\Entity\Treasury\Bank;
use App\Entity\Treasury\BankAccount;
use App\Entity\Treasury\CashDesk;
use App\Repository\Billing\Purchase\PurchaseSettlementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PurchaseSettlementRepository::class)]
#[ORM\Table(name: 'purchase_settlement')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/purchase-settlement/{id}/',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:PurchaseSettlement:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),

        new GetCollection(
            uriTemplate: '/get/purchase-settlement',
            controller: GetPurchaseSettlementController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:PurchaseSettlement:collection'],
            ]
        ),

        new Post(
            uriTemplate: '/create/purchase-settlement',
            controller: PostPurchaseSettlementController::class,
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

                ]
            ],
            denormalizationContext: [
                'groups' => ['write:PurchaseSettlement'],
            ],
            deserialize: false
        ),

        new Post(
            uriTemplate: '/edit/purchase-settlement/{id}',
            requirements: ['id' => '\d+'],
            controller: PutPurchaseSettlementController::class,
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

                ]
            ],
            denormalizationContext: [
                'groups' => ['write:PurchaseSettlement'],
            ],
            deserialize: false
        ),

        // Invoice settlement

       /* new Put(
            uriTemplate: '/edit/purchase-invoice/settlement/{id}',
            requirements: ['id' => '\d+'],
            controller: EditPurchaseInvoiceSettlementController::class,
            denormalizationContext: [
                'groups' => ['write:PurchaseSettlement'],
            ]
        ),*/



        new Put(
            uriTemplate: '/validate/purchase/settlement/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidatePurchaseSettlementController::class,
            denormalizationContext: [
                'groups' => ['write:PurchaseSettlement'],
            ]
        ),

        new Put(
            uriTemplate: '/validate/purchase/return/settlement/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidatePurchaseReturnSettlementController::class,
            denormalizationContext: [
                'groups' => ['write:PurchaseSettlement'],
            ]
        ),

        new Delete(
            uriTemplate: '/delete/purchase-settlement/{id}',
            requirements: ['id' => '\d+'],
            controller: DeletePurchaseSettlementController::class
        ),
    ]
)]
class PurchaseSettlement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:PurchaseSettlement:collection', 'get:PurchaseInvoice:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement', 'get:PurchaseInvoice:collection'])]
    private ?PurchaseInvoice $invoice = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement', 'get:PurchaseInvoice:collection'])]
    private ?PurchaseReturnInvoice $purchaseReturnInvoice = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?Supplier $supplier = null;

    #[ORM\Column(length: 110)]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?string $reference = null;

    #[ORM\Column]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?float $amountPay = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?float $amountRest = null;

    #[ORM\Column]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?\DateTimeImmutable $settleAt = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement', 'get:Bank:collection'])]
    private ?Bank $bank = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?BankAccount $bankAccount = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?CashDesk $cashDesk = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?string $note = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?PaymentMethod $paymentMethod = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement', 'get:PaymentGateway:collection'])]
    private ?PaymentGateway $paymentGateway = null;

    #[ORM\Column(length: 110, nullable: true)]
    private ?string $gatewayReference = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?string $phone = null;

    #[ORM\Column(length: 110, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?bool $isValidate = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?User $validateBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?\DateTimeImmutable $validateAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?string $picture = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?string $fileName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?string $fileType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PurchaseSettlement:collection', 'write:PurchaseSettlement'])]
    private ?int $fileSize = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isTreat;

    // Return Invoice
    // Supplier

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Branch $branch = null;

    public function __construct(){

        $this->is_enable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoice(): ?PurchaseInvoice
    {
        return $this->invoice;
    }

    public function setInvoice(?PurchaseInvoice $invoice): self
    {
        $this->invoice = $invoice;

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
    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): static
    {
        $this->institution = $institution;

        return $this;
    }

    public function getAmountPay(): ?float
    {
        return $this->amountPay;
    }

    public function setAmountPay(float $amountPay): self
    {
        $this->amountPay = $amountPay;

        return $this;
    }

    public function getAmountRest(): ?float
    {
        return $this->amountRest;
    }

    public function setAmountRest(?float $amountRest): self
    {
        $this->amountRest = $amountRest;

        return $this;
    }

    public function getSettleAt(): ?\DateTimeImmutable
    {
        return $this->settleAt;
    }

    public function setSettleAt(\DateTimeImmutable $settleAt): self
    {
        $this->settleAt = $settleAt;

        return $this;
    }

    public function getBank(): ?Bank
    {
        return $this->bank;
    }

    public function setBank(?Bank $bank): self
    {
        $this->bank = $bank;

        return $this;
    }

    public function getCashDesk(): ?CashDesk
    {
        return $this->cashDesk;
    }

    public function setCashDesk(?CashDesk $cashDesk): self
    {
        $this->cashDesk = $cashDesk;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?PaymentMethod $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getPaymentGateway(): ?PaymentGateway
    {
        return $this->paymentGateway;
    }

    public function setPaymentGateway(?PaymentGateway $paymentGateway): self
    {
        $this->paymentGateway = $paymentGateway;

        return $this;
    }

    public function getBankAccount(): ?BankAccount
    {
        return $this->bankAccount;
    }

    public function setBankAccount(?BankAccount $bankAccount): self
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    public function isIsValidate(): ?bool
    {
        return $this->isValidate;
    }

    public function setIsValidate(bool $isValidate): self
    {
        $this->isValidate = $isValidate;

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

    public function getValidateAt(): ?\DateTimeImmutable
    {
        return $this->validateAt;
    }

    public function setValidateAt(?\DateTimeImmutable $validateAt): self
    {
        $this->validateAt = $validateAt;

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

    public function getPurchaseReturnInvoice(): ?PurchaseReturnInvoice
    {
        return $this->purchaseReturnInvoice;
    }

    public function setPurchaseReturnInvoice(?PurchaseReturnInvoice $purchaseReturnInvoice): self
    {
        $this->purchaseReturnInvoice = $purchaseReturnInvoice;

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
    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(?string $fileType): self
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(?int $fileSize): self
    {
        $this->fileSize = $fileSize;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getGatewayReference(): ?string
    {
        return $this->gatewayReference;
    }

    public function setGatewayReference(?string $gatewayReference): self
    {
        $this->gatewayReference = $gatewayReference;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

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
