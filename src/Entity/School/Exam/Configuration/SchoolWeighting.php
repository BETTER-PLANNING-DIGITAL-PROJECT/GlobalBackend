<?php

namespace App\Entity\School\Exam\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\School\PeriodType;
use App\Repository\School\Exam\Configuration\SchoolWeightingRepository;
use App\State\Current\InstitutionProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SchoolWeightingRepository::class)]
#[ORM\Table(name: 'school_school_weighting')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/school/weighting/get',
            normalizationContext: [
                'groups' => ['get:SchoolWeighting:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/school/weighting/create',
            denormalizationContext: [
                'groups' => ['write:SchoolWeighting'],
            ],
            processor: InstitutionProcessor::class,
        ),
        new Put(
            uriTemplate: '/school/weighting/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SchoolWeighting'],
            ],
        ),
    ]
)]
class SchoolWeighting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:SchoolWeighting:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:SchoolWeighting:collection'])]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:SchoolWeighting:collection'])]
    private ?School $school = null;

    #[ORM\Column(type: Types::TEXT,nullable: true)]
    #[Groups(['get:SchoolWeighting:collection', 'write:SchoolWeighting'])]
    private ?string $sequencePonderations = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SchoolWeighting:collection', 'write:SchoolWeighting'])]
    private ?PeriodType $periodType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SchoolWeighting:collection', 'write:SchoolWeighting'])]
    private ?int $numberOfDivision = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SchoolWeighting:collection', 'write:SchoolWeighting'])]
    private ?float $generalEliminateAverage = null;


    #[ORM\Column(nullable: true)]
    #[Groups(['get:SchoolWeighting:collection', 'write:SchoolWeighting'])]
    private ?float $groupEliminateAverage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SchoolWeighting:collection', 'write:SchoolWeighting'])]
    private ?float $eliminateMark = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SchoolWeighting:collection', 'write:SchoolWeighting'])]
    private ?float $validationMark = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SchoolWeighting:collection', 'write:SchoolWeighting'])]
    private ?float $entryBase = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SchoolWeighting:collection', 'write:SchoolWeighting'])]
    private ?float $priority = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SchoolWeighting:collection', 'write:SchoolWeighting'])]
    private ?bool $isMarkForAllSequenceRequired = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SchoolWeighting:collection', 'write:SchoolWeighting'])]
    private ?bool $isActif = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SchoolWeighting:collection', 'write:SchoolWeighting'])]
    private ?bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SchoolWeighting:collection', 'write:SchoolWeighting'])]
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

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): static
    {
        $this->school = $school;

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

    public function getSequencePonderations(): ?string
    {
        return $this->sequencePonderations;
    }

    public function setSequencePonderations(?string $sequencePonderations): self
    {
        $this->sequencePonderations = $sequencePonderations;
        return $this;
    }

    public function getNumberOfDivision(): ?int
    {
        return $this->numberOfDivision;
    }

    public function setNumberOfDivision(?int $numberOfDivision): self
    {
        $this->numberOfDivision = $numberOfDivision;
        return $this;
    }


    public function getEntryBase(): ?float
    {
        return $this->entryBase;
    }

    public function setEntryBase(?float $entryBase): self
    {
        $this->entryBase = $entryBase;
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

    /**
     * @return bool|null
     */
    public function isIsMarkForAllSequenceRequired(): ?bool
    {
        return $this->isMarkForAllSequenceRequired;
    }

    /**
     * @param bool|null $isMarkForAllSequenceRequired
     * @return SchoolWeighting
     */
    public function setIsMarkForAllSequenceRequired(?bool $isMarkForAllSequenceRequired): SchoolWeighting
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
     * @return SchoolWeighting
     */
    public function setIsCoefficientOfNullMarkConsiderInTheAverageCalculation(?bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation): SchoolWeighting
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
     * @return SchoolWeighting
     */
    public function setIsValidateCompensateModulate(?bool $isValidateCompensateModulate): SchoolWeighting
    {
        $this->isValidateCompensateModulate = $isValidateCompensateModulate;
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

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): SchoolWeighting
    {
        $this->year = $year;
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
     * @return float|null
     */
    public function getPriority(): ?float
    {
        return $this->priority;
    }

    /**
     * @param float|null $priority
     * @return SchoolWeighting
     */
    public function setPriority(?float $priority): SchoolWeighting
    {
        $this->priority = $priority;
        return $this;
    }

    public function getIsActif(): ?bool
    {
        return $this->isActif;
    }

    public function setIsActif(?bool $isActif): SchoolWeighting
    {
        $this->isActif = $isActif;
        return $this;
    }

    public function getGroupEliminateAverage(): ?float
    {
        return $this->groupEliminateAverage;
    }

    public function setGroupEliminateAverage(?float $groupEliminateAverage): SchoolWeighting
    {
        $this->groupEliminateAverage = $groupEliminateAverage;
        return $this;
    }
}
