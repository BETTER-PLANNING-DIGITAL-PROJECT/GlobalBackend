<?php

namespace App\Entity\Security\Institution;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\Security\CreateInstitutionController;
use App\Controller\Security\EditInstitutionController;
use App\Entity\Setting\Institution\ManagerType;
use App\Entity\Setting\Location\Country;
use App\Repository\Security\Institution\InstitutionRepository;
use App\State\Processor\Security\DeleteInstitutionProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InstitutionRepository::class)]
#[ORM\Table(name: 'security_institution')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/institution/{id}/',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Institution:item'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/institutions',
            normalizationContext: [
                'groups' => ['get:Institution:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/institution',
            controller: CreateInstitutionController::class,
            openapiContext: [
                "summary" => "Create custom institution with that resources.",
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
                'groups' => ['write:Institution'],
            ],
            deserialize: false,
        ),
        new Post(
            uriTemplate: '/edit/institution/{id}',
            requirements: ['id' => '\d+'],
            controller: EditInstitutionController::class,
            denormalizationContext: [
                'groups' => ['write:Institution'],
            ],
            deserialize: false,
        ),
        new Delete(
            uriTemplate: '/delete/institution/{id}',
            requirements: ['id' => '\d+'],
            processor: DeleteInstitutionProcessor::class
        ),
    ]
)]
#[UniqueEntity(
    fields: ['code'],
    message: 'This institution already exist',
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This institution already exist',
)]
class Institution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Institution:collection', 'get:Institution:item', 'get:Subject:collection','get:Teacher:collection','get:EvaluationPeriod:collection', 'get:User:collection', 'get:User:item', 'get:Branch:collection', 'get:Year:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Code cannot be smaller than {{ limit }} characters',
        maxMessage: 'Code cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:Institution:collection', 'get:Institution:item', 'write:Institution', 'get:Branch:collection', 'get:User:item','get:User:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Name cannot be smaller than {{ limit }} characters',
        maxMessage: 'Name cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:Institution:collection', 'get:Institution:item','write:Institution', 'get:Branch:collection', 'get:Teacher:collection','get:Subject:collection','get:EvaluationPeriod:collection', 'get:User:collection', 'get:User:item', 'get:Year:collection'])]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Assert\Length(
        max: 50,
        maxMessage: 'Email cannot be longer than {{ limit }} characters',
    )]
    #[Groups([ 'get:Institution:collection', 'get:Institution:item', 'write:Institution', 'get:User:item'])]
    private ?string $email = null;

    #[ORM\Column(length: 30)]
    #[Assert\Length(
        max: 30,
        maxMessage: 'Phone number cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:Institution:collection', 'get:Institution:item', 'write:Institution', 'get:User:item'])]
    private ?string $phone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:Institution:collection', 'get:Institution:item', 'write:Institution', 'get:User:item'])]
    private ?string $address = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Institution:collection', 'get:Institution:item', 'write:Institution'])]
    private ?string $website = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['get:Institution:collection', 'get:Institution:item', 'write:Institution','get:User:item'])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Institution:collection', 'get:Institution:item', 'write:Institution'])]
    private ?string $city = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Institution:collection', 'get:Institution:item', 'write:Institution'])]
    private ?string $region = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Institution:collection', 'get:Institution:item', 'write:Institution'])]
    private ?Country $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Institution:collection', 'get:User:collection', 'get:Institution:item', 'get:User:item'])]
    private ?string $picture = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Institution:collection', 'get:User:collection', 'get:Institution:item', 'get:User:item'])]
    private ?string $fileName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Institution:collection', 'get:User:collection', 'get:Institution:item', 'get:User:item'])]
    private ?string $fileType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Institution:collection', 'get:User:collection', 'get:Institution:item', 'get:User:item'])]
    private ?int $fileSize = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Institution:collection', 'get:Institution:item', 'write:Institution'])]
    private ?string $manager = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Institution:collection', 'get:Institution:item', 'write:Institution'])]
    private ?ManagerType $managerType = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    #[Assert\NotNull]
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

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

    public function getManager(): ?string
    {
        return $this->manager;
    }

    public function setManager(?string $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    public function getManagerType(): ?ManagerType
    {
        return $this->managerType;
    }

    public function setManagerType(?ManagerType $managerType): static
    {
        $this->managerType = $managerType;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }
}
