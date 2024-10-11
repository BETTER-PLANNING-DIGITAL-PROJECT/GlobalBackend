<?php

namespace App\Entity\Billing\Sale;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Billing\Sale\Reception\CreateSaleReturnInvoiceReceptionController;
use App\Controller\Billing\Sale\Reception\CreateSaleReturnInvoiceReceptionSettlementStockInValidateController;
use App\Controller\Billing\Sale\Reception\CreateSaleReturnInvoiceReceptionValidateController;
use App\Controller\Billing\Sale\Reception\CreateSaleReturnInvoiceStockInController;
use App\Controller\Billing\Sale\Return\ClearSaleReturnInvoiceItemController;
use App\Controller\Billing\Sale\Return\CreateSaleReturnInvoiceSettlementController;
use App\Controller\Billing\Sale\Return\CreateSaleReturnInvoiceSettlementValidateController;
use App\Controller\Billing\Sale\Return\GetSaleReturnInvoiceController;
use App\Controller\Billing\Sale\Return\GetSaleReturnInvoiceItemController;
use App\Controller\Billing\Sale\Return\GetSaleReturnInvoiceSumFeeAmountController;
use App\Controller\Billing\Sale\Return\GetSaleReturnInvoiceTotalAmountController;
use App\Controller\Billing\Sale\Return\ValidateSaleReturnInvoiceController;
use App\Controller\Billing\Sale\School\Return\ClearSaleReturnInvoiceFeeController;
use App\Controller\Billing\Sale\School\Return\CreateSchoolSaleReturnInvoiceSettlementController;
use App\Controller\Billing\Sale\School\Return\CreateSchoolSaleReturnInvoiceValidateController;
use App\Controller\Billing\Sale\School\Return\GetSaleReturnInvoiceFeeController;
use App\Controller\Billing\Sale\School\Return\GetSchoolSaleReturnInvoiceController;
use App\Controller\Billing\Sale\School\Return\GetSchoolSaleReturnInvoiceTotalAmountController;
use App\Controller\Billing\Sale\School\Return\ValidateSchoolSaleReturnInvoiceController;
use App\Entity\Partner\Customer;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleReturnInvoiceRepository;
use App\State\Processor\Billing\Sale\CancelSaleReturnInvoiceProcessor;
use App\State\Processor\Billing\Sale\CreateSaleReturnInvoiceItemProcessor;
use App\State\Processor\Billing\Sale\DeleteSaleReturnInvoiceProcessor;
use App\State\Processor\Billing\Sale\GenerateSaleReturnInvoiceProcessor;
use App\State\Processor\Billing\Sale\PutSaleReturnInvoiceProcessor;
use App\State\Processor\Billing\Sale\School\Return\CancelSchoolSaleReturnInvoiceProcessor;
use App\State\Processor\Billing\Sale\School\Return\CreateSaleReturnInvoiceFeeProcessor;
use App\State\Processor\Billing\Sale\School\Return\DeleteSchoolSaleReturnInvoiceProcessor;
use App\State\Processor\Billing\Sale\School\Return\GenerateSchoolSaleReturnInvoiceProcessor;
use App\State\Processor\Billing\Sale\School\Return\PutSchoolSaleReturnInvoiceProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SaleReturnInvoiceRepository::class)]
#[ORM\Table(name: 'sale_return_invoice')]

#[ApiResource(
    operations:[

        new Get(
            uriTemplate: '/get/sale-return-invoice/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:SaleReturnInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/sale-return-invoice',
            controller: GetSaleReturnInvoiceController::class,
            normalizationContext: [
                'groups' => ['get:SaleReturnInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Post(
            uriTemplate: '/generate/sale-return-invoice',
            denormalizationContext: [
                'groups' => ['write:SaleReturnInvoice'],
            ],
            processor: GenerateSaleReturnInvoiceProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/sale-return-invoice/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SaleReturnInvoice'],
            ],
            processor: PutSaleReturnInvoiceProcessor::class
        ),
        new Delete(
            uriTemplate: '/validate/sale-return-invoice/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidateSaleReturnInvoiceController::class
        ),
        new Delete(
            uriTemplate: '/cancel/sale-return-invoice/{id}',
            requirements: ['id' => '\d+'],
            processor: CancelSaleReturnInvoiceProcessor::class
        ),
        new Delete(
            uriTemplate: '/delete/sale-return-invoice/{id}',
            requirements: ['id' => '\d+'],
            processor: DeleteSaleReturnInvoiceProcessor::class
        ),
        /*new Delete(
            uriTemplate: '/hold/sale-invoice/{id}',
            requirements: ['id' => '\d+'],
            controller: HoldSaleInvoiceController::class,
            normalizationContext: [
                'groups' => ['get:SaleReturnInvoice:collection'],
            ],
        ),*/

        // Sale Return Invoice Item
        new Get(
            uriTemplate: '/get/sale-return-invoice/{id}/item',
            requirements: ['id' => '\d+'],
            controller: GetSaleReturnInvoiceItemController::class,
            normalizationContext: [
                'groups' => ['get:SaleReturnInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Put(
            uriTemplate: '/create/sale-return-invoice/{id}/item',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SaleReturnInvoice'],
            ],
            processor: CreateSaleReturnInvoiceItemProcessor::class,
        ),
        new Delete(
            uriTemplate: '/clear/sale-return-invoice/{id}/items',
            requirements: ['id' => '\d+'],
            controller: ClearSaleReturnInvoiceItemController::class,
            normalizationContext: [
                'groups' => ['get:SaleReturnInvoice:collection'],
            ],
        ),
        new Get(
            uriTemplate: '/get/sale-return-invoice/{id}/total-amount',
            requirements: ['id' => '\d+'],
            controller: GetSaleReturnInvoiceTotalAmountController::class,
            normalizationContext: [
                'groups' => ['get:SaleReturnInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),

        // Settlement
        // Complete
        new Post(
            uriTemplate: '/create/sale-return-invoice/{id}/settlement/reception/stock/in/validate',
            requirements: ['id' => '\d+'],
            controller: CreateSaleReturnInvoiceReceptionSettlementStockInValidateController::class,
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
                'groups' => ['write:SaleReturnInvoice'],
            ],
            deserialize: false
        ),

        // Draft Settlement
        new Post(
            uriTemplate: '/create/sale-return-invoice/{id}/settlement',
            requirements: ['id' => '\d+'],
            controller: CreateSaleReturnInvoiceSettlementController::class,
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
                'groups' => ['write:SaleReturnInvoice'],
            ],
            deserialize: false
        ),

        // Validate settlement
        new Post(
            uriTemplate: '/create/validate/sale-return-invoice/{id}/settlement',
            requirements: ['id' => '\d+'],
            controller: CreateSaleReturnInvoiceSettlementValidateController::class,
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
                'groups' => ['write:SaleReturnInvoice'],
            ],
            deserialize: false
        ),
        // Settlement  end

        // Reception
        new Delete(
            uriTemplate: '/create/sale-return-invoice/{id}/reception',
            requirements: ['id' => '\d+'],
            controller: CreateSaleReturnInvoiceReceptionController::class,
            normalizationContext: [
                'groups' => ['get:SaleReturnInvoice:collection'],
            ],
        ),
        new Delete(
            uriTemplate: '/create/sale-return-invoice/{id}/validate/reception',
            requirements: ['id' => '\d+'],
            controller: CreateSaleReturnInvoiceReceptionValidateController::class,
            normalizationContext: [
                'groups' => ['get:SaleReturnInvoice:collection'],
            ],
        ),
        new Delete(
            uriTemplate: '/create/sale-return-invoice/{id}/stock/in',
            requirements: ['id' => '\d+'],
            controller: CreateSaleReturnInvoiceStockInController::class,
            normalizationContext: [
                'groups' => ['get:SaleReturnInvoice:collection'],
            ],
        ),
        // Delivery end

        // Start : School Sale Return
        new GetCollection(
            uriTemplate: '/get/school/sale-return-invoice',
            controller: GetSchoolSaleReturnInvoiceController::class,
            normalizationContext: [
                'groups' => ['get:SaleReturnInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Post(
            uriTemplate: '/generate/school/sale-return-invoice',
            denormalizationContext: [
                'groups' => ['write:SaleReturnInvoice'],
            ],
            processor: GenerateSchoolSaleReturnInvoiceProcessor::class,
        ),
        new Get(
            uriTemplate: '/get/school/sale-return-invoice/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:SaleReturnInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Put(
            uriTemplate: '/edit/school/sale-return-invoice/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SaleReturnInvoice'],
            ],
            processor: PutSchoolSaleReturnInvoiceProcessor::class
        ),
        new Put(
            uriTemplate: '/create/sale-return-invoice/{id}/fee',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SaleReturnInvoice'],
            ],
            processor: CreateSaleReturnInvoiceFeeProcessor::class,
        ),
        new Get(
            uriTemplate: '/get/school-sale-return-invoice/{id}/fee',
            requirements: ['id' => '\d+'],
            controller: GetSaleReturnInvoiceFeeController::class,
            normalizationContext: [
                'groups' => ['get:SaleReturnInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Get(
            uriTemplate: '/get/school/sale-return-invoice/{id}/total-amount',
            requirements: ['id' => '\d+'],
            controller: GetSchoolSaleReturnInvoiceTotalAmountController::class,
            normalizationContext: [
                'groups' => ['get:SaleReturnInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Delete(
            uriTemplate: '/validate/school/sale-return-invoice/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidateSchoolSaleReturnInvoiceController::class
        ),
        new Delete(
            uriTemplate: '/cancel/school/sale-return-invoice/{id}',
            requirements: ['id' => '\d+'],
            processor: CancelSchoolSaleReturnInvoiceProcessor::class
        ),
        new Delete(
            uriTemplate: '/delete/school/sale-return-invoice/{id}',
            requirements: ['id' => '\d+'],
            processor: DeleteSchoolSaleReturnInvoiceProcessor::class
        ),
        new Delete(
            uriTemplate: '/clear/school-sale-return-invoice/{id}/fees',
            requirements: ['id' => '\d+'],
            controller: ClearSaleReturnInvoiceFeeController::class,
            normalizationContext: [
                'groups' => ['get:SaleReturnInvoice:collection'],
            ],
        ),

        // - Settlement
        new Post(
            uriTemplate: '/create/school/sale-return-invoice/{id}/settlement',
            requirements: ['id' => '\d+'],
            controller: CreateSchoolSaleReturnInvoiceSettlementController::class,
            openapiContext: [
                "summary" => "Create custom settlement with that resources.",
                "requestBody" => [
                    "description" => "Customization of our endpoint.",
                    "required" => true,
                    "content"=>[
                        "multipart/form-data" => [
                        ]
                    ],

                    "responses"=>[
                        "200" => ["description" => "MyEntity resources created successfully."],
                        "400" => ["description" => "Invalid request body."],
                        "404" => ["description" => "MyEntity resources not found."]
                    ]
                ]],
            denormalizationContext: [
                'groups' => ['write:SaleReturnInvoice'],
            ],
            deserialize: false,

        ),

        new Post(
            uriTemplate: '/create/school/validate/sale-return-invoice/{id}/settlement',
            requirements: ['id' => '\d+'],
            controller: CreateSchoolSaleReturnInvoiceValidateController::class,
            openapiContext: [
                "summary" => "Create custom settlement with that resources.",
                "requestBody" => [
                    "description" => "Customization of our endpoint.",
                    "required" => true,
                    "content"=>[
                        "multipart/form-data" => [

                        ]
                    ],

                    "responses"=>[
                        "200" => ["description" => "MyEntity resources created successfully."],
                        "400" => ["description" => "Invalid request body."],
                        "404" => ["description" => "MyEntity resources not found."]
                    ]

                ]],
            denormalizationContext: [
                'groups' => ['write:SaleReturnInvoice'],
            ],
            deserialize: false,
        ),

        new Get(
            uriTemplate: '/get/sale-return-invoice/{id}/sum-fee-amount',
            requirements: ['id' => '\d+'],
            controller: GetSaleReturnInvoiceSumFeeAmountController::class,
            normalizationContext: [
                'groups' => ['get:SaleReturnInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),

        // End : School Sale Return

    ]
)]
class SaleReturnInvoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:SaleReturnInvoice:collection', 'get:SaleReturnInvoiceItem:collection','get:SaleReturnInvoiceItem:collection','get:SaleSettlement:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleReturnInvoice:collection', 'get:SaleReturnInvoiceItem:collection', 'write:SaleReturnInvoice'])]
    private ?SaleInvoice $saleInvoice = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:SaleReturnInvoice:collection', 'get:SaleReturnInvoiceItem:collection', 'write:SaleReturnInvoice','get:SaleReturnInvoiceItem:collection','get:SaleSettlement:collection'])]
    private ?string $invoiceNumber = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleReturnInvoice:collection', 'get:SaleReturnInvoiceItem:collection', 'write:SaleReturnInvoice'])]
    private ?Customer $customer = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice'])]
    private ?\DateTimeImmutable $invoiceAt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice', 'get:SaleReturnInvoiceItem:collection'])]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice', 'get:SaleReturnInvoiceItem:collection'])]
    private ?string $amountPaid = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice', 'get:SaleReturnInvoiceItem:collection'])]
    private ?string $balance = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice'])]
    private ?string $shippingAddress = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice'])]
    private ?string $paymentReference = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice'])]
    private ?bool $isStandard = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice'])]
    private ?\DateTimeImmutable $deadLine = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice', 'get:SaleReturnInvoiceItem:collection'])]
    private ?string $status = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice', 'get:SaleReturnInvoiceItem:collection'])]
    private ?string $otherStatus = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice', 'get:SaleReturnInvoiceItem:collection'])]
    private ?string $virtualBalance = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice', 'get:SaleReturnInvoiceItem:collection'])]
    private ?string $ttc = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice'])]
    private ?StudentRegistration $studentRegistration = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice'])]
    private ?SchoolClass $class = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice'])]
    private ?School $school = null;

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
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleReturnInvoice:collection', 'write:SaleReturnInvoice'])]
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

    public function getSaleInvoice(): ?SaleInvoice
    {
        return $this->saleInvoice;
    }

    public function setSaleInvoice(?SaleInvoice $saleInvoice): self
    {
        $this->saleInvoice = $saleInvoice;

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

    public function getOtherStatus(): ?string
    {
        return $this->otherStatus;
    }

    public function setOtherStatus(?string $otherStatus): self
    {
        $this->otherStatus = $otherStatus;

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

    public function isIsStandard(): ?bool
    {
        return $this->isStandard;
    }

    public function setIsStandard(?bool $isStandard): self
    {
        $this->isStandard = $isStandard;

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
