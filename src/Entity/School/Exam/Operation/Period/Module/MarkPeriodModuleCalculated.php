<?php

namespace App\Entity\School\Exam\Operation\Period\Module;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\MarkGrade;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Operation\Period\Module\MarkAnnualModuleCalculatedRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MarkAnnualModuleCalculatedRepository::class)]
#[ORM\Table(name: 'school_mark_period_module_calculated')]
class MarkPeriodModuleCalculated
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
    #[Assert\NotNull(message: 'module NotNull')]
    private ?Module $module = null;

    #[ORM\Column(nullable: true)]
    private ?float $mark = null;

    #[ORM\Column(nullable: true)]
    private ?float $total = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isEliminated = null;

    #[ORM\Column(nullable: true)]
    private ?float $totalCredit = null;

    #[ORM\Column]
    private ?float $totalCreditValidated = null;

    #[ORM\Column]
    private ?float $totalCreditConsidered = null;

    #[ORM\ManyToOne]
    private ?MarkGrade $grade = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:MarkSequenceModuleCalculated:collection'])]
    private ?float $averageGpa = null; // GPA moyen des matieres du groupe

    #[ORM\Column(nullable: true)]
    private ?bool $isValidated = null;

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

    public function setInstitution(?Institution $institution): MarkPeriodModuleCalculated
    {
        $this->institution = $institution;
        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): MarkPeriodModuleCalculated
    {
        $this->year = $year;
        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): MarkPeriodModuleCalculated
    {
        $this->school = $school;
        return $this;
    }

    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function setClass(?SchoolClass $class): MarkPeriodModuleCalculated
    {
        $this->class = $class;
        return $this;
    }

    public function getStudent(): ?StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(?StudentRegistration $student): MarkPeriodModuleCalculated
    {
        $this->student = $student;
        return $this;
    }

    public function getEvaluationPeriod(): ?EvaluationPeriod
    {
        return $this->evaluationPeriod;
    }

    public function setEvaluationPeriod(?EvaluationPeriod $evaluationPeriod): MarkPeriodModuleCalculated
    {
        $this->evaluationPeriod = $evaluationPeriod;
        return $this;
    }

    public function getSequence(): ?Sequence
    {
        return $this->sequence;
    }

    public function setSequence(?Sequence $sequence): MarkPeriodModuleCalculated
    {
        $this->sequence = $sequence;
        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): MarkPeriodModuleCalculated
    {
        $this->module = $module;
        return $this;
    }

    public function getMark(): ?float
    {
        return $this->mark;
    }

    public function setMark(?float $mark): MarkPeriodModuleCalculated
    {
        $this->mark = $mark;
        return $this;
    }

    public function getGrade(): ?MarkGrade
    {
        return $this->grade;
    }

    public function setGrade(?MarkGrade $grade): MarkPeriodModuleCalculated
    {
        $this->grade = $grade;
        return $this;
    }

    public function getAverageGpa(): ?float
    {
        return $this->averageGpa;
    }

    public function setAverageGpa(?float $averageGpa): MarkPeriodModuleCalculated
    {
        $this->averageGpa = $averageGpa;
        return $this;
    }

    public function getIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(?bool $is_enable): MarkPeriodModuleCalculated
    {
        $this->is_enable = $is_enable;
        return $this;
    }

    public function getIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(?bool $is_archive): MarkPeriodModuleCalculated
    {
        $this->is_archive = $is_archive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): MarkPeriodModuleCalculated
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): MarkPeriodModuleCalculated
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): MarkPeriodModuleCalculated
    {
        $this->user = $user;
        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): MarkPeriodModuleCalculated
    {
        $this->isValidated = $isValidated;
        return $this;
    }

    public function getTotalCredit(): ?float
    {
        return $this->totalCredit;
    }

    public function setTotalCredit(?float $totalCredit): MarkPeriodModuleCalculated
    {
        $this->totalCredit = $totalCredit;
        return $this;
    }

    public function getTotalCreditValidated(): ?float
    {
        return $this->totalCreditValidated;
    }

    public function setTotalCreditValidated(?float $totalCreditValidated): MarkPeriodModuleCalculated
    {
        $this->totalCreditValidated = $totalCreditValidated;
        return $this;
    }

    public function getIsEliminated(): ?bool
    {
        return $this->isEliminated;
    }

    public function setIsEliminated(?bool $isEliminated): MarkPeriodModuleCalculated
    {
        $this->isEliminated = $isEliminated;
        return $this;
    }

    public function getTotalCreditConsidered(): ?float
    {
        return $this->totalCreditConsidered;
    }

    public function setTotalCreditConsidered(?float $totalCreditConsidered): MarkPeriodModuleCalculated
    {
        $this->totalCreditConsidered = $totalCreditConsidered;
        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): MarkPeriodModuleCalculated
    {
        $this->total = $total;
        return $this;
    }
}
