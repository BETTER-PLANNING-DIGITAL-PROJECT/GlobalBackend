<?php

namespace App\Entity\Treasury;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Treasury\CashDeskOperationController;
use App\Controller\Treasury\GetCashDeskOperationController;
use App\Controller\Treasury\ValidateCashDeskOperationController;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Finance\OperationCategory;
use App\Repository\Treasury\CashDeskOperationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CashDeskOperationRepository::class)]
#[ORM\Table(name: 'treasury_cash_desk_operation')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/cash-desk-operation/{id}/',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:CashDeskOperation:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/cash-desk-operation',
            controller: GetCashDeskOperationController::class,
            normalizationContext: [
                'groups' => ['get:CashDeskOperation:collection'],
            ],
        ),
        // Create cash desk operation
        new Post(
            uriTemplate: '/create/cash-desk-operation',
            controller: CashDeskOperationController::class,
            denormalizationContext: [
                'groups' => ['write:CashDeskOperation'],
            ],
            // processor: SystemProcessor::class,
        ),

        // Validate cash desk operation
        new Delete(
            uriTemplate: '/validate/cash-desk-operation/{id}',
            requirements: ['id' => '\d+'],
            controller: ValidateCashDeskOperationController::class,
            denormalizationContext: [
                'groups' => ['write:CashDeskOperation'],
            ],

        ),
    ]
)]
class CashDeskOperation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:CashDeskOperation:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:CashDeskOperation:collection', 'write:CashDeskOperation'])]
    private ?OperationCategory $operationCategory = null;

    #[ORM\ManyToOne]
    #[Groups(['get:CashDeskOperation:collection', 'write:CashDeskOperation'])]
    private ?CashDesk $vault = null;

    #[ORM\ManyToOne]
    #[Groups(['get:CashDeskOperation:collection', 'write:CashDeskOperation'])]
    private ?CashDesk $cashDesk = null;

    #[ORM\ManyToOne]
    #[Groups(['get:CashDeskOperation:collection', 'write:CashDeskOperation'])]
    private ?User $validateBy = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:CashDeskOperation:collection', 'write:CashDeskOperation'])]
    private ?string $reference = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:CashDeskOperation:collection', 'write:CashDeskOperation'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:CashDeskOperation:collection', 'write:CashDeskOperation'])]
    private ?string $amount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CashDeskOperation:collection', 'write:CashDeskOperation'])]
    private ?bool $isValidate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CashDeskOperation:collection', 'write:CashDeskOperation'])]
    private ?\DateTimeImmutable $validate_At = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:CashDeskOperation:collection', 'write:CashDeskOperation'])]
    private ?string $reason = null;

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
    #[Groups(['get:CashDeskOperation:collection', 'write:CashDeskOperation'])]
    private ?Branch $branch;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function isIsValidate(): ?bool
    {
        return $this->isValidate;
    }

    public function setIsValidate(?bool $isValidate): self
    {
        $this->isValidate = $isValidate;

        return $this;
    }

    public function getValidateAt(): ?\DateTimeImmutable
    {
        return $this->validate_At;
    }

    public function setValidateAt(?\DateTimeImmutable $validate_At): self
    {
        $this->validate_At = $validate_At;

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
    public function getOperationCategory(): ?OperationCategory
    {
        return $this->operationCategory;
    }

    public function setOperationCategory(?OperationCategory $operationCategory): self
    {
        $this->operationCategory = $operationCategory;

        return $this;
    }
    public function getVault(): ?CashDesk
    {
        return $this->vault;
    }

    public function setVault(?CashDesk $vault): self
    {
        $this->vault = $vault;

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

    public function getCashDesk(): ?CashDesk
    {
        return $this->cashDesk;
    }

    public function setCashDesk(?CashDesk $cashDesk): self
    {
        $this->cashDesk = $cashDesk;

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
