<?php

namespace App\Entity\School\Exam\Operation\Period\Course;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\NoteType;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Mark;
use App\Entity\School\Exam\Operation\Sequence\Course\MarkSequenceCourseCalculated;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Operation\Period\Course\MarkPeriodCourseCalculationRelationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MarkPeriodCourseCalculationRelationRepository::class)]
#[ORM\Table(name: 'school_mark_period_course_calculation_relation')]
class MarkPeriodCourseCalculationRelation
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
    private ?MarkSequenceCourseCalculated $markSequenceCourseCalculated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'markSequenceCourseCalculated NotNull')]
    private ?MarkPeriodCourseCalculated $markPeriodCourseCalculated = null;

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

    public function setId(?int $id): MarkPeriodCourseCalculationRelation
    {
        $this->id = $id;
        return $this;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): MarkPeriodCourseCalculationRelation
    {
        $this->institution = $institution;
        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): MarkPeriodCourseCalculationRelation
    {
        $this->year = $year;
        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): MarkPeriodCourseCalculationRelation
    {
        $this->school = $school;
        return $this;
    }

    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function setClass(?SchoolClass $class): MarkPeriodCourseCalculationRelation
    {
        $this->class = $class;
        return $this;
    }

    public function getStudent(): ?StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(?StudentRegistration $student): MarkPeriodCourseCalculationRelation
    {
        $this->student = $student;
        return $this;
    }

    public function getEvaluationPeriod(): ?EvaluationPeriod
    {
        return $this->evaluationPeriod;
    }

    public function setEvaluationPeriod(?EvaluationPeriod $evaluationPeriod): MarkPeriodCourseCalculationRelation
    {
        $this->evaluationPeriod = $evaluationPeriod;
        return $this;
    }

    public function getStudentCourseRegistration(): ?StudentCourseRegistration
    {
        return $this->studentCourseRegistration;
    }

    public function setStudentCourseRegistration(?StudentCourseRegistration $studentCourseRegistration): MarkPeriodCourseCalculationRelation
    {
        $this->studentCourseRegistration = $studentCourseRegistration;
        return $this;
    }

    public function getClassProgram(): ?ClassProgram
    {
        return $this->classProgram;
    }

    public function setClassProgram(?ClassProgram $classProgram): MarkPeriodCourseCalculationRelation
    {
        $this->classProgram = $classProgram;
        return $this;
    }

    public function getNoteType(): ?NoteType
    {
        return $this->noteType;
    }

    public function setNoteType(?NoteType $noteType): MarkPeriodCourseCalculationRelation
    {
        $this->noteType = $noteType;
        return $this;
    }

    public function getMarkCalculated(): ?float
    {
        return $this->markCalculated;
    }

    public function setMarkCalculated(?float $markCalculated): MarkPeriodCourseCalculationRelation
    {
        $this->markCalculated = $markCalculated;
        return $this;
    }

    public function getMarkUsed(): ?float
    {
        return $this->markUsed;
    }

    public function setMarkUsed(?float $markUsed): MarkPeriodCourseCalculationRelation
    {
        $this->markUsed = $markUsed;
        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): MarkPeriodCourseCalculationRelation
    {
        $this->isValidated = $isValidated;
        return $this;
    }

    public function getIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(?bool $is_enable): MarkPeriodCourseCalculationRelation
    {
        $this->is_enable = $is_enable;
        return $this;
    }

    public function getIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(?bool $is_archive): MarkPeriodCourseCalculationRelation
    {
        $this->is_archive = $is_archive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): MarkPeriodCourseCalculationRelation
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): MarkPeriodCourseCalculationRelation
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): MarkPeriodCourseCalculationRelation
    {
        $this->user = $user;
        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): MarkPeriodCourseCalculationRelation
    {
        $this->module = $module;
        return $this;
    }

    public function getMarkSequenceCourseCalculated(): ?MarkSequenceCourseCalculated
    {
        return $this->markSequenceCourseCalculated;
    }

    public function setMarkSequenceCourseCalculated(?MarkSequenceCourseCalculated $markSequenceCourseCalculated): MarkPeriodCourseCalculationRelation
    {
        $this->markSequenceCourseCalculated = $markSequenceCourseCalculated;
        return $this;
    }

    public function getMarkPeriodCourseCalculated(): ?MarkPeriodCourseCalculated
    {
        return $this->markPeriodCourseCalculated;
    }

    public function setMarkPeriodCourseCalculated(?MarkPeriodCourseCalculated $markPeriodCourseCalculated): MarkPeriodCourseCalculationRelation
    {
        $this->markPeriodCourseCalculated = $markPeriodCourseCalculated;
        return $this;
    }

    public function getSequence(): ?Sequence
    {
        return $this->sequence;
    }

    public function setSequence(?Sequence $sequence): MarkPeriodCourseCalculationRelation
    {
        $this->sequence = $sequence;
        return $this;
    }
}
