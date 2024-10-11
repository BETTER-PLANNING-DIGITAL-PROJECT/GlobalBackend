<?php

namespace App\Entity\School\Exam\Operation\Annual\Course;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculated;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Operation\Annual\Course\MarkAnnualCourseSequenceCalculationRelationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MarkAnnualCourseSequenceCalculationRelationRepository::class)]
#[ORM\Table(name: 'school_mark_annual_course_period_calculation_relation')]
class MarkAnnualCoursePeriodCalculationRelation
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
    #[Assert\NotNull(message: 'studentCourseRegistration NotNull')]
    private ?StudentCourseRegistration $studentCourseRegistration = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'course NotNull')]
    private ?ClassProgram $classProgram = null;

    #[ORM\ManyToOne]
    private ?Module $module = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'mark NotNull')]
    private ?MarkPeriodCourseCalculated $markPeriodCourseCalculated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'markAnnualCourseCalculated NotNull')]
    private ?MarkAnnualCourseCalculated $markAnnualCourseCalculated = null;

    #[ORM\Column(nullable: true)]
    private ?float $markCalculated = null;

    #[ORM\Column(nullable: true)]
    private ?float $markUsed = null;

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

    public function setId(?int $id): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->id = $id;
        return $this;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->institution = $institution;
        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->year = $year;
        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->school = $school;
        return $this;
    }

    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function setClass(?SchoolClass $class): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->class = $class;
        return $this;
    }

    public function getStudent(): ?StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(?StudentRegistration $student): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->student = $student;
        return $this;
    }

    public function getEvaluationPeriod(): ?EvaluationPeriod
    {
        return $this->evaluationPeriod;
    }

    public function setEvaluationPeriod(?EvaluationPeriod $evaluationPeriod): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->evaluationPeriod = $evaluationPeriod;
        return $this;
    }

    public function getStudentCourseRegistration(): ?StudentCourseRegistration
    {
        return $this->studentCourseRegistration;
    }

    public function setStudentCourseRegistration(?StudentCourseRegistration $studentCourseRegistration): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->studentCourseRegistration = $studentCourseRegistration;
        return $this;
    }

    public function getClassProgram(): ?ClassProgram
    {
        return $this->classProgram;
    }

    public function setClassProgram(?ClassProgram $classProgram): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->classProgram = $classProgram;
        return $this;
    }

    public function getMarkCalculated(): ?float
    {
        return $this->markCalculated;
    }

    public function setMarkCalculated(?float $markCalculated): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->markCalculated = $markCalculated;
        return $this;
    }

    public function getMarkUsed(): ?float
    {
        return $this->markUsed;
    }

    public function setMarkUsed(?float $markUsed): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->markUsed = $markUsed;
        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->isValidated = $isValidated;
        return $this;
    }

    public function getIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(?bool $is_enable): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->is_enable = $is_enable;
        return $this;
    }

    public function getIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(?bool $is_archive): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->is_archive = $is_archive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->user = $user;
        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->module = $module;
        return $this;
    }

    public function getMarkPeriodCourseCalculated(): ?MarkPeriodCourseCalculated
    {
        return $this->markPeriodCourseCalculated;
    }

    public function setMarkPeriodCourseCalculated(?MarkPeriodCourseCalculated $markPeriodCourseCalculated): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->markPeriodCourseCalculated = $markPeriodCourseCalculated;
        return $this;
    }

    public function getMarkAnnualCourseCalculated(): ?MarkAnnualCourseCalculated
    {
        return $this->markAnnualCourseCalculated;
    }

    public function setMarkAnnualCourseCalculated(?MarkAnnualCourseCalculated $markAnnualCourseCalculated): MarkAnnualCoursePeriodCalculationRelation
    {
        $this->markAnnualCourseCalculated = $markAnnualCourseCalculated;
        return $this;
    }
}
