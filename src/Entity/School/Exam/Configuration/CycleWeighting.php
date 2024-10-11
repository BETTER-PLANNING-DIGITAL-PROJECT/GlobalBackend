<?php

namespace App\Entity\School\Exam\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\School\Schooling\Configuration\Cycle;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\School\PeriodType;
use App\Repository\School\Exam\Configuration\CycleWeightingRepository;
use App\State\Current\InstitutionProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CycleWeightingRepository::class)]
#[ORM\Table(name: 'school_cycle_weighting')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/cycle/weighting/get',
            normalizationContext: [
                'groups' => ['get:CycleWeighting:collection'],
            ],

        ),
        new Post(
            uriTemplate: '/cycle/weighting/create',
            denormalizationContext: [
                'groups' => ['write:CycleWeighting'],
            ],
            processor: InstitutionProcessor::class,
        ),
        new Put(
            uriTemplate: '/cycle/weighting/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:CycleWeighting'],
            ],
        ),
    ]
)]
class CycleWeighting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:CycleWeighting:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:CycleWeighting:collection'])]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:CycleWeighting:collection'])]
    private ?Cycle $cycle = null;

    #[ORM\Column(type: Types::TEXT,nullable: true)]
    #[Groups(['get:CycleWeighting:collection', 'write:CycleWeighting'])]
    private ?string $sequencePonderations = null;

    #[ORM\ManyToOne]
    #[Groups(['get:CycleWeighting:collection', 'write:CycleWeighting'])]
    private ?PeriodType $periodType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CycleWeighting:collection', 'write:CycleWeighting'])]
    private ?int $numberOfDivision = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CycleWeighting:collection', 'write:CycleWeighting'])]
    private ?float $generalEliminateAverage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CycleWeighting:collection'])]
    private ?float $groupEliminateAverage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CycleWeighting:collection', 'write:CycleWeighting'])]
    private ?float $eliminateMark = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CycleWeighting:collection', 'write:CycleWeighting'])]
    private ?float $validationMark = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CycleWeighting:collection', 'write:CycleWeighting'])]
    private ?float $entryBase = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CycleWeighting:collection', 'write:CycleWeighting'])]
    private ?float $priority = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CycleWeighting:collection', 'write:CycleWeighting'])]
    private ?bool $isMarkForAllSequenceRequired = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CycleWeighting:collection', 'write:CycleWeighting'])]
    private ?bool $isActif = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CycleWeighting:collection', 'write:CycleWeighting'])]
    private ?bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CycleWeighting:collection', 'write:CycleWeighting'])]
    private ?bool $isValidateCompensateModulate = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?bool $is_enable = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?bool $is_archive = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
        $this->is_archive = false;

        $this->isCoefficientOfNullMarkConsiderInTheAverageCalculation = false;
        $this->isValidateCompensateModulate = false;
        $this->isMarkForAllSequenceRequired = false;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPeriodType(): ?PeriodType
    {
        return $this->periodType;
    }

    public function setPeriodType(?PeriodType $periodType): static
    {
        $this->periodType = $periodType;

        return $this;
    }

    public function getGeneralEliminateAverage(): ?float
    {
        return $this->generalEliminateAverage;
    }

    public function setGeneralEliminateAverage(?float $generalEliminateAverage): self
    {
        $this->generalEliminateAverage = $generalEliminateAverage;

        return $this;
    }

    public function getEliminateMark(): ?float
    {
        return $this->eliminateMark;
    }

    public function setEliminateMark(?float $eliminateMark): self
    {
        $this->eliminateMark = $eliminateMark;

        return $this;
    }

    public function getValidationMark(): ?float
    {
        return $this->validationMark;
    }

    public function setValidationMark(?float $validationMark): self
    {
        $this->validationMark = $validationMark;

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

    public function isIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(bool $is_archive): static
    {
        $this->is_archive = $is_archive;

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

    /**
     * @return string|null
     */
    public function getSequencePonderations(): ?string
    {
        return $this->sequencePonderations;
    }

    /**
     * @param string|null $sequencePonderations
     * @return CycleWeighting
     */
    public function setSequencePonderations(?string $sequencePonderations): CycleWeighting
    {
        $this->sequencePonderations = $sequencePonderations;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNumberOfDivision(): ?int
    {
        return $this->numberOfDivision;
    }

    /**
     * @param int|null $numberOfDivision
     * @return CycleWeighting
     */
    public function setNumberOfDivision(?int $numberOfDivision): CycleWeighting
    {
        $this->numberOfDivision = $numberOfDivision;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getEntryBase(): ?float
    {
        return $this->entryBase;
    }

    /**
     * @param float|null $entryBase
     * @return CycleWeighting
     */
    public function setEntryBase(?float $entryBase): CycleWeighting
    {
        $this->entryBase = $entryBase;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isIsMarkForAllSequenceRequired(): ?bool
    {
        return $this->isMarkForAllSequenceRequired;
    }

    /**
     * @param bool|null $isMarkForAllSequenceRequired
     * @return CycleWeighting
     */
    public function setIsMarkForAllSequenceRequired(?bool $isMarkForAllSequenceRequired): CycleWeighting
    {
        $this->isMarkForAllSequenceRequired = $isMarkForAllSequenceRequired;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isIsCoefficientOfNullMarkConsiderInTheAverageCalculation(): ?bool
    {
        return $this->isCoefficientOfNullMarkConsiderInTheAverageCalculation;
    }

    /**
     * @param bool|null $isCoefficientOfNullMarkConsiderInTheAverageCalculation
     * @return CycleWeighting
     */
    public function setIsCoefficientOfNullMarkConsiderInTheAverageCalculation(?bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation): CycleWeighting
    {
        $this->isCoefficientOfNullMarkConsiderInTheAverageCalculation = $isCoefficientOfNullMarkConsiderInTheAverageCalculation;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isIsValidateCompensateModulate(): ?bool
    {
        return $this->isValidateCompensateModulate;
    }

    /**
     * @param bool|null $isValidateCompensateModulate
     * @return CycleWeighting
     */
    public function setIsValidateCompensateModulate(?bool $isValidateCompensateModulate): CycleWeighting
    {
        $this->isValidateCompensateModulate = $isValidateCompensateModulate;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPriority(): ?float
    {
        return $this->priority;
    }

    /**
     * @param float|null $priority
     * @return CycleWeighting
     */
    public function setPriority(?float $priority): CycleWeighting
    {
        $this->priority = $priority;
        return $this;
    }

    public function getIsActif(): ?bool
    {
        return $this->isActif;
    }

    public function setIsActif(?bool $isActif): CycleWeighting
    {
        $this->isActif = $isActif;
        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): CycleWeighting
    {
        $this->year = $year;
        return $this;
    }

    public function getGroupEliminateAverage(): ?float
    {
        return $this->groupEliminateAverage;
    }

    public function setGroupEliminateAverage(?float $groupEliminateAverage): CycleWeighting
    {
        $this->groupEliminateAverage = $groupEliminateAverage;
        return $this;
    }
}
