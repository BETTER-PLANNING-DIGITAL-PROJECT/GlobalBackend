<?php

namespace App\Entity\Treasury;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Treasury\GetBankAccountController;
use App\Controller\Treasury\PostBankAccountController;
use App\Controller\Treasury\PutBankAccountController;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Treasury\BankAccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BankAccountRepository::class)]
#[ORM\Table(name: 'treasury_bank_account')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/bank-account/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:BankAccount:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/bank-account',
            controller: GetBankAccountController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:BankAccount:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/bank-account',
            controller: PostBankAccountController::class,
            denormalizationContext: [
                'groups' => ['write:BankAccount'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/bank-account/{id}',
            requirements: ['id' => '\d+'],
            controller: PutBankAccountController::class,
            denormalizationContext: [
                'groups' => ['write:BankAccount'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/bank-account/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]

)]
/*#[UniqueEntity(
    fields: ['bank','accountNumber'],
    message: 'this bank account already exist.',
    errorPath: 'accountNumber',
)]*/
class BankAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:SaleSettlement:collection','get:BankAccount:collection','get:Supplier:collection','get:BankHistory:collection','get:BankOperation:collection','get:Customer:collection','get:CashDeskOperation:collection', 'get:Needs:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:BankAccount:collection', 'write:BankAccount'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Bank $bank = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['get:BankAccount:collection', 'write:BankAccount'])]
    private ?string $codeSwift = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['get:BankAccount:collection', 'write:BankAccount'])]
    private ?string $codeIbam = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['get:BankAccount:collection', 'write:BankAccount'])]
    private ?string $codeBranch = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:BankAccount:collection', 'write:BankAccount','get:Supplier:collection','get:SaleSettlement:collection','get:BankOperation:collection'])]
    private ?string $accountNumber = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:BankAccount:collection', 'write:BankAccount'])]
    private ?string $codeRib = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:BankAccount:collection', 'write:BankAccount'])]
    private ?string $balance = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:SaleSettlement:collection','get:BankAccount:collection', 'write:BankAccount','get:BankHistory:collection','get:Supplier:collection','get:Customer:collection','get:BankOperation:collection','get:CashDeskOperation:collection', 'get:Needs:collection'])]
    private ?string $accountName = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:BankAccount:collection', 'write:BankAccount'])]
    private ?bool $isDefault = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:BankAccount:collection', 'write:BankAccount'])]
    private ?Branch $branch;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

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
    public function getCodeSwift(): ?string
    {
        return $this->codeSwift;
    }

    public function setCodeSwift(?string $codeSwift): self
    {
        $this->codeSwift = $codeSwift;

        return $this;
    }

    public function getCodeIbam(): ?string
    {
        return $this->codeIbam;
    }

    public function setCodeIbam(?string $codeIbam): self
    {
        $this->codeIbam = $codeIbam;

        return $this;
    }

    public function getCodeBranch(): ?string
    {
        return $this->codeBranch;
    }

    public function setCodeBranch(?string $codeBranch): self
    {
        $this->codeBranch = $codeBranch;

        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): self
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function getCodeRib(): ?string
    {
        return $this->codeRib;
    }

    public function setCodeRib(?string $codeRib): self
    {
        $this->codeRib = $codeRib;

        return $this;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(?string $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getAccountName(): ?string
    {
        return $this->accountName;
    }

    public function setAccountName(?string $accountName): self
    {
        $this->accountName = $accountName;

        return $this;
    }

    public function isIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(?bool $isDefault): self
    {
        $this->isDefault = $isDefault;

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

    public function getBank(): ?Bank
    {
        return $this->bank;
    }

    public function setBank(?Bank $bank): self
    {
        $this->bank = $bank;

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
