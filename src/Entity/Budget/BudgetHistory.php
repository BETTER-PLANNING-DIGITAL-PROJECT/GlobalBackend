<?php

namespace App\Entity\Budget;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Budget\BudgetHistoryRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BudgetHistoryRepository::class)]
#[ORM\Table(name: 'budget_history')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/budget-history',
            normalizationContext: [
                'groups' => ['get:BudgetHistory:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/budget-history',
            denormalizationContext: [
                'groups' => ['write:BudgetHistory'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/budget-history/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:BudgetHistory'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/budget-history/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]

class BudgetHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:BudgetHistory:collection'])]
    private ?int $id = null;
	
    #[ORM\ManyToOne]
    #[Groups(['get:BudgetHistory:collection', 'write:BudgetHistory'])]
    private ?Budget $budget = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:BudgetHistory:collection', 'write:BudgetHistory'])]
    private ?string $reference = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:BudgetHistory:collection', 'write:BudgetHistory'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:BudgetHistory:collection', 'write:BudgetHistory'])]
    private ?float $debit = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:BudgetHistory:collection', 'write:BudgetHistory'])]
    private ?float $credit = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:BudgetHistory:collection', 'write:BudgetHistory'])]
    private ?string $balance = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:BudgetHistory:collection', 'write:BudgetHistory'])]
    private ?\DateTimeImmutable $dateAt = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;


    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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
