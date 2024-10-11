<?php

namespace App\Entity\School\Exam\Operation\Period\GeneralAverage;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
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
#[ORM\Table(name: 'school_mark_period_general_average_calculated')]
class MarkPeriodGeneralAverageCalculated
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

    #[ORM\Column(nullable: true)]
    private ?float $average = null;

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
    private ?bool $grantThComposition = null;

    #[ORM\Column(nullable: true)]
    private ?bool $grantEncouragement = null;

    #[ORM\Column(nullable: true)]
    private ?bool $grantCongratulation = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isClassed = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isEliminated = null;

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

        $this->blameWork = false;
        $this->warningWork = false;
        $this->grantEncouragement = false;
        $this->grantThComposition = false;
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

    public function setInstitution(?Institution $institution): MarkPeriodGeneralAverageCalculated
    {
        $this->institution = $institution;
        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): MarkPeriodGeneralAverageCalculated
    {
        $this->year = $year;
        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): MarkPeriodGeneralAverageCalculated
    {
        $this->school = $school;
        return $this;
    }

    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function setClass(?SchoolClass $class): MarkPeriodGeneralAverageCalculated
    {
        $this->class = $class;
        return $this;
    }

    public function getStudent(): ?StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(?StudentRegistration $student): MarkPeriodGeneralAverageCalculated
    {
        $this->student = $student;
        return $this;
    }

    public function getEvaluationPeriod(): ?EvaluationPeriod
    {
        return $this->evaluationPeriod;
    }

    public function setEvaluationPeriod(?EvaluationPeriod $evaluationPeriod): MarkPeriodGeneralAverageCalculated
    {
        $this->evaluationPeriod = $evaluationPeriod;
        return $this;
    }

    public function getAverage(): ?float
    {
        return $this->average;
    }

    public function setAverage(?float $average): MarkPeriodGeneralAverageCalculated
    {
        $this->average = $average;
        return $this;
    }

    public function getTotalCredit(): ?float
    {
        return $this->totalCredit;
    }

    public function setTotalCredit(?float $totalCredit): MarkPeriodGeneralAverageCalculated
    {
        $this->totalCredit = $totalCredit;
        return $this;
    }

    public function getTotalCreditValidated(): ?float
    {
        return $this->totalCreditValidated;
    }

    public function setTotalCreditValidated(?float $totalCreditValidated): MarkPeriodGeneralAverageCalculated
    {
        $this->totalCreditValidated = $totalCreditValidated;
        return $this;
    }

    public function getGrade(): ?MarkGrade
    {
        return $this->grade;
    }

    public function setGrade(?MarkGrade $grade): MarkPeriodGeneralAverageCalculated
    {
        $this->grade = $grade;
        return $this;
    }

    public function getAverageGpa(): ?float
    {
        return $this->averageGpa;
    }

    public function setAverageGpa(?float $averageGpa): MarkPeriodGeneralAverageCalculated
    {
        $this->averageGpa = $averageGpa;
        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): MarkPeriodGeneralAverageCalculated
    {
        $this->isValidated = $isValidated;
        return $this;
    }

    public function getIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(?bool $is_enable): MarkPeriodGeneralAverageCalculated
    {
        $this->is_enable = $is_enable;
        return $this;
    }

    public function getIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(?bool $is_archive): MarkPeriodGeneralAverageCalculated
    {
        $this->is_archive = $is_archive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): MarkPeriodGeneralAverageCalculated
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): MarkPeriodGeneralAverageCalculated
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): MarkPeriodGeneralAverageCalculated
    {
        $this->user = $user;
        return $this;
    }

    public function getIsClassed(): ?bool
    {
        return $this->isClassed;
    }

    public function setIsClassed(?bool $isClassed): MarkPeriodGeneralAverageCalculated
    {
        $this->isClassed = $isClassed;
        return $this;
    }

    public function getIsEliminated(): ?bool
    {
        return $this->isEliminated;
    }

    public function setIsEliminated(?bool $isEliminated): MarkPeriodGeneralAverageCalculated
    {
        $this->isEliminated = $isEliminated;
        return $this;
    }

    public function getNumberOfAttendedCourses(): ?int
    {
        return $this->numberOfAttendedCourses;
    }

    public function setNumberOfAttendedCourses(?int $numberOfAttendedCourses): MarkPeriodGeneralAverageCalculated
    {
        $this->numberOfAttendedCourses = $numberOfAttendedCourses;
        return $this;
    }

    public function getNumberOfComposedCourses(): ?int
    {
        return $this->numberOfComposedCourses;
    }

    public function setNumberOfComposedCourses(?int $numberOfComposedCourses): MarkPeriodGeneralAverageCalculated
    {
        $this->numberOfComposedCourses = $numberOfComposedCourses;
        return $this;
    }

    public function getTotalOfCreditsAttended(): ?float
    {
        return $this->totalOfCreditsAttended;
    }

    public function setTotalOfCreditsAttended(?float $totalOfCreditsAttended): MarkPeriodGeneralAverageCalculated
    {
        $this->totalOfCreditsAttended = $totalOfCreditsAttended;
        return $this;
    }

    public function getTotalOfCreditsComposed(): ?float
    {
        return $this->totalOfCreditsComposed;
    }

    public function setTotalOfCreditsComposed(?float $totalOfCreditsComposed): MarkPeriodGeneralAverageCalculated
    {
        $this->totalOfCreditsComposed = $totalOfCreditsComposed;
        return $this;
    }

    public function getPercentageSubjectNumber(): ?float
    {
        return $this->percentageSubjectNumber;
    }

    public function setPercentageSubjectNumber(?float $percentageSubjectNumber): MarkPeriodGeneralAverageCalculated
    {
        $this->percentageSubjectNumber = $percentageSubjectNumber;
        return $this;
    }

    public function getPercentageTotalCoefficient(): ?float
    {
        return $this->percentageTotalCoefficient;
    }

    public function setPercentageTotalCoefficient(?float $percentageTotalCoefficient): MarkPeriodGeneralAverageCalculated
    {
        $this->percentageTotalCoefficient = $percentageTotalCoefficient;
        return $this;
    }

    public function getTotalCreditConsidered(): ?float
    {
        return $this->totalCreditConsidered;
    }

    public function setTotalCreditConsidered(?float $totalCreditConsidered): MarkPeriodGeneralAverageCalculated
    {
        $this->totalCreditConsidered = $totalCreditConsidered;
        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): MarkPeriodGeneralAverageCalculated
    {
        $this->rank = $rank;
        return $this;
    }

    public function getTotalStudentsClassed(): ?int
    {
        return $this->totalStudentsClassed;
    }

    public function setTotalStudentsClassed(?int $totalStudentsClassed): MarkPeriodGeneralAverageCalculated
    {
        $this->totalStudentsClassed = $totalStudentsClassed;
        return $this;
    }

    public function getBlameWork(): ?bool
    {
        return $this->blameWork;
    }

    public function setBlameWork(?bool $blameWork): MarkPeriodGeneralAverageCalculated
    {
        $this->blameWork = $blameWork;
        return $this;
    }

    public function getWarningWork(): ?bool
    {
        return $this->warningWork;
    }

    public function setWarningWork(?bool $warningWork): MarkPeriodGeneralAverageCalculated
    {
        $this->warningWork = $warningWork;
        return $this;
    }

    public function getGrantThComposition(): ?bool
    {
        return $this->grantThComposition;
    }

    public function setGrantThComposition(?bool $grantThComposition): MarkPeriodGeneralAverageCalculated
    {
        $this->grantThComposition = $grantThComposition;
        return $this;
    }

    public function getGrantEncouragement(): ?bool
    {
        return $this->grantEncouragement;
    }

    public function setGrantEncouragement(?bool $grantEncouragement): MarkPeriodGeneralAverageCalculated
    {
        $this->grantEncouragement = $grantEncouragement;
        return $this;
    }

    public function getGrantCongratulation(): ?bool
    {
        return $this->grantCongratulation;
    }

    public function setGrantCongratulation(?bool $grantCongratulation): MarkPeriodGeneralAverageCalculated
    {
        $this->grantCongratulation = $grantCongratulation;
        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): MarkPeriodGeneralAverageCalculated
    {
        $this->total = $total;
        return $this;
    }
}
