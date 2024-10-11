<?php

namespace App\Entity\Treasury;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Treasury\BankRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BankRepository::class)]
#[ORM\Table(name: 'treasury_bank')]
#[ApiResource(
    operations:[

        new Get(
            uriTemplate: '/get/bank/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Bank:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/bank',
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:Bank:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/bank',
            denormalizationContext: [
                'groups' => ['write:Bank'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/bank/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Bank'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/bank/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['code'],
    message: 'this code already exist.',
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'this name already exist.',
)]
class Bank
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:SaleSettlement:collection','get:Bank:collection', 'get:BankAccount:collection', 'get:Needs:collection', 'get:BankHistory:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:Bank:collection', 'write:Bank', 'get:SaleSettlement:collection','get:BankAccount:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:SaleSettlement:collection','get:Bank:collection', 'write:Bank','get:BankAccount:collection', 'get:Needs:collection', 'get:BankHistory:collection'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Bank:collection', 'write:Bank'])]
    private ?int $orderNumber = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Bank:collection', 'write:Bank'])]
    private ?string $address = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Bank:collection', 'write:Bank'])]
    private ?string $email = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Bank:collection', 'write:Bank'])]
    private ?string $phone = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Bank:collection', 'write:Bank'])]
    private ?string $postbox = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Bank:collection', 'write:Bank'])]
    private ?string $taxPayerNumber = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Bank:collection', 'write:Bank'])]
    private ?string $businessNumber = null;

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

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOrderNumber(): ?int
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(?int $orderNumber): self
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }


    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

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

    public function getPostbox(): ?string
    {
        return $this->postbox;
    }

    public function setPostbox(?string $postbox): self
    {
        $this->postbox = $postbox;

        return $this;
    }

    public function getTaxPayerNumber(): ?string
    {
        return $this->taxPayerNumber;
    }

    public function setTaxPayerNumber(?string $taxPayerNumber): self
    {
        $this->taxPayerNumber = $taxPayerNumber;

        return $this;
    }

    public function getBusinessNumber(): ?string
    {
        return $this->businessNumber;
    }

    public function setBusinessNumber(?string $businessNumber): self
    {
        $this->businessNumber = $businessNumber;

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

}
