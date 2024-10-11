<?php

namespace App\Entity\Billing;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Security\CurrentYearController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Billing\InvoiceSectionRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InvoiceSectionRepository::class)]
#[ORM\Table(name: 'billing_invoice_section')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/invoice-section',
            normalizationContext: [
                'groups' => ['get:InvoiceSection:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/current-year',
            controller: CurrentYearController::class,
            normalizationContext: [
                'groups' => ['get:InvoiceSection:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/invoice-section',
            denormalizationContext: [
                'groups' => ['write:InvoiceSection'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/invoice-section/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:InvoiceSection'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/invoice-section/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This section already exist.',
)]
class InvoiceSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:InvoiceSection:collection','get:SpecificInvoice:collection','get:InvoiceModel:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:InvoiceSection:collection', 'write:InvoiceSection'])]
    private ?Year $year = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:InvoiceSection:collection', 'write:InvoiceSection'])]
    private ?string $code = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:InvoiceSection:collection', 'write:InvoiceSection','get:SpecificInvoice:collection','get:InvoiceModel:collection'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:InvoiceSection:collection', 'write:InvoiceSection'])]
    private ?float $amount = null;

    // Budget Heading

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
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

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
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
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

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
