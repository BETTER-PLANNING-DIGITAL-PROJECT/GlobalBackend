<?php

namespace App\Entity\Budget;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Budget\CreateMultipleBudgetByRateController;
use App\Controller\Budget\GetValidatedBudgetController;
use App\Controller\Budget\GetValidatedRevenueBudgetController;
use App\Controller\Budget\PostBudgetController;
use App\Controller\Budget\PutBudgetController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Budget\BudgetRepository;
use App\State\Processor\Budget\PostBudgetWithRateProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BudgetRepository::class)]
#[ORM\Table(name: 'budget')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/budget/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Budget:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/budget',
            normalizationContext: [
                'groups' => ['get:Budget:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/validated-budget-needs',
            controller: GetValidatedBudgetController::class,
            normalizationContext: [
                'groups' => ['get:Budget:collection'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/get/validated-budget-revenue',
            controller: GetValidatedRevenueBudgetController::class,
            normalizationContext: [
                'groups' => ['get:Budget:collection'],
            ]
        ),
        new Post(
            uriTemplate: '/create/budget',
            controller: PostBudgetController::class,
            denormalizationContext: [
                'groups' => ['write:Budget'],
            ],
        ),
        new Post(
            uriTemplate: '/create/budget/rate',
            denormalizationContext: [
                'groups' => ['write:Budget'],
            ],
            processor: PostBudgetWithRateProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/budget/{id}',
            requirements: ['id' => '\d+'],
            controller: PutBudgetController::class,
            denormalizationContext: [
                'groups' => ['write:Budget'],
            ],
        ),


        new Post(
            uriTemplate: '/read/multiple/budget/rate',
            controller: CreateMultipleBudgetByRateController::class,
            denormalizationContext: [
                'groups' => ['write:Budget'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/budget/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
  class Budget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Budget:collection','get:BudgetReview:collection','get:Needs:collection','get:Revenue:collection', 'get:BudgetHistory:collection'])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Budget:collection', 'write:Budget'])]
    private ?float $amount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Budget:collection', 'write:Budget', 'get:BudgetReview:collection'])]
    private ?float $validatedAmount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Budget:collection', 'write:Budget', 'get:Needs:collection'])]
    private ?float $allocatedAmount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Budget:collection', 'write:Budget', 'get:Needs:collection'])]
    private ?float $spentAmount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Budget:collection', 'write:Budget', 'get:Needs:collection'])]
    private ?float $leftAmount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Budget:collection', 'write:Budget', 'get:BudgetReview:collection'])]
    private ?float $rate = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Budget:collection', 'write:Budget', 'get:BudgetReview:collection', 'get:Needs:collection', 'get:Revenue:collection', 'get:BudgetHistory:collection'])]
    private ?BudgetLine $line = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Budget:collection', 'write:Budget', 'get:BudgetReview:collection', 'get:Needs:collection', 'get:Revenue:collection'])]
    private ?BudgetExercise $exercise = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Budget:collection', 'write:Budget'])]
    private ?bool $isIncrease = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

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

    public function getLine(): ?BudgetLine
    {
        return $this->line;
    }

    public function setLine(?BudgetLine $line): self
    {
        $this->line = $line;

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

    public function isIsIncrease(): ?bool
    {
        return $this->isIncrease;
    }

    public function setIsIncrease(?bool $isIncrease): self
    {
        $this->isIncrease = $isIncrease;

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

    public function getAllocatedAmount(): ?float
    {
        return $this->allocatedAmount;
    }

    public function setAllocatedAmount(?float $allocatedAmount): self
    {
        $this->allocatedAmount = $allocatedAmount;

        return $this;
    }

    public function getSpentAmount(): ?float
    {
        return $this->spentAmount;
    }

    public function setSpentAmount(?float $spentAmount): self
    {
        $this->spentAmount = $spentAmount;

        return $this;
    }

    public function getLeftAmount(): ?float
    {
        return $this->leftAmount;
    }

    public function setLeftAmount(?float $leftAmount): self
    {
        $this->leftAmount = $leftAmount;

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
