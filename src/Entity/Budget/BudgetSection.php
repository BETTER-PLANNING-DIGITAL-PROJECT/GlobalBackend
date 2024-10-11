<?php

namespace App\Entity\Budget;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Budget\BudgetSectionUniquenessPerLevelController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Budget\BudgetSectionRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BudgetSectionRepository::class)]
#[ORM\Table(name: 'budget_section')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/budget-section/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:BudgetSection:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/budget-section',
            normalizationContext: [
                'groups' => ['get:BudgetSection:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/budget-section',
            controller: BudgetSectionUniquenessPerLevelController::class,
            denormalizationContext: [
                'groups' => ['write:BudgetSection'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/budget-section/{id}',
            requirements: ['id' => '\d+'],
//            controller: BudgetSectionUniquenessPerLevelController::class,
            denormalizationContext: [
                'groups' => ['write:BudgetSection'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/budget-section/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]

class BudgetSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:BudgetSection:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:BudgetSection:collection', 'write:BudgetSection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:BudgetSection:collection', 'write:BudgetSection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[Groups(['get:BudgetSection:collection', 'write:BudgetSection'])]
    private ?BudgetSectionLevel $level = null;

    #[ORM\ManyToOne]
    #[Groups(['get:BudgetSection:collection', 'write:BudgetSection'])]
    private ?BudgetManager $manager = null;

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
    #[Groups(['get:Budget:collection', 'write:Budget'])]
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

    public function getLevel(): ?BudgetSectionLevel
    {
        return $this->level;
    }

    public function setLevel(?BudgetSectionLevel $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getManager(): ?BudgetManager
    {
        return $this->manager;
    }

    public function setManager(?BudgetManager $manager): self
    {
        $this->manager = $manager;

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

}
