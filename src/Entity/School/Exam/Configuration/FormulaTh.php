<?php

namespace App\Entity\School\Exam\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\FormulaThRepository;
use App\State\Current\InstitutionProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FormulaThRepository::class)]
#[ORM\Table(name: 'school_formula_th')]
#[ApiResource(
    operations:[
        /*new Get(
            uriTemplate: '/formula-th/{id}/get',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:FormulaTh:item'],
            ],
        ),*/
        new GetCollection(
            uriTemplate: '/formula-th/get',
            normalizationContext: [
                'groups' => ['get:FormulaTh:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/formula-th/create',
            denormalizationContext: [
                'groups' => ['write:FormulaTh'],
            ],
            processor: InstitutionProcessor::class,

        ),
        new Put(
            uriTemplate: '/formula-th/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:FormulaTh'],
            ],
        ),
        new Delete(
            uriTemplate: '/formula-th/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['year'],
    message: 'This formula th already exist.',
)]
class FormulaTh
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:FormulaTh:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[Groups(['get:FormulaTh:collection'])]
    private ?Year $year = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?string $halfYearAverageFormula = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?string $finalAverageFormula = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?string $finalMarkFormula = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?int $warningConductAbsenceHours = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?int $warningConductExclusionDays = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?int $blameConductAbsenceHours = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?int $blameConductExclusionDays = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?float $blameWorkAverage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?float $warningWorkAverage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?float $grantThCompositionAverage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?float $grantThAnnualAverage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?float $grantEncouragementAverage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?float $grantCongratulationAverage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?int $refuseThAbsenceHours = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?int $refuseThExclusionDays = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?int $refuseThSetHours = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?int $refuseThNumberOfBlame = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?float $percentageSubjectNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?string $andOr = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaTh:collection', 'write:FormulaTh'])]
    private ?float $percentageTotalCoefficient = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?bool $is_archive = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = false;
        $this->is_archive = false;

//        $this->isMarkActif = true;
//        $this->isCourseActif = true;
        $this->percentageTotalCoefficient = 0;
        $this->percentageSubjectNumber = 0;
        $this->andOr = 'o';
        $this->halfYearAverageFormula = '1';
        $this->finalAverageFormula = '1';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): self
    {
        $this->institution = $institution;

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
     * @return string|null
     */
    public function getHalfYearAverageFormula(): ?string
    {
        return $this->halfYearAverageFormula;
    }

    /**
     * @param string|null $halfYearAverageFormula
     * @return FormulaTh
     */
    public function setHalfYearAverageFormula(?string $halfYearAverageFormula): FormulaTh
    {
        $this->halfYearAverageFormula = $halfYearAverageFormula;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFinalAverageFormula(): ?string
    {
        return $this->finalAverageFormula;
    }

    /**
     * @param string|null $finalAverageFormula
     * @return FormulaTh
     */
    public function setFinalAverageFormula(?string $finalAverageFormula): FormulaTh
    {
        $this->finalAverageFormula = $finalAverageFormula;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getWarningConductAbsenceHours(): ?int
    {
        return $this->warningConductAbsenceHours;
    }

    /**
     * @param int|null $warningConductAbsenceHours
     * @return FormulaTh
     */
    public function setWarningConductAbsenceHours(?int $warningConductAbsenceHours): FormulaTh
    {
        $this->warningConductAbsenceHours = $warningConductAbsenceHours;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getWarningConductExclusionDays(): ?int
    {
        return $this->warningConductExclusionDays;
    }

    /**
     * @param int|null $warningConductExclusionDays
     * @return FormulaTh
     */
    public function setWarningConductExclusionDays(?int $warningConductExclusionDays): FormulaTh
    {
        $this->warningConductExclusionDays = $warningConductExclusionDays;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getBlameConductAbsenceHours(): ?int
    {
        return $this->blameConductAbsenceHours;
    }

    /**
     * @param int|null $blameConductAbsenceHours
     * @return FormulaTh
     */
    public function setBlameConductAbsenceHours(?int $blameConductAbsenceHours): FormulaTh
    {
        $this->blameConductAbsenceHours = $blameConductAbsenceHours;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getBlameConductExclusionDays(): ?int
    {
        return $this->blameConductExclusionDays;
    }

    /**
     * @param int|null $blameConductExclusionDays
     * @return FormulaTh
     */
    public function setBlameConductExclusionDays(?int $blameConductExclusionDays): FormulaTh
    {
        $this->blameConductExclusionDays = $blameConductExclusionDays;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getBlameWorkAverage(): ?float
    {
        return $this->blameWorkAverage;
    }

    /**
     * @param float|null $blameWorkAverage
     * @return FormulaTh
     */
    public function setBlameWorkAverage(?float $blameWorkAverage): FormulaTh
    {
        $this->blameWorkAverage = $blameWorkAverage;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getWarningWorkAverage(): ?float
    {
        return $this->warningWorkAverage;
    }

    /**
     * @param float|null $warningWorkAverage
     * @return FormulaTh
     */
    public function setWarningWorkAverage(?float $warningWorkAverage): FormulaTh
    {
        $this->warningWorkAverage = $warningWorkAverage;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getGrantThCompositionAverage(): ?float
    {
        return $this->grantThCompositionAverage;
    }

    /**
     * @param float|null $grantThCompositionAverage
     * @return FormulaTh
     */
    public function setGrantThCompositionAverage(?float $grantThCompositionAverage): FormulaTh
    {
        $this->grantThCompositionAverage = $grantThCompositionAverage;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getGrantThAnnualAverage(): ?float
    {
        return $this->grantThAnnualAverage;
    }

    /**
     * @param float|null $grantThAnnualAverage
     * @return FormulaTh
     */
    public function setGrantThAnnualAverage(?float $grantThAnnualAverage): FormulaTh
    {
        $this->grantThAnnualAverage = $grantThAnnualAverage;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getGrantEncouragementAverage(): ?float
    {
        return $this->grantEncouragementAverage;
    }

    /**
     * @param float|null $grantEncouragementAverage
     * @return FormulaTh
     */
    public function setGrantEncouragementAverage(?float $grantEncouragementAverage): FormulaTh
    {
        $this->grantEncouragementAverage = $grantEncouragementAverage;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getGrantCongratulationAverage(): ?float
    {
        return $this->grantCongratulationAverage;
    }

    /**
     * @param float|null $grantCongratulationAverage
     * @return FormulaTh
     */
    public function setGrantCongratulationAverage(?float $grantCongratulationAverage): FormulaTh
    {
        $this->grantCongratulationAverage = $grantCongratulationAverage;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRefuseThAbsenceHours(): ?int
    {
        return $this->refuseThAbsenceHours;
    }

    /**
     * @param int|null $refuseThAbsenceHours
     * @return FormulaTh
     */
    public function setRefuseThAbsenceHours(?int $refuseThAbsenceHours): FormulaTh
    {
        $this->refuseThAbsenceHours = $refuseThAbsenceHours;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRefuseThExclusionDays(): ?int
    {
        return $this->refuseThExclusionDays;
    }

    /**
     * @param int|null $refuseThExclusionDays
     * @return FormulaTh
     */
    public function setRefuseThExclusionDays(?int $refuseThExclusionDays): FormulaTh
    {
        $this->refuseThExclusionDays = $refuseThExclusionDays;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRefuseThSetHours(): ?int
    {
        return $this->refuseThSetHours;
    }

    /**
     * @param int|null $refuseThSetHours
     * @return FormulaTh
     */
    public function setRefuseThSetHours(?int $refuseThSetHours): FormulaTh
    {
        $this->refuseThSetHours = $refuseThSetHours;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRefuseThNumberOfBlame(): ?int
    {
        return $this->refuseThNumberOfBlame;
    }

    /**
     * @param int|null $refuseThNumberOfBlame
     * @return FormulaTh
     */
    public function setRefuseThNumberOfBlame(?int $refuseThNumberOfBlame): FormulaTh
    {
        $this->refuseThNumberOfBlame = $refuseThNumberOfBlame;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPercentageSubjectNumber(): ?float
    {
        return $this->percentageSubjectNumber;
    }

    /**
     * @param float|null $percentageSubjectNumber
     * @return FormulaTh
     */
    public function setPercentageSubjectNumber(?float $percentageSubjectNumber): FormulaTh
    {
        $this->percentageSubjectNumber = $percentageSubjectNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAndOr(): ?string
    {
        return $this->andOr;
    }

    /**
     * @param string|null $andOr
     * @return FormulaTh
     */
    public function setAndOr(?string $andOr): FormulaTh
    {
        $this->andOr = $andOr;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPercentageTotalCoefficient(): ?float
    {
        return $this->percentageTotalCoefficient;
    }

    /**
     * @param float|null $percentageTotalCoefficient
     * @return FormulaTh
     */
    public function setPercentageTotalCoefficient(?float $percentageTotalCoefficient): FormulaTh
    {
        $this->percentageTotalCoefficient = $percentageTotalCoefficient;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    /**
     * @param bool|null $is_enable
     * @return FormulaTh
     */
    public function setIsEnable(?bool $is_enable): FormulaTh
    {
        $this->is_enable = $is_enable;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    /**
     * @param bool|null $is_archive
     * @return FormulaTh
     */
    public function setIsArchive(?bool $is_archive): FormulaTh
    {
        $this->is_archive = $is_archive;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeImmutable|null $createdAt
     * @return FormulaTh
     */
    public function setCreatedAt(?\DateTimeImmutable $createdAt): FormulaTh
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeImmutable|null $updatedAt
     * @return FormulaTh
     */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): FormulaTh
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return FormulaTh
     */
    public function setUser(?User $user): FormulaTh
    {
        $this->user = $user;
        return $this;
    }

    public function getFinalMarkFormula(): ?string
    {
        return $this->finalMarkFormula;
    }

    public function setFinalMarkFormula(?string $finalMarkFormula): FormulaTh
    {
        $this->finalMarkFormula = $finalMarkFormula;
        return $this;
    }
}
