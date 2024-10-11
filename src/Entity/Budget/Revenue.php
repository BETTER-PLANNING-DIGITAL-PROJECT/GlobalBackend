<?php

namespace App\Entity\Budget;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Budget\GetValidatedRevenueBudgetController;
use App\Controller\Budget\NeedsAccountOperationController;
use App\Controller\Budget\NeedsCashOperationController;
use App\Controller\Budget\PostNeedsController;
use App\Controller\Budget\PostRevenueController;
use App\Controller\Budget\PutNeedsController;
use App\Controller\Budget\PutRevenueController;
use App\Controller\Budget\RevenueAccountOperationController;
use App\Controller\Budget\RevenueCashOperationController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Treasury\Bank;
use App\Entity\Treasury\BankAccount;
use App\Entity\Treasury\CashDesk;
use App\Repository\Budget\RevenueRepository;
use App\State\Provider\Treasury\ValidatedBudgetIncomeTransactionProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: RevenueRepository::class)]
#[ORM\Table(name: 'budget_revenue')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/revenue',
            normalizationContext: [
                'groups' => ['get:Revenue:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/validated-revenue',
            normalizationContext: [
                'groups' => ['get:Revenue:collection'],
            ],
            provider: ValidatedBudgetIncomeTransactionProvider::class,
        ),
        new Post(
            uriTemplate: '/create/revenue',
            controller: PostRevenueController::class,
            denormalizationContext: [
                'groups' => ['write:Revenue'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/revenue/{id}',
            requirements: ['id' => '\d+'],
            controller: PutRevenueController::class,
            denormalizationContext: [
                'groups' => ['write:Revenue'],
            ]
        ),
        new Put(
            uriTemplate: '/revenue/cash/transaction/{id}',
            requirements: ['id' => '\d+'],
            controller: RevenueCashOperationController::class,
            denormalizationContext: [
                'groups' => ['write:Revenue'],
            ]
        ),
        new Put(
            uriTemplate: '/revenue/account/transaction/{id}',
            requirements: ['id' => '\d+'],
            controller: RevenueAccountOperationController::class,
            denormalizationContext: [
                'groups' => ['write:Revenue'],
            ],
            deserialize: false
        ),
        new Delete(
            uriTemplate: '/delete/revenue/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class Revenue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Revenue:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: false)]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?string $reference = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?Budget $budget = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?BudgetExercise $exercise = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?BankAccount $bankAccount = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?Bank $bank = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?CashDesk $cashDesk = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?User $settledBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?\DateTimeImmutable $settled_At = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?float $requestAmount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?float $validatedAmount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?float $vat = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?string $applicant = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?string $reason = null;

    // many to one : groupNeeds : GroupNeeds

    #[ORM\Column]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?bool $isCash = null;

    #[ORM\Column]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?bool $isValidated = null;

    #[ORM\Column]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?bool $isOpen = null;

    #[ORM\Column]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?bool $isEdited = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Revenue:collection', 'write:Revenue'])]
    private ?Year $year = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
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
    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): self
    {
        $this->budget = $budget;

        return $this;
    }

    public function getExercise(): ?BudgetExercise
    {
        return $this->exercise;
    }

    public function setExercise(?BudgetExercise $exercise): self
    {
        $this->exercise = $exercise;

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

    public function getSettledAt(): ?\DateTimeImmutable
    {
        return $this->settled_At;
    }

    public function setSettledAt(?\DateTimeImmutable $settled_At): self
    {
        $this->settled_At = $settled_At;

        return $this;
    }

    public function getRequestAmount(): ?float
    {
        return $this->requestAmount;
    }

    public function setRequestAmount(?float $requestAmount): self
    {
        $this->requestAmount = $requestAmount;

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

    public function getApplicant(): ?string
    {
        return $this->applicant;
    }

    public function setApplicant(?string $applicant): self
    {
        $this->applicant = $applicant;

        return $this;
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

    public function isIsCash(): ?bool
    {
        return $this->isCash;
    }

    public function setIsCash(bool $isCash): static
    {
        $this->isCash = $isCash;

        return $this;
    }

    public function isIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(bool $isValidated): static
    {
        $this->isValidated = $isValidated;

        return $this;
    }

    public function isIsOpen(): ?bool
    {
        return $this->isOpen;
    }

    public function setIsOpen(bool $isOpen): static
    {
        $this->isOpen = $isOpen;

        return $this;
    }

    public function isIsEdited(): ?bool
    {
        return $this->isEdited;
    }

    public function setIsEdited(bool $isEdited): static
    {
        $this->isEdited = $isEdited;

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

    public function getValidatedAmount(): ?float
    {
        return $this->validatedAmount;
    }

    public function setValidatedAmount(?float $validatedAmount): self
    {
        $this->validatedAmount = $validatedAmount;

        return $this;
    }

    public function getVat(): ?float
    {
        return $this->vat;
    }

    public function setVat(?float $vat): self
    {
        $this->vat = $vat;

        return $this;
    }

    public function getSettledBy(): ?User
    {
        return $this->settledBy;
    }

    public function setSettledBy(?User $settledBy): self
    {
        $this->settledBy = $settledBy;

        return $this;
    }
}
