<?php

namespace App\Entity\School\Study\Program;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\School\PeriodType;
use App\Repository\School\Study\Program\ClassYearlyQuotaRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClassYearlyQuotaRepository::class)]
#[ORM\Table(name: 'school_class_yearly_quota')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/class-yearly-quota/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:ClassYearlyQuota:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/class-yearly-quota',
            normalizationContext: [
                'groups' => ['get:ClassYearlyQuota:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/class-yearly-quota',
            denormalizationContext: [
                'groups' => ['write:ClassYearlyQuota'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/class-yearly-quota/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:ClassYearlyQuota'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/class-yearly-quota/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/class-yearly-quota',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['schoolClass','year'],
    message: 'this year already exist',
    errorPath: 'schoolClass'
)]
class ClassYearlyQuota
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:ClassYearlyQuota:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?SchoolClass $schoolClass = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?int $p1cc = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?int $p1ex = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?int $p1rt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?int $p2cc = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?int $p2ex = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?int $p2rt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?int $p3cc = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?int $p3ex = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?int $p3rt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?PeriodType $periodType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?float $generalEliminateAverage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?float $eliminateMark = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?float $validationMark = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?bool $is_mark_for_all_sequence_required = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?bool $is_coefficient_of_null_mark_consider_in_the_average_calculation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?bool $is_validate_compensate_modulate = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?Year $year = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?int $minAnnualQuota = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?int $maxAnnualQuota = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?int $minMonthlyQuota = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?int $maxMonthlyQuota = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?int $minWeeklyQuota = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassYearlyQuota:collection', 'write:ClassYearlyQuota'])]
    private ?int $maxWeeklyQuota = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
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

    public function getSchoolClass(): ?SchoolClass
    {
        return $this->schoolClass;
    }

    public function setSchoolClass(?SchoolClass $schoolClass): static
    {
        $this->schoolClass = $schoolClass;

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

    public function getP1cc(): ?int
    {
        return $this->p1cc;
    }

    public function setP1cc(?int $p1cc): self
    {
        $this->p1cc = $p1cc;

        return $this;
    }

    public function getP1ex(): ?int
    {
        return $this->p1ex;
    }

    public function setP1ex(?int $p1ex): self
    {
        $this->p1ex = $p1ex;

        return $this;
    }

    public function getP1rt(): ?int
    {
        return $this->p1rt;
    }

    public function setP1rt(?int $p1rt): self
    {
        $this->p1rt = $p1rt;

        return $this;
    }

    public function getP2cc(): ?int
    {
        return $this->p2cc;
    }

    public function setP2cc(?int $p2cc): self
    {
        $this->p2cc = $p2cc;

        return $this;
    }

    public function getP2ex(): ?int
    {
        return $this->p2ex;
    }

    public function setP2ex(?int $p2ex): self
    {
        $this->p2ex = $p2ex;

        return $this;
    }

    public function getP2rt(): ?int
    {
        return $this->p2rt;
    }

    public function setP2rt(?int $p2rt): self
    {
        $this->p2rt = $p2rt;

        return $this;
    }

    public function getP3cc(): ?int
    {
        return $this->p3cc;
    }

    public function setP3cc(?int $p3cc): self
    {
        $this->p3cc = $p3cc;

        return $this;
    }

    public function getP3ex(): ?int
    {
        return $this->p3ex;
    }

    public function setP3ex(?int $p3ex): self
    {
        $this->p3ex = $p3ex;

        return $this;
    }

    public function getP3rt(): ?int
    {
        return $this->p3rt;
    }

    public function setP3rt(?int $p3rt): self
    {
        $this->p3rt = $p3rt;

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

    public function isIsMarkForAllSequenceRequired(): ?bool
    {
        return $this->is_mark_for_all_sequence_required;
    }

    public function setIsMarkForAllSequenceRequired(?bool $is_mark_for_all_sequence_required): self
    {
        $this->is_mark_for_all_sequence_required = $is_mark_for_all_sequence_required;

        return $this;
    }

    public function isIsCoefficientOfNullMarkConsiderInTheAverageCalculation(): ?bool
    {
        return $this->is_coefficient_of_null_mark_consider_in_the_average_calculation;
    }

    public function setIsCoefficientOfNullMarkConsiderInTheAverageCalculation(?bool $is_coefficient_of_null_mark_consider_in_the_average_calculation): self
    {
        $this->is_coefficient_of_null_mark_consider_in_the_average_calculation = $is_coefficient_of_null_mark_consider_in_the_average_calculation;

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

    public function isIsValidateCompensateModulate(): ?bool
    {
        return $this->is_validate_compensate_modulate;
    }

    public function setIsValidateCompensateModulate(?bool $is_validate_compensate_modulate): static
    {
        $this->is_validate_compensate_modulate = $is_validate_compensate_modulate;

        return $this;
    }

    public function getMinAnnualQuota(): ?int
    {
        return $this->minAnnualQuota;
    }

    public function setMinAnnualQuota(?int $minAnnualQuota): self
    {
        $this->minAnnualQuota = $minAnnualQuota;

        return $this;
    }

    public function getMaxAnnualQuota(): ?int
    {
        return $this->maxAnnualQuota;
    }

    public function setMaxAnnualQuota(?int $maxAnnualQuota): self
    {
        $this->maxAnnualQuota = $maxAnnualQuota;

        return $this;
    }

    public function getMinMonthlyQuota(): ?int
    {
        return $this->minMonthlyQuota;
    }

    public function setMinMonthlyQuota(?int $minMonthlyQuota): self
    {
        $this->minMonthlyQuota = $minMonthlyQuota;

        return $this;
    }

    public function getMaxMonthlyQuota(): ?int
    {
        return $this->maxMonthlyQuota;
    }

    public function setMaxMonthlyQuota(?int $maxMonthlyQuota): self
    {
        $this->maxMonthlyQuota = $maxMonthlyQuota;

        return $this;
    }

    public function getMinWeeklyQuota(): ?int
    {
        return $this->minWeeklyQuota;
    }

    public function setMinWeeklyQuota(?int $minWeeklyQuota): self
    {
        $this->minWeeklyQuota = $minWeeklyQuota;

        return $this;
    }

    public function getMaxWeeklyQuota(): ?int
    {
        return $this->maxWeeklyQuota;
    }

    public function setMaxWeeklyQuota(?int $maxWeeklyQuota): self
    {
        $this->maxWeeklyQuota = $maxWeeklyQuota;

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
