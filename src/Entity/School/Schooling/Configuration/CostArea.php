<?php

namespace App\Entity\School\Schooling\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Finance\ExpenseHeading;
use App\Repository\School\Schooling\Configuration\CostAreaRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CostAreaRepository::class)]
#[ORM\Table(name: 'school_cost_area')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/cost-area/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:CostArea:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/cost-area',
            normalizationContext: [
                'groups' => ['get:CostArea:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/cost-area',
            denormalizationContext: [
                'groups' => ['write:CostArea'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/cost-area/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:CostArea'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/cost-area/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/cost-area',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['code'],
    message: 'this code already exist',
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'this name already exist',
)]
class CostArea
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:CostArea:collection', 'get:Tuition:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:CostArea:collection', 'write:CostArea', 'get:Tuition:collection', 'get:Fee:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:CostArea:collection', 'write:CostArea', 'get:Tuition:collection', 'get:Fee:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:CostArea:collection', 'write:CostArea'])]
    private ?ExpenseHeading $expenseHeading = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CostArea:collection', 'write:CostArea'])]
    private ?bool $isamountorquantity = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Groups(['get:CostArea:collection', 'write:CostArea'])]
    private ?bool $isfornewstudent = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Groups(['get:CostArea:collection', 'write:CostArea'])]
    private ?bool $isforoldstudent = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Groups(['get:CostArea:collection', 'write:CostArea'])]
    private ?bool $isforrepeatingstudent = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Groups(['get:CostArea:collection', 'write:CostArea'])]
    private ?bool $isfornonrepeatingstudent = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

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
        $this->isamountorquantity = 1;

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

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getExpenseHeading(): ?ExpenseHeading
    {
        return $this->expenseHeading;
    }

    public function setExpenseHeading(?ExpenseHeading $expenseHeading): static
    {
        $this->expenseHeading = $expenseHeading;

        return $this;
    }

    public function isIsAmountOrQuantity(): ?bool
    {
        return $this->isamountorquantity;
    }

    public function setIsAmountOrQuantity(?bool $isamountorquantity): static
    {
        $this->isamountorquantity = $isamountorquantity;

        return $this;
    }

    public function isIsForNewStudent(): ?bool
    {
        return $this->isfornewstudent;
    }

    public function setIsForNewStudent(bool $isfornewstudent): static
    {
        $this->isfornewstudent = $isfornewstudent;

        return $this;
    }

    public function isIsForOldStudent(): ?bool
    {
        return $this->isforoldstudent;
    }

    public function setIsForOldStudent(bool $isforoldstudent): static
    {
        $this->isforoldstudent = $isforoldstudent;

        return $this;
    }

    public function isIsForRepeatingStudent(): ?bool
    {
        return $this->isforrepeatingstudent;
    }

    public function setIsForRepeatingStudent(bool $isforrepeatingstudent): static
    {
        $this->isforrepeatingstudent = $isforrepeatingstudent;

        return $this;
    }

    public function isIsForNonRepeatingStudent(): ?bool
    {
        return $this->isfornonrepeatingstudent;
    }

    public function setIsForNonRepeatingStudent(bool $isfornonrepeatingstudent): static
    {
        $this->isfornonrepeatingstudent = $isfornonrepeatingstudent;

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
