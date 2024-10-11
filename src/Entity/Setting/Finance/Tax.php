<?php

namespace App\Entity\Setting\Finance;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Setting\Finance\TaxRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TaxRepository::class)]
#[ORM\Table(name: 'setting_tax')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/tax/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Tax:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/tax',
            normalizationContext: [
                'groups' => ['get:Tax:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/tax',
            denormalizationContext: [
                'groups' => ['write:Tax'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/tax/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Tax'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/tax/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class Tax
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Tax:collection','get:SaleInvoiceItem:collection','get:PurchaseInvoiceItem:collection','get:PurchaseReturnInvoiceItem:collection','get:InvoiceSetting:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Tax:collection', 'write:Tax','get:SaleInvoiceItem:collection','get:PurchaseInvoiceItem:collection','get:PurchaseReturnInvoiceItem:collection','get:InvoiceSetting:collection'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Tax:collection', 'write:Tax','get:SaleInvoiceItem:collection'])]
    private ?float $rate = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Tax:collection', 'write:Tax'])]
    private ?string $label = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Tax:collection', 'write:Tax'])]
    private ?string $scope = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Tax:collection', 'write:Tax'])]
    private ?string $typeTax = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Tax:collection', 'write:Tax'])]
    private ?bool $is_default = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(?float $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(?string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getTypeTax(): ?string
    {
        return $this->typeTax;
    }

    public function setTypeTax(?string $typeTax): self
    {
        $this->typeTax = $typeTax;

        return $this;
    }

    public function isIsDefault(): ?bool
    {
        return $this->is_default;
    }

    public function setIsDefault(?bool $is_default): self
    {
        $this->is_default = $is_default;

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
