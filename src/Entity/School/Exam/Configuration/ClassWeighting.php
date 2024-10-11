<?php

namespace App\Entity\School\Exam\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\School\PeriodType;
use App\Repository\School\Exam\Configuration\ClassWeightingRepository;
use App\State\Current\InstitutionProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClassWeightingRepository::class)]
#[ORM\Table(name: 'school_class_weighting')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/class/weighting/get',
            normalizationContext: [
                'groups' => ['get:ClassWeighting:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/class/weighting/create',
            denormalizationContext: [
                'groups' => ['write:ClassWeighting'],
            ],
            processor: InstitutionProcessor::class,
        ),
        new Put(
            uriTemplate: '/class/weighting/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:ClassWeighting'],
            ],
        ),
    ]
)]
class ClassWeighting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:ClassWeighting:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:ClassWeighting:collection'])]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:ClassWeighting:collection'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:ClassWeighting:collection'])]
    private ?SchoolClass $class = null;

    #[ORM\Column(type: Types::TEXT,nullable: true)]
    #[Groups(['get:ClassWeighting:collection', 'write:ClassWeighting'])]
    private ?string $sequencePonderations = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ClassWeighting:collection', 'write:ClassWeighting'])]
    private ?PeriodType $periodType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassWeighting:collection', 'write:ClassWeighting'])]
    private ?int $numberOfDivision = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassWeighting:collection', 'write:ClassWeighting'])]
    private ?float $groupEliminateAverage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassWeighting:collection'])]
    private ?float $generalEliminateAverage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassWeighting:collection', 'write:ClassWeighting'])]
    private ?float $eliminateMark = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassWeighting:collection', 'write:ClassWeighting'])]
    private ?float $validationMark = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassWeighting:collection', 'write:ClassWeighting'])]
    private ?float $entryBase = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassWeighting:collection', 'write:ClassWeighting'])]
    private ?float $priority = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassWeighting:collection', 'write:ClassWeighting'])]
    private ?bool $isMarkForAllSequenceRequired = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassWeighting:collection', 'write:ClassWeighting'])]
    private ?bool $isActif = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassWeighting:collection', 'write:ClassWeighting'])]
    private ?bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassWeighting:collection', 'write:ClassWeighting'])]
    private ?bool $isValidateCompensateModulate = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?bool $is_enable = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?bool $is_archive = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $updatedAt = null;

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

    public function getGroupEliminateAverage(): ?float
    {
        return $this->groupEliminateAverage;
    }

    public function setGroupEliminateAverage(?float $groupEliminateAverage): self
    {
        $this->groupEliminateAverage = $groupEliminateAverage;

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

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): static
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
     * @return SchoolClass|null
     */
    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    /**
     * @param SchoolClass|null $class
     * @return ClassWeighting
     */
    public function setClass(?SchoolClass $class): ClassWeighting
    {
        $this->class = $class;
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
     * @return ClassWeighting
     */
    public function setSequencePonderations(?string $sequencePonderations): ClassWeighting
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
     * @return ClassWeighting
     */
    public function setNumberOfDivision(?int $numberOfDivision): ClassWeighting
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
     * @return ClassWeighting
     */
    public function setEntryBase(?float $entryBase): ClassWeighting
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
     * @return ClassWeighting
     */
    public function setIsMarkForAllSequenceRequired(?bool $isMarkForAllSequenceRequired): ClassWeighting
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
     * @return ClassWeighting
     */
    public function setIsCoefficientOfNullMarkConsiderInTheAverageCalculation(?bool $isCoefficientOfNullMarkConsiderInTheAverageCalculation): ClassWeighting
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
     * @return ClassWeighting
     */
    public function setIsValidateCompensateModulate(?bool $isValidateCompensateModulate): ClassWeighting
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
     * @return ClassWeighting
     */
    public function setPriority(?float $priority): ClassWeighting
    {
        $this->priority = $priority;
        return $this;
    }

    public function getIsActif(): ?bool
    {
        return $this->isActif;
    }

    public function setIsActif(?bool $isActif): ClassWeighting
    {
        $this->isActif = $isActif;
        return $this;
    }

    public function getGeneralEliminateAverage(): ?float
    {
        return $this->generalEliminateAverage;
    }

    public function setGeneralEliminateAverage(?float $generalEliminateAverage): ClassWeighting
    {
        $this->generalEliminateAverage = $generalEliminateAverage;
        return $this;
    }
}
