<?php

namespace App\Entity\Billing\Sale;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Billing\Sale\Delivery\CreateSaleInvoiceDeliveryController;
use App\Controller\Billing\Sale\Delivery\CreateSaleInvoiceDeliverySettlementStockOutValidateController;
use App\Controller\Billing\Sale\Delivery\CreateSaleInvoiceStockOutController;
use App\Controller\Billing\Sale\Delivery\CreateSaleInvoiceDeliveryValidateController;
use App\Controller\Billing\Sale\Invoice\ClearSaleInvoiceItemController;
use App\Controller\Billing\Sale\Invoice\CreateSaleInvoiceSettlementController;
use App\Controller\Billing\Sale\Invoice\CreateSaleInvoiceSettlementValidateController;
use App\Controller\Billing\Sale\Invoice\GenerateSaleReturnInvoiceController;
use App\Controller\Billing\Sale\Invoice\GetSaleInvoiceController;
use App\Controller\Billing\Sale\Invoice\GetSaleInvoiceItemController;
use App\Controller\Billing\Sale\Invoice\GetSaleInvoiceTotalAmountController;
use App\Controller\Billing\Sale\Invoice\GetSchoolSaleInvoiceController;
use App\Controller\Billing\Sale\Invoice\ValidateSaleInvoiceController;
use App\Controller\Billing\Sale\School\Invoice\ClearSaleInvoiceFeeController;
use App\Controller\Billing\Sale\School\Invoice\CreateSchoolSaleInvoiceSettlementController;
use App\Controller\Billing\Sale\School\Invoice\CreateSchoolSaleInvoiceSettlementValidateController;
use App\Controller\Billing\Sale\School\Invoice\GenerateSchoolSaleReturnInvoiceController;
use App\Controller\Billing\Sale\School\Invoice\GetSaleInvoiceFeeController;
use App\Controller\Billing\Sale\School\Invoice\GetSaleInvoiceFeeTotalAmountController;
use App\Controller\Billing\Sale\School\Invoice\ValidateSchoolSaleInvoiceController;
use App\Entity\Partner\Customer;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Billing\Sale\SaleInvoiceRepository;
use App\State\Processor\Billing\Sale\CancelSaleInvoiceProcessor;
use App\State\Processor\Billing\Sale\CreateSaleInvoiceItemProcessor;
use App\State\Processor\Billing\Sale\DeleteSaleInvoiceProcessor;
use App\State\Processor\Billing\Sale\GenerateSaleInvoiceProcessor;
use App\State\Processor\Billing\Sale\PutSaleInvoiceProcessor;
use App\State\Processor\Billing\Sale\School\CancelSchoolSaleInvoiceProcessor;
use App\State\Processor\Billing\Sale\School\CreateSaleInvoiceFeeProcessor;
use App\State\Processor\Billing\Sale\School\DeleteSchoolSaleInvoiceProcessor;
use App\State\Processor\Billing\Sale\School\GenerateSchoolSaleInvoiceProcessor;
use App\State\Processor\Billing\Sale\School\PutSchoolSaleInvoiceProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SaleInvoiceRepository::class)]
#[ORM\Table(name: 'sale_invoice')]
#[ApiResource(
    operations:[

        new Get(
            uriTemplate: '/get/sale-invoice/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:SaleInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/sale-invoice',
            controller: GetSaleInvoiceController::class,
            normalizationContext: [
                'groups' => ['get:SaleInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Post(
            uriTemplate: '/generate/sale-invoice',
            denormalizationContext: [
                'groups' => ['write:SaleInvoice'],
            ],
            processor: GenerateSaleInvoiceProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/sale-invoice/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SaleInvoice'],
            ],
            processor: PutSaleInvoiceProcessor::class
        ),
        new Delete(
            uriTemplate: '/validate/sale-invoice/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidateSaleInvoiceController::class
        ),
        new Delete(
            uriTemplate: '/cancel/sale-invoice/{id}',
            requirements: ['id' => '\d+'],
            processor: CancelSaleInvoiceProcessor::class
        ),
        new Delete(
            uriTemplate: '/delete/sale-invoice/{id}',
            requirements: ['id' => '\d+'],
            processor: DeleteSaleInvoiceProcessor::class
        ),

        // Sale Invoice Item
        new Get(
            uriTemplate: '/get/sale-invoice/{id}/item',
            requirements: ['id' => '\d+'],
            controller: GetSaleInvoiceItemController::class,
            normalizationContext: [
                'groups' => ['get:SaleInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Put(
            uriTemplate: '/create/sale-invoice/{id}/item',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SaleInvoice'],
            ],
            processor: CreateSaleInvoiceItemProcessor::class,
        ),
        new Delete(
            uriTemplate: '/clear/sale-invoice/{id}/items',
            requirements: ['id' => '\d+'],
            controller: ClearSaleInvoiceItemController::class,
            normalizationContext: [
                'groups' => ['get:SaleInvoice:collection'],
            ],
        ),
        new Get(
            uriTemplate: '/get/sale-invoice/{id}/total-amount',
            requirements: ['id' => '\d+'],
            controller: GetSaleInvoiceTotalAmountController::class,
            normalizationContext: [
                'groups' => ['get:SaleInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),

        // Settlement
        // Complete
        new Post(
            uriTemplate: '/create/sale-invoice/{id}/settlement/delivery/stock/out/validate',
            requirements: ['id' => '\d+'],
            controller: CreateSaleInvoiceDeliverySettlementStockOutValidateController::class,
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
                'groups' => ['write:SaleInvoice'],
            ],
            deserialize: false
        ),

        // Draft Settlement
        new Post(
            uriTemplate: '/create/sale-invoice/{id}/settlement',
            requirements: ['id' => '\d+'],
            controller: CreateSaleInvoiceSettlementController::class,
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
                'groups' => ['write:SaleInvoice'],
            ],
            deserialize: false
        ),

        // Validate settlement
        new Post(
            uriTemplate: '/create/validate/sale-invoice/{id}/settlement',
            requirements: ['id' => '\d+'],
            controller: CreateSaleInvoiceSettlementValidateController::class,
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
                'groups' => ['write:SaleInvoice'],
            ],
            deserialize: false
        ),
        // Settlement  end

        // Delivery
        new Delete(
            uriTemplate: '/create/sale-invoice/{id}/delivery',
            requirements: ['id' => '\d+'],
            controller: CreateSaleInvoiceDeliveryController::class,
            normalizationContext: [
                'groups' => ['get:SaleInvoice:collection'],
            ],
        ),
        new Delete(
            uriTemplate: '/create/sale-invoice/{id}/validate/delivery',
            requirements: ['id' => '\d+'],
            controller: CreateSaleInvoiceDeliveryValidateController::class,
            normalizationContext: [
                'groups' => ['get:SaleInvoice:collection'],
            ],
        ),
        new Delete(
            uriTemplate: '/create/sale-invoice/{id}/stock/out',
            requirements: ['id' => '\d+'],
            controller: CreateSaleInvoiceStockOutController::class,
            normalizationContext: [
                'groups' => ['get:SaleInvoice:collection'],
            ],
        ),
        // Delivery end

        // Return Sale Invoice
        new Delete(
            uriTemplate: '/generate/return/sale-invoice/{id}',
            requirements: ['id' => '\d+'],
            controller: GenerateSaleReturnInvoiceController::class
        ),

        // Start School:
        // Start - Sale Invoice Part
        new GetCollection(
            uriTemplate: '/get/school/sale-invoice',
            controller: GetSchoolSaleInvoiceController::class,
            normalizationContext: [
                'groups' => ['get:SaleInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),

        new Post(
            uriTemplate: '/generate/school/sale-invoice',
            denormalizationContext: [
                'groups' => ['write:SaleInvoice'],
            ],
            processor: GenerateSchoolSaleInvoiceProcessor::class,
        ),

        new Get(
            uriTemplate: '/get/school/sale-invoice/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:SaleInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),

        new Put(
            uriTemplate: '/edit/school-sale-invoice/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SaleInvoice'],
            ],
            processor: PutSchoolSaleInvoiceProcessor::class
        ),

        new Put(
            uriTemplate: '/create/sale-invoice/{id}/fee',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SaleInvoice'],
            ],
            processor: CreateSaleInvoiceFeeProcessor::class,
        ),

        new Get(
            uriTemplate: '/get/sale-invoice/{id}/fee',
            requirements: ['id' => '\d+'],
            controller: GetSaleInvoiceFeeController::class,
            normalizationContext: [
                'groups' => ['get:SaleInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),

        new Get(
            uriTemplate: '/get/sale-invoice-fee/{id}/total-amount',
            requirements: ['id' => '\d+'],
            controller: GetSaleInvoiceFeeTotalAmountController::class,
            normalizationContext: [
                'groups' => ['get:SaleInvoice:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),

        new Delete(
            uriTemplate: '/validate/school-sale-invoice/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidateSchoolSaleInvoiceController::class
        ),

        new Delete(
            uriTemplate: '/cancel/school/sale-invoice/{id}',
            requirements: ['id' => '\d+'],
            processor: CancelSchoolSaleInvoiceProcessor::class
        ),

        new Delete(
            uriTemplate: '/delete/school/sale-invoice/{id}',
            requirements: ['id' => '\d+'],
            processor: DeleteSchoolSaleInvoiceProcessor::class
        ),

        new Delete(
            uriTemplate: '/generate/school/return-sale-invoice/{id}',
            requirements: ['id' => '\d+'],
            controller: GenerateSchoolSaleReturnInvoiceController::class
        ),

        new Post(
            uriTemplate: '/create/school-sale-invoice/{id}/settlement',
            requirements: ['id' => '\d+'],
            controller: CreateSchoolSaleInvoiceSettlementController::class,
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
                'groups' => ['write:SaleInvoice'],
            ],
            deserialize: false
        ),

        new Post(
            uriTemplate: '/create/validate/school-sale-invoice/{id}/settlement',
            requirements: ['id' => '\d+'],
            controller: CreateSchoolSaleInvoiceSettlementValidateController::class,
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
                'groups' => ['write:SaleInvoice'],
            ],
            deserialize: false
        ),

        new Delete(
            uriTemplate: '/clear/school-sale-invoice/{id}/fees',
            requirements: ['id' => '\d+'],
            controller: ClearSaleInvoiceFeeController::class,
            normalizationContext: [
                'groups' => ['get:SaleInvoice:collection'],
            ],
        ),

        // End - Sale Invoice Part
        // End School

    ]
)]
class SaleInvoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:SaleInvoice:collection','get:SaleInvoiceItem:collection','get:SaleSettlement:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice','get:SaleInvoiceItem:collection','get:SaleSettlement:collection'])]
    private ?string $invoiceNumber = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?Customer $customer = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?\DateTimeImmutable $invoiceAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?bool $isStandard = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?string $amountPaid = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?string $balance = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?string $shippingAddress = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?string $paymentReference = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?\DateTimeImmutable $deadLine = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?string $status = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?string $otherStatus = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?string $virtualBalance = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?string $ttc = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?StudentRegistration $studentRegistration = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    private ?SchoolClass $class = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
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
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SaleInvoice:collection', 'write:SaleInvoice'])]
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

    public function isIsStandard(): ?bool
    {
        return $this->isStandard;
    }

    public function setIsStandard(?bool $isStandard): self
    {
        $this->isStandard = $isStandard;

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
