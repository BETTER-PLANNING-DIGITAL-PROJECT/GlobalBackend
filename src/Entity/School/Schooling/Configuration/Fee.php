<?php

namespace App\Entity\School\Schooling\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Controller\School\Schooling\Configuration\DeleteFeeController;
use App\Controller\School\Schooling\Configuration\GetFeeController;
use App\Controller\School\Schooling\Configuration\ImportFeeController;
use App\Controller\School\Schooling\Configuration\PostFeeController;
use App\Controller\School\Schooling\Configuration\PutFeeController;
use App\Entity\Product\Unit;
use App\Entity\Setting\Finance\PaymentGateway;
use App\Entity\Budget\BudgetLine;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\FeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FeeRepository::class)]
#[ORM\Table(name: 'school_fee')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/fee/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Fee:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/fee',
            controller: GetFeeController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:Fee:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/fee',
            controller: PostFeeController::class,

            denormalizationContext: [
                'groups' => ['write:Fee'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/fee/{id}',
            requirements: ['id' => '\d+'],
            controller: PutFeeController::class,
            denormalizationContext: [
                'groups' => ['write:Fee'],
            ],
        ),
        new Post(
            uriTemplate: '/import/fee',
            controller: ImportFeeController::class,
            openapiContext: [
                "summary" => "Add multiple Class resources.",
            ],
            denormalizationContext: [
                'groups' => ['write:Fee']
            ],
        ),
        new Delete(
            uriTemplate: '/delete/fee/{id}',
            requirements: ['id' => '\d+'],
            controller: DeleteFeeController::class,
        ),
        new Delete(
            uriTemplate: '/delete/selected/fee',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
class Fee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Fee:collection', 'get:Payment:collection', 'get:FeeInstallment:collection', 'get:Item:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank!')]
    #[Groups(['get:Fee:collection', 'write:Fee', 'get:FeeInstallment:collection', 'get:Item:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank!')]
    #[Groups(['get:Fee:collection', 'write:Fee', 'get:FeeInstallment:collection', 'get:Item:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Fee:collection', 'write:Fee', 'get:Item:collection'])]
    private ?CostArea $costArea = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Fee:collection', 'write:Fee', 'get:Item:collection'])]
    private ?SchoolClass $class = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Fee:collection', 'write:Fee'])]
    private ?PensionScheme $pensionScheme = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Fee:collection', 'write:Fee'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Fee:collection', 'write:Fee'])]
    private ?Cycle $cycle = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Fee:collection', 'write:Fee'])]
    private ?Speciality $speciality = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Fee:collection', 'write:Fee'])]
    private ?Level $level = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Fee:collection', 'write:Fee'])]
    private ?TrainingType $trainingType = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Fee:collection', 'write:Fee'])]
    private ?BudgetLine $budgetLine = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 4)]
    #[Groups(['get:Fee:collection', 'write:Fee', 'get:Payment:collection'])]
    private ?string $amount = null;

    #[ORM\Column]
    #[Groups(['get:Fee:collection', 'write:Fee'])]
    private ?\DateTimeImmutable $paymentDate = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Fee:collection', 'write:Fee'])]
    private ?Year $year = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['write:Fee'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: PaymentGateway::class)]
    #[ORM\JoinTable(name: 'school_fee_payment_gateway')]
    #[Groups(['get:Fee:collection', 'write:Fee'])]
    private Collection $paymentGateways;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Fee:collection', 'write:Fee'])]
    private ?int $position = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Fee:collection'])]
    private ?bool $isPaymentFee = null;

    #[ORM\ManyToOne]
    private ?Unit $unit = null;

    #[ORM\Column(nullable: true)]
    private ?float $defaultQuantity = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
        $this->isPaymentFee = false;
        $this->paymentGateways = new ArrayCollection();
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

    public function getCostArea(): ?CostArea
    {
        return $this->costArea;
    }

    public function setCostArea(?CostArea $costArea): static
    {
        $this->costArea = $costArea;

        return $this;
    }

    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function setClass(?SchoolClass $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function getPensionScheme(): ?PensionScheme
    {
        return $this->pensionScheme;
    }

    public function setPensionScheme(?PensionScheme $pensionScheme): static
    {
        $this->pensionScheme = $pensionScheme;

        return $this;
    }

    public function getSpeciality(): ?Speciality
    {
        return $this->speciality;
    }

    public function setSpeciality(?Speciality $speciality): static
    {
        $this->speciality = $speciality;

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getTrainingType(): ?TrainingType
    {
        return $this->trainingType;
    }

    public function setTrainingType(?TrainingType $trainingType): static
    {
        $this->trainingType = $trainingType;

        return $this;
    }

    public function getBudgetLine(): ?BudgetLine
    {
        return $this->budgetLine;
    }

    public function setBudgetLine(?BudgetLine $budgetLine): self
    {
        $this->budgetLine = $budgetLine;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

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

    public function getCycle(): ?Cycle
    {
        return $this->cycle;
    }

    public function setCycle(?Cycle $cycle): static
    {
        $this->cycle = $cycle;

        return $this;
    }
    
    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): static
    {
        $this->school = $school;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeImmutable
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(\DateTimeImmutable $paymentDate): static
    {
        $this->paymentDate = $paymentDate;

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

    /**
     * @return Collection<int, PaymentGateway>
     */
    public function getPaymentGateways(): Collection
    {
        return $this->paymentGateways;
    }

    public function addPaymentGateway(PaymentGateway $paymentGateway): static
    {
        if (!$this->paymentGateways->contains($paymentGateway)) {
            $this->paymentGateways->add($paymentGateway);
        }

        return $this;
    }

    public function removePaymentGateway(PaymentGateway $paymentGateway): static
    {
        $this->paymentGateways->removeElement($paymentGateway);

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function isIsPaymentFee(): ?bool
    {
        return $this->isPaymentFee;
    }

    public function setIsPaymentFee(?bool $isPaymentFee): self
    {
        $this->isPaymentFee = $isPaymentFee;

        return $this;
    }

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getDefaultQuantity(): ?float
    {
        return $this->defaultQuantity;
    }

    public function setDefaultQuantity(?float $defaultQuantity): self
    {
        $this->defaultQuantity = $defaultQuantity;

        return $this;
    }

}
