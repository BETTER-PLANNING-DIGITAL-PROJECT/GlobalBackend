<?php

namespace App\Entity\School\Exam\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\Speciality;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\School\PeriodType;
use App\Repository\School\Exam\Configuration\SpecialityWeightingRepository;
use App\State\Current\InstitutionProcessor;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SpecialityWeightingRepository::class)]
#[ORM\Table(name: 'school_speciality_weighting')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/speciality/weighting/get',
            normalizationContext: [
                'groups' => ['get:SpecialityWeighting:collection'],
            ],

        ),
        new Post(
            uriTemplate: '/speciality/weighting/create',
            denormalizationContext: [
                'groups' => ['write:SpecialityWeighting'],
            ],
            processor: InstitutionProcessor::class,
        ),
        new Put(
            uriTemplate: '/speciality/weighting/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SpecialityWeighting'],
            ],
        ),
    ]
)]
class SpecialityWeighting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:SpecialityWeighting:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:SpecialityWeighting:collection'])]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:SpecialityWeighting:collection'])]
    private ?Speciality $speciality = null;

    #[ORM\Column(type: Types::TEXT,nullable: true)]
    #[Groups(['get:SpecialityWeighting:collection', 'write:SpecialityWeighting'])]
    private ?string $sequencePonderations = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SpecialityWeighting:collection', 'write:SpecialityWeighting'])]
    private ?PeriodType $periodType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecialityWeighting:collection', 'write:SpecialityWeighting'])]
    private ?int $numberOfDivision = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecialityWeighting:collection', 'write:SpecialityWeighting'])]
    private ?float $generalEliminateAverage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecialityWeighting:collection'])]
    private ?float $groupEliminateAverage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecialityWeighting:collection', 'write:SpecialityWeighting'])]
    private ?float $eliminateMark = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecialityWeighting:collection', 'write:SpecialityWeighting'])]
    private ?float $validationMark = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecialityWeighting:collection', 'write:SpecialityWeighting'])]
    private ?float $entryBase = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecialityWeighting:collection', 'write:SpecialityWeighting'])]
    private ?float $priority = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecialityWeighting:collection', 'write:SpecialityWeighting'])]
    private ?bool $isMarkForAllSequenceRequired = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecialityWeighting:collection', 'write:SpecialityWeighting'])]
    private ?bool $isActif = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecialityWeighting:collection', 'write:SpecialityWeighting'])]
    private ?bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SpecialityWeighting:collection', 'write:SpecialityWeighting'])]
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
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    public function __construct(){
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();

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

    public function getSpeciality(): ?Speciality
    {
        return $this->speciality;
    }

    public function setSpeciality(?Speciality $speciality): static
    {
        $this->speciality = $speciality;

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

    /**
     * @param float|null $validationMark
     * @return SpecialityWeighting
     */
    public function setValidationMark(?float $validationMark): SpecialityWeighting
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

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
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

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): SpecialityWeighting
    {
        $this->year = $year;
        return $this;
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
     * @return SpecialityWeighting
     */
    public function setSequencePonderations(?string $sequencePonderations): SpecialityWeighting
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
     * @return SpecialityWeighting
     */
    public function setNumberOfDivision(?int $numberOfDivision): SpecialityWeighting
    {
        $this->numberOfDivision = $numberOfDivision;
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
     * @return SpecialityWeighting
     */
    public function setIsMarkForAllSequenceRequired(?bool $isMarkForAllSequenceRequired): SpecialityWeighting
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
     * @return SpecialityWeighting
     */
    public function setIsCoefficientOfNullMarkConsiderInTheAverageCalculation(?bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation): SpecialityWeighting
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
     * @return SpecialityWeighting
     */
    public function setIsValidateCompensateModulate(?bool $isValidateCompensateModulate): SpecialityWeighting
    {
        $this->isValidateCompensateModulate = $isValidateCompensateModulate;
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
     * @return SpecialityWeighting
     */
    public function setEntryBase(?float $entryBase): SpecialityWeighting
    {
        $this->entryBase = $entryBase;
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
     * @return SpecialityWeighting
     */
    public function setPriority(?float $priority): SpecialityWeighting
    {
        $this->priority = $priority;
        return $this;
    }

    public function getIsActif(): ?bool
    {
        return $this->isActif;
    }

    public function setIsActif(?bool $isActif): SpecialityWeighting
    {
        $this->isActif = $isActif;
        return $this;
    }

    public function getGroupEliminateAverage(): ?float
    {
        return $this->groupEliminateAverage;
    }

    public function setGroupEliminateAverage(?float $groupEliminateAverage): SpecialityWeighting
    {
        $this->groupEliminateAverage = $groupEliminateAverage;
        return $this;
    }
}
