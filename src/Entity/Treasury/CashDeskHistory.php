<?php

namespace App\Entity\Treasury;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Finance\OperationCategory;
use App\Repository\Treasury\CashDeskHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CashDeskHistoryRepository::class)]
#[ORM\Table(name: 'treasury_cash_desk_history')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/cash-desk-history',
            normalizationContext: [
                'groups' => ['get:CashDeskHistory:collection'],
            ],
        ),
        /*new Post(
            uriTemplate: '/create/cash-desk-history',
            denormalizationContext: [
                'groups' => ['write:CashDeskHistory'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/cash-desk-history/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:CashDeskHistory'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/cash-desk-history/{id}',
            requirements: ['id' => '\d+'],
        ),*/
    ]
)]

class CashDeskHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:CashDeskHistory:collection'])]
    private ?int $id = null;
	
    #[ORM\ManyToOne]
    #[Groups(['get:CashDeskHistory:collection', 'write:CashDeskHistory'])]
    private ?CashDesk $cashDesk = null;
	
    #[ORM\ManyToOne]
    #[Groups(['get:CashDeskHistory:collection', 'write:CashDeskHistory'])]
    private ?OperationCategory $operationCategory = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:CashDeskHistory:collection', 'write:CashDeskHistory'])]
    private ?string $reference = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:CashDeskHistory:collection', 'write:CashDeskHistory'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CashDeskHistory:collection', 'write:CashDeskHistory'])]
    private ?float $debit = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CashDeskHistory:collection', 'write:CashDeskHistory'])]
    private ?float $credit = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:CashDeskHistory:collection', 'write:CashDeskHistory'])]
    private ?string $balance = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CashDeskHistory:collection', 'write:CashDeskHistory'])]
    private ?\DateTimeImmutable $dateAt = null;

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

    public function getCashDesk(): ?CashDesk
    {
        return $this->cashDesk;
    }

    public function setCashDesk(?CashDesk $cashDesk): self
    {
        $this->cashDesk = $cashDesk;

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

    public function getDebit(): ?float
    {
        return $this->debit;
    }

    public function setDebit(?float $debit): self
    {
        $this->debit = $debit;

        return $this;
    }

    public function getCredit(): ?float
    {
        return $this->credit;
    }

    public function setCredit(?float $credit): self
    {
        $this->credit = $credit;

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

    public function getDateAt(): ?\DateTimeImmutable
    {
        return $this->dateAt;
    }

    public function setDateAt(?\DateTimeImmutable $dateAt): self
    {
        $this->dateAt = $dateAt;

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
