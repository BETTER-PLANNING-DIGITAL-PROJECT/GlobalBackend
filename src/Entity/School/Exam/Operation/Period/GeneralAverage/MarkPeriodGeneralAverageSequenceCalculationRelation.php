<?php

namespace App\Entity\School\Exam\Operation\Period\GeneralAverage;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\MarkGrade;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Period\Course\MarkAnnualCourseCalculated;
use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCourseCalculationRelationRepository;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageSequenceCalculationRelationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MarkPeriodGeneralAverageSequenceCalculationRelationRepository::class)]
#[ORM\Table(name: 'school_mark_period_general_average_sequence_calculation_relation')]
class MarkPeriodGeneralAverageSequenceCalculationRelation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'institution NotNull')]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'school_year NotNull')]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'school NotNull')]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'school_class NotNull')]
    private ?SchoolClass $class = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'student NotNull')]
    private ?StudentRegistration $student = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'evaluationPeriod NotNull')]
    private ?EvaluationPeriod $evaluationPeriod = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'sequence NotNull')]
    private ?Sequence $sequence = null;

    #[ORM\ManyToOne]
    private ?MarkSequenceGeneralAverageCalculated $sequenceGeneralAverageCalculated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'periodGeneralAverageCalculated NotNull')]
    private ?MarkPeriodGeneralAverageCalculated $periodGeneralAverageCalculated = null;

    #[ORM\Column(nullable: true)]
    private ?float $averageCalculated = null;

    #[ORM\Column(nullable: true)]
    private ?float $averageUsed = null;

    #[ORM\Column(nullable: true)]
    private ?float $sequenceGeneralEliminateAverage = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isEliminated = null;

    #[ORM\ManyToOne]
    private ?MarkGrade $gradeUsed = null;

    #[ORM\Column(nullable: true)]
    private ?float $averageGpaCalculated = null;

    #[ORM\Column(nullable: true)]
    private ?float $gpaUsed = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isValidated = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isClassed = null;

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

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = false;
        $this->is_archive = false;
        $this->isValidated = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->institution = $institution;
        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->year = $year;
        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->school = $school;
        return $this;
    }

    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function setClass(?SchoolClass $class): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->class = $class;
        return $this;
    }

    public function getStudent(): ?StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(?StudentRegistration $student): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->student = $student;
        return $this;
    }

    public function getEvaluationPeriod(): ?EvaluationPeriod
    {
        return $this->evaluationPeriod;
    }

    public function setEvaluationPeriod(?EvaluationPeriod $evaluationPeriod): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->evaluationPeriod = $evaluationPeriod;
        return $this;
    }

    public function getSequence(): ?Sequence
    {
        return $this->sequence;
    }

    public function setSequence(?Sequence $sequence): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->sequence = $sequence;
        return $this;
    }

    public function getSequenceGeneralAverageCalculated(): ?MarkSequenceGeneralAverageCalculated
    {
        return $this->sequenceGeneralAverageCalculated;
    }

    public function setSequenceGeneralAverageCalculated(?MarkSequenceGeneralAverageCalculated $sequenceGeneralAverageCalculated): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->sequenceGeneralAverageCalculated = $sequenceGeneralAverageCalculated;
        return $this;
    }

    public function getPeriodGeneralAverageCalculated(): ?MarkPeriodGeneralAverageCalculated
    {
        return $this->periodGeneralAverageCalculated;
    }

    public function setPeriodGeneralAverageCalculated(?MarkPeriodGeneralAverageCalculated $periodGeneralAverageCalculated): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->periodGeneralAverageCalculated = $periodGeneralAverageCalculated;
        return $this;
    }

    public function getAverageCalculated(): ?float
    {
        return $this->averageCalculated;
    }

    public function setAverageCalculated(?float $averageCalculated): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->averageCalculated = $averageCalculated;
        return $this;
    }

    public function getAverageUsed(): ?float
    {
        return $this->averageUsed;
    }

    public function setAverageUsed(?float $averageUsed): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->averageUsed = $averageUsed;
        return $this;
    }

    public function getIsEliminated(): ?bool
    {
        return $this->isEliminated;
    }

    public function setIsEliminated(?bool $isEliminated): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->isEliminated = $isEliminated;
        return $this;
    }

    public function getGradeUsed(): ?MarkGrade
    {
        return $this->gradeUsed;
    }

    public function setGradeUsed(?MarkGrade $gradeUsed): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->gradeUsed = $gradeUsed;
        return $this;
    }

    public function getAverageGpaCalculated(): ?float
    {
        return $this->averageGpaCalculated;
    }

    public function setAverageGpaCalculated(?float $averageGpaCalculated): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->averageGpaCalculated = $averageGpaCalculated;
        return $this;
    }

    public function getGpaUsed(): ?float
    {
        return $this->gpaUsed;
    }

    public function setGpaUsed(?float $gpaUsed): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->gpaUsed = $gpaUsed;
        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->isValidated = $isValidated;
        return $this;
    }

    public function getIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(?bool $is_enable): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->is_enable = $is_enable;
        return $this;
    }

    public function getIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(?bool $is_archive): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->is_archive = $is_archive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->user = $user;
        return $this;
    }

    public function getIsClassed(): ?bool
    {
        return $this->isClassed;
    }

    public function setIsClassed(?bool $isClassed): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->isClassed = $isClassed;
        return $this;
    }

    public function getSequenceGeneralEliminateAverage(): ?float
    {
        return $this->sequenceGeneralEliminateAverage;
    }

    public function setSequenceGeneralEliminateAverage(?float $sequenceGeneralEliminateAverage): MarkPeriodGeneralAverageSequenceCalculationRelation
    {
        $this->sequenceGeneralEliminateAverage = $sequenceGeneralEliminateAverage;
        return $this;
    }
}
