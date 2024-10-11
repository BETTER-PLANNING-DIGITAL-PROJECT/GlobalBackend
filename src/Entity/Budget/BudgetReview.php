<?php

namespace App\Entity\Budget;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Budget\PostBudgetReviewController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Budget\BudgetReviewRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BudgetReviewRepository::class)]
#[ORM\Table(name: 'budget_review')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/budget-review/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:BudgetReview:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/budget-review',
            normalizationContext: [
                'groups' => ['get:BudgetReview:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/budget-review',
            controller: PostBudgetReviewController::class,
            denormalizationContext: [
                'groups' => ['write:BudgetReview'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/budget-review/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:BudgetReview'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/budget-review/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class BudgetReview
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:BudgetReview:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:BudgetReview:collection', 'write:BudgetReview'])]
    private ?Budget $budget = null;

    #[ORM\ManyToOne]
    #[Groups(['get:BudgetReview:collection', 'write:BudgetReview'])]
    private ?BudgetExercise $exercise = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:BudgetReview:collection', 'write:BudgetReview'])]
    private ?float $amount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:BudgetReview:collection', 'write:BudgetReview'])]
    private ?bool $isType = null;

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

    #[ORM\ManyToOne]
    #[Groups(['get:BudgetReview:collection', 'write:BudgetReview'])]
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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function isIsType(): ?bool
    {
        return $this->isType;
    }

    public function setIsType(bool $isType): self
    {
        $this->isType = $isType;

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

}
