<?php

namespace App\Entity\Billing\Sale;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Billing\Sale\School\Settlement\CreateSchoolSaleSettlementController;
use App\Controller\Billing\Sale\School\Settlement\DeleteSchoolSaleSettlementController;
use App\Controller\Billing\Sale\School\Settlement\EditSchoolSaleSettlementController;
use App\Controller\Billing\Sale\School\Settlement\GetSchoolSaleSettlementController;
use App\Controller\Billing\Sale\School\Settlement\ValidateSchoolSaleReturnSettlementController;
use App\Controller\Billing\Sale\School\Settlement\ValidateSchoolSaleSettlementController;
use App\Controller\Billing\Sale\Settlement\CreateSaleSettlementController;
use App\Controller\Billing\Sale\Settlement\DeleteSaleSettlementController;
use App\Controller\Billing\Sale\Settlement\EditSaleSettlementController;
use App\Controller\Billing\Sale\Settlement\GetSaleSettlementController;
use App\Controller\Billing\Sale\Settlement\ValidateSaleReturnSettlementController;
use App\Controller\Billing\Sale\Settlement\ValidateSaleSettlementController;
use App\Controller\Report\Billing\SchoolSale\SchoolInvoicePaymentReceiptController;
use App\Controller\Report\Billing\SchoolSale\SummaryPaymentPerClassReportController;
use App\Entity\Partner\Customer;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Finance\PaymentGateway;
use App\Entity\Setting\Finance\PaymentMethod;
use App\Entity\Treasury\Bank;
use App\Entity\Treasury\BankAccount;
use App\Entity\Treasury\CashDesk;
use App\Repository\Billing\Sale\SaleSettlementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SaleSettlementRepository::class)]
#[ORM\Table(name: 'sale_settlement')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/settlement/{id}/',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:SaleSettlement:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/sale-settlement',
            controller: GetSaleSettlementController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:SaleSettlement:collection'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/get/summary-payment-per-class-report',
            controller: SummaryPaymentPerClassReportController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:SaleSettlement:collection'],
            ]
        ),

        new Post(
            uriTemplate: '/create/sale-settlement',
            controller: CreateSaleSettlementController::class,
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
                'groups' => ['write:SaleSettlement'],
            ],
            deserialize: false
        ),

        new Post(
            uriTemplate: '/edit/sale-settlement/{id}',
            requirements: ['id' => '\d+'],
            controller: EditSaleSettlementController::class,
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
                'groups' => ['write:SaleSettlement'],
            ],
            deserialize: false
        ),

        new Put(
            uriTemplate: '/validate/sale-settlement/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidateSaleSettlementController::class,
            denormalizationContext: [
                'groups' => ['write:SaleSettlement'],
            ]
        ),

        new Put(
            uriTemplate: '/validate/sale/return/settlement/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidateSaleReturnSettlementController::class,
            denormalizationContext: [
                'groups' => ['write:SaleSettlement'],
            ]
        ),

        new Delete(
            uriTemplate: '/delete/sale-settlement/{id}',
            requirements: ['id' => '\d+'],
            controller: DeleteSaleSettlementController::class
        ),


        // Start : School
        // Sale Settlement
        new GetCollection(
            uriTemplate: '/get/school-sale-settlement',
            controller: GetSchoolSaleSettlementController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:SaleSettlement:collection'],
            ]
        ),
        new Delete(
            uriTemplate: '/delete/school/sale-settlement/{id}',
            requirements: ['id' => '\d+'],
            controller: DeleteSchoolSaleSettlementController::class
        ),

        new Post(
            uriTemplate: '/create/school/sale-settlement',
            controller: CreateSchoolSaleSettlementController::class,
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
                'groups' => ['write:SaleSettlement'],
            ],
            deserialize: false
        ),

        new Post(
            uriTemplate: '/edit/school/sale-settlement/{id}',
            requirements: ['id' => '\d+'],
            controller: EditSchoolSaleSettlementController::class,
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
                'groups' => ['write:SaleSettlement'],
            ],
            deserialize: false
        ),
        new Put(
            uriTemplate: '/validate/school-sale-settlement/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidateSchoolSaleSettlementController::class,
            denormalizationContext: [
                'groups' => ['write:SaleSettlement'],
            ]
        ),

        new Delete( // receipt
            uriTemplate: '/sale-settlement/receipt/situation/{id}',
            requirements: ['id' => '\d+'],
            controller: SchoolInvoicePaymentReceiptController::class,
        ),
        // Sale Settlement


        // Sale Return Settlement
        new Put(
            uriTemplate: '/validate/school/sale/return/settlement/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidateSchoolSaleReturnSettlementController::class,
            denormalizationContext: [
                'groups' => ['write:SaleSettlement'],
            ]
        ),
        // Sale Return Settlement


        // End : School
    ]
)]
class SaleSettlement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:SaleSettlement:collection', 'get:SaleInvoice:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement', 'get:SaleInvoice:collection'])]
    private ?SaleInvoice $invoice = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement', 'get:SaleInvoice:collection'])]
    private ?SaleReturnInvoice $saleReturnInvoice = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?Customer $customer = null;

    #[ORM\Column(length: 110)]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?string $reference = null;

    #[ORM\Column]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?float $amountPay = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?float $amountRest = null;

    #[ORM\Column]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?\DateTimeImmutable $settleAt = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement', 'get:Bank:collection'])]
    private ?Bank $bank = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?BankAccount $bankAccount = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?CashDesk $cashDesk = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?string $note = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?PaymentMethod $paymentMethod = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement', 'get:PaymentGateway:collection'])]
    private ?PaymentGateway $paymentGateway = null;

    #[ORM\Column(length: 110, nullable: true)]
    private ?string $gatewayReference = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?string $phone = null;

    #[ORM\Column(length: 110, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?bool $isValidate = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?User $validateBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?\DateTimeImmutable $validateAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?string $picture = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?string $fileName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?string $fileType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?int $fileSize = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isTreat;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleSettlement:collection', 'write:SaleSettlement'])]
    private ?StudentRegistration $studentRegistration = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?SchoolClass $class = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?School $school = null;

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

    public function getInvoice(): ?SaleInvoice
    {
        return $this->invoice;
    }

    public function setInvoice(?SaleInvoice $invoice): self
    {
        $this->invoice = $invoice;

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

    public function getStudentRegistration(): ?StudentRegistration
    {
        return $this->studentRegistration;
    }

    public function setStudentRegistration(?StudentRegistration $studentRegistration): self
    {
        $this->studentRegistration = $studentRegistration;

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

    public function getSaleReturnInvoice(): ?SaleReturnInvoice
    {
        return $this->saleReturnInvoice;
    }

    public function setSaleReturnInvoice(?SaleReturnInvoice $saleReturnInvoice): self
    {
        $this->saleReturnInvoice = $saleReturnInvoice;

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
