<?php

namespace App\Entity\Budget;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Budget\BudgetExerciseRepository;
use App\State\Processor\Budget\CloseBudgetExerciseProcessor;
use App\State\Processor\Budget\OpenBudgetExerciseProcessor;
use App\State\Processor\Global\SystemProcessor;
use App\State\Provider\Budget\CurrentlyOpennedBudgetExerciseProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BudgetExerciseRepository::class)]
#[ORM\Table(name: 'budget_exercise')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/budget-exercise/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:BudgetExercise:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/budget-exercise',
            normalizationContext: [
                'groups' => ['get:BudgetExercise:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/open/budget-exercise',
            normalizationContext: [
                'groups' => ['get:BudgetExercise:collection'],
            ],
            provider: CurrentlyOpennedBudgetExerciseProvider::class
        ),
        new Post(
            uriTemplate: '/create/budget-exercise',
            denormalizationContext: [
                'groups' => ['write:BudgetExercise'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/budget-exercise/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:BudgetExercise'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/budget-exercise/{id}',
            requirements: ['id' => '\d+'],
        ),

        new Delete(
            uriTemplate: '/close/budget-exercise/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:BudgetExercise'],
            ],
            processor: CloseBudgetExerciseProcessor::class,
        ),

        new Delete(
            uriTemplate: '/open/budget-exercise/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:BudgetExercise'],
            ],
            processor: OpenBudgetExerciseProcessor::class,
        ),
    ]
)]
#[UniqueEntity(
    fields: ['code'],
    message: 'this code already exist',
)]
class BudgetExercise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:BudgetExercise:collection', 'get:Budget:collection', 'get:BudgetReview:collection', 'get:Needs:collection', 'get:Revenue:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:BudgetExercise:collection', 'write:BudgetExercise', 'get:Budget:collection', 'get:BudgetReview:collection', 'get:Needs:collection', 'get:Revenue:collection'])]
    private ?string $code = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['get:BudgetExercise:collection', 'write:BudgetExercise', 'get:Budget:collection'])]
    private ?\DateTimeInterface $startAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['get:BudgetExercise:collection', 'write:BudgetExercise', 'get:Budget:collection'])]
    private ?\DateTimeInterface $endAt = null;

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
    #[Groups(['get:BudgetExercise:collection', 'write:BudgetExercise'])]
    private ?Year $year = null;

    #[ORM\Column]
    #[Groups(['get:BudgetExercise:collection', 'write:BudgetExercise'])]
    private ?bool $isClose = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->isClose = false;
        $this->is_enable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function isIsClose(): ?bool
    {
        return $this->isClose;
    }

    public function setIsClose(bool $isClose): self
    {
        $this->isClose = $isClose;

        return $this;
    }
}
