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
use App\Repository\Billing\InvoiceModelRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InvoiceModelRepository::class)]
#[ORM\Table(name: 'billing_invoice_model')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/invoice-model',
            normalizationContext: [
                'groups' => ['get:InvoiceModel:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/current-year',
            controller: CurrentYearController::class,
            normalizationContext: [
                'groups' => ['get:InvoiceModel:collection'],
            ],
        ),

        new Post(
            uriTemplate: '/create/invoice-model',
            denormalizationContext: [
                'groups' => ['write:InvoiceModel'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/invoice-model/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:InvoiceModel'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/invoice-model/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This Model already exist.',
)]
class InvoiceModel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:InvoiceModel:collection','get:SpecificInvoice:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:InvoiceModel:collection', 'write:InvoiceModel'])]
    private ?Year $year = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:InvoiceModel:collection', 'write:InvoiceModel'])]
    private ?string $code = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:InvoiceModel:collection', 'write:InvoiceModel'])]
    private ?string $name = null;

    #[ORM\Column]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:InvoiceModel:collection', 'write:InvoiceModel'])]
    private ?int $position = null;

    #[ORM\ManyToOne]
    #[Groups(['get:InvoiceModel:collection', 'write:InvoiceModel'])]
    private ?InvoiceSection $section = null;

    #[ORM\ManyToOne]
    #[Groups(['get:InvoiceModel:collection', 'write:InvoiceModel'])]
    private ?InvoiceArea $area = null;

    #[ORM\ManyToOne]
    #[Groups(['get:InvoiceModel:collection', 'write:InvoiceModel'])]
    private ?InvoicePeriod $period = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:InvoiceModel:collection', 'write:InvoiceModel'])]
    private ?int $dueDate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:InvoiceModel:collection', 'write:InvoiceModel'])]
    private ?float $amount = null;

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
    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

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

    public function getSection(): ?InvoiceSection
    {
        return $this->section;
    }

    public function setSection(?InvoiceSection $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function getArea(): ?InvoiceArea
    {
        return $this->area;
    }

    public function setArea(?InvoiceArea $area): self
    {
        $this->area = $area;

        return $this;
    }

    public function getPeriod(): ?InvoicePeriod
    {
        return $this->period;
    }

    public function setPeriod(?InvoicePeriod $period): self
    {
        $this->period = $period;

        return $this;
    }
    public function getDueDate(): ?int
    {
        return $this->dueDate;
    }

    public function setDueDate(?int $dueDate): self
    {
        $this->dueDate = $dueDate;

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
