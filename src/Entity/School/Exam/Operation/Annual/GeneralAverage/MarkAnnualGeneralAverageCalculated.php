<?php

namespace App\Entity\School\Exam\Operation\Annual\GeneralAverage;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\FormulaCondition;
use App\Entity\School\Exam\Configuration\MarkGrade;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculatedRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MarkPeriodGeneralAverageCalculatedRepository::class)]
#[ORM\Table(name: 'school_mark_annual_general_average_calculated')]
class MarkAnnualGeneralAverageCalculated
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

    #[ORM\Column(nullable: true)]
    private ?float $average = null;

    #[ORM\Column(nullable: true)]
    private ?string $failureReason = null;

    #[ORM\Column(nullable: true)]
    private ?float $total = null;

    #[ORM\Column(nullable: true)]
    private ?float $totalCredit = null;

    #[ORM\Column]
    private ?float $totalCreditValidated = null;

    #[ORM\Column]
    private ?float $totalCreditConsidered = null;

    #[ORM\ManyToOne]
    private ?MarkGrade $grade = null;

    #[ORM\ManyToOne]
    private ?FormulaCondition $promotionFormulaCondition = null;

    #[ORM\Column(nullable: true)]
    private ?float $averageGpa = null; // GPA moyen des matieres de la sequence

    #[ORM\Column(name: '`rank`',nullable: true)]
    private ?int $rank = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalStudentsClassed = null;

    #[ORM\Column]
    private ?int $numberOfAttendedCourses = null;

    #[ORM\Column]
    private ?int $numberOfComposedCourses = null;

    #[ORM\Column]
    private ?float $totalOfCreditsAttended = null;

    #[ORM\Column]
    private ?float $totalOfCreditsComposed = null;

    #[ORM\Column]
    private ?float $percentageSubjectNumber = null;

    #[ORM\Column]
    private ?float $percentageTotalCoefficient = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isValidated = null;

    #[ORM\Column(nullable: true)]
    private ?bool $blameWork = null;

    #[ORM\Column(nullable: true)]
    private ?bool $warningWork = null;

    #[ORM\Column(nullable: true)]
    private ?bool $grantThAnnual = null;

    #[ORM\Column(nullable: true)]
    private ?bool $grantEncouragement = null;

    #[ORM\Column(nullable: true)]
    private ?bool $grantCongratulation = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isClassed = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isEliminated = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isPromoted = null;

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
        $this->isClassed = true;
        $this->isEliminated = false;
        $this->isPromoted = false;

        $this->blameWork = false;
        $this->warningWork = false;
        $this->grantEncouragement = false;
        $this->grantThAnnual = false;
        $this->grantCongratulation = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): MarkAnnualGeneralAverageCalculated
    {
        $this->institution = $institution;
        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): MarkAnnualGeneralAverageCalculated
    {
        $this->year = $year;
        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): MarkAnnualGeneralAverageCalculated
    {
        $this->school = $school;
        return $this;
    }

    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function setClass(?SchoolClass $class): MarkAnnualGeneralAverageCalculated
    {
        $this->class = $class;
        return $this;
    }

    public function getStudent(): ?StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(?StudentRegistration $student): MarkAnnualGeneralAverageCalculated
    {
        $this->student = $student;
        return $this;
    }

    public function getEvaluationPeriod(): ?EvaluationPeriod
    {
        return $this->evaluationPeriod;
    }

    public function setEvaluationPeriod(?EvaluationPeriod $evaluationPeriod): MarkAnnualGeneralAverageCalculated
    {
        $this->evaluationPeriod = $evaluationPeriod;
        return $this;
    }

    public function getAverage(): ?float
    {
        return $this->average;
    }

    public function setAverage(?float $average): MarkAnnualGeneralAverageCalculated
    {
        $this->average = $average;
        return $this;
    }

    public function getTotalCredit(): ?float
    {
        return $this->totalCredit;
    }

    public function setTotalCredit(?float $totalCredit): MarkAnnualGeneralAverageCalculated
    {
        $this->totalCredit = $totalCredit;
        return $this;
    }

    public function getTotalCreditValidated(): ?float
    {
        return $this->totalCreditValidated;
    }

    public function setTotalCreditValidated(?float $totalCreditValidated): MarkAnnualGeneralAverageCalculated
    {
        $this->totalCreditValidated = $totalCreditValidated;
        return $this;
    }

    public function getGrade(): ?MarkGrade
    {
        return $this->grade;
    }

    public function setGrade(?MarkGrade $grade): MarkAnnualGeneralAverageCalculated
    {
        $this->grade = $grade;
        return $this;
    }

    public function getAverageGpa(): ?float
    {
        return $this->averageGpa;
    }

    public function setAverageGpa(?float $averageGpa): MarkAnnualGeneralAverageCalculated
    {
        $this->averageGpa = $averageGpa;
        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): MarkAnnualGeneralAverageCalculated
    {
        $this->isValidated = $isValidated;
        return $this;
    }

    public function getIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(?bool $is_enable): MarkAnnualGeneralAverageCalculated
    {
        $this->is_enable = $is_enable;
        return $this;
    }

    public function getIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(?bool $is_archive): MarkAnnualGeneralAverageCalculated
    {
        $this->is_archive = $is_archive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): MarkAnnualGeneralAverageCalculated
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): MarkAnnualGeneralAverageCalculated
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): MarkAnnualGeneralAverageCalculated
    {
        $this->user = $user;
        return $this;
    }

    public function getIsClassed(): ?bool
    {
        return $this->isClassed;
    }

    public function setIsClassed(?bool $isClassed): MarkAnnualGeneralAverageCalculated
    {
        $this->isClassed = $isClassed;
        return $this;
    }

    public function getIsEliminated(): ?bool
    {
        return $this->isEliminated;
    }

    public function setIsEliminated(?bool $isEliminated): MarkAnnualGeneralAverageCalculated
    {
        $this->isEliminated = $isEliminated;
        return $this;
    }

    public function getNumberOfAttendedCourses(): ?int
    {
        return $this->numberOfAttendedCourses;
    }

    public function setNumberOfAttendedCourses(?int $numberOfAttendedCourses): MarkAnnualGeneralAverageCalculated
    {
        $this->numberOfAttendedCourses = $numberOfAttendedCourses;
        return $this;
    }

    public function getNumberOfComposedCourses(): ?int
    {
        return $this->numberOfComposedCourses;
    }

    public function setNumberOfComposedCourses(?int $numberOfComposedCourses): MarkAnnualGeneralAverageCalculated
    {
        $this->numberOfComposedCourses = $numberOfComposedCourses;
        return $this;
    }

    public function getTotalOfCreditsAttended(): ?float
    {
        return $this->totalOfCreditsAttended;
    }

    public function setTotalOfCreditsAttended(?float $totalOfCreditsAttended): MarkAnnualGeneralAverageCalculated
    {
        $this->totalOfCreditsAttended = $totalOfCreditsAttended;
        return $this;
    }

    public function getTotalOfCreditsComposed(): ?float
    {
        return $this->totalOfCreditsComposed;
    }

    public function setTotalOfCreditsComposed(?float $totalOfCreditsComposed): MarkAnnualGeneralAverageCalculated
    {
        $this->totalOfCreditsComposed = $totalOfCreditsComposed;
        return $this;
    }

    public function getPercentageSubjectNumber(): ?float
    {
        return $this->percentageSubjectNumber;
    }

    public function setPercentageSubjectNumber(?float $percentageSubjectNumber): MarkAnnualGeneralAverageCalculated
    {
        $this->percentageSubjectNumber = $percentageSubjectNumber;
        return $this;
    }

    public function getPercentageTotalCoefficient(): ?float
    {
        return $this->percentageTotalCoefficient;
    }

    public function setPercentageTotalCoefficient(?float $percentageTotalCoefficient): MarkAnnualGeneralAverageCalculated
    {
        $this->percentageTotalCoefficient = $percentageTotalCoefficient;
        return $this;
    }

    public function getTotalCreditConsidered(): ?float
    {
        return $this->totalCreditConsidered;
    }

    public function setTotalCreditConsidered(?float $totalCreditConsidered): MarkAnnualGeneralAverageCalculated
    {
        $this->totalCreditConsidered = $totalCreditConsidered;
        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): MarkAnnualGeneralAverageCalculated
    {
        $this->rank = $rank;
        return $this;
    }

    public function getTotalStudentsClassed(): ?int
    {
        return $this->totalStudentsClassed;
    }

    public function setTotalStudentsClassed(?int $totalStudentsClassed): MarkAnnualGeneralAverageCalculated
    {
        $this->totalStudentsClassed = $totalStudentsClassed;
        return $this;
    }

    public function getBlameWork(): ?bool
    {
        return $this->blameWork;
    }

    public function setBlameWork(?bool $blameWork): MarkAnnualGeneralAverageCalculated
    {
        $this->blameWork = $blameWork;
        return $this;
    }

    public function getWarningWork(): ?bool
    {
        return $this->warningWork;
    }

    public function setWarningWork(?bool $warningWork): MarkAnnualGeneralAverageCalculated
    {
        $this->warningWork = $warningWork;
        return $this;
    }

    public function getGrantEncouragement(): ?bool
    {
        return $this->grantEncouragement;
    }

    public function setGrantEncouragement(?bool $grantEncouragement): MarkAnnualGeneralAverageCalculated
    {
        $this->grantEncouragement = $grantEncouragement;
        return $this;
    }

    public function getGrantCongratulation(): ?bool
    {
        return $this->grantCongratulation;
    }

    public function setGrantCongratulation(?bool $grantCongratulation): MarkAnnualGeneralAverageCalculated
    {
        $this->grantCongratulation = $grantCongratulation;
        return $this;
    }

    public function getGrantThAnnual(): ?bool
    {
        return $this->grantThAnnual;
    }

    public function setGrantThAnnual(?bool $grantThAnnual): MarkAnnualGeneralAverageCalculated
    {
        $this->grantThAnnual = $grantThAnnual;
        return $this;
    }

    public function getIsPromoted(): ?bool
    {
        return $this->isPromoted;
    }

    public function setIsPromoted(?bool $isPromoted): MarkAnnualGeneralAverageCalculated
    {
        $this->isPromoted = $isPromoted;
        return $this;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function setFailureReason(?string $failureReason): MarkAnnualGeneralAverageCalculated
    {
        $this->failureReason = $failureReason;
        return $this;
    }

    public function getPromotionFormulaCondition(): ?FormulaCondition
    {
        return $this->promotionFormulaCondition;
    }

    public function setPromotionFormulaCondition(?FormulaCondition $promotionFormulaCondition): MarkAnnualGeneralAverageCalculated
    {
        $this->promotionFormulaCondition = $promotionFormulaCondition;
        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): MarkAnnualGeneralAverageCalculated
    {
        $this->total = $total;
        return $this;
    }
}
