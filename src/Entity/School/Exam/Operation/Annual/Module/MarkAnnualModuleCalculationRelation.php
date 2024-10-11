<?php

namespace App\Entity\School\Exam\Operation\Annual\Module;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\MarkGrade;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculated;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Operation\Annual\Module\MarkAnnualModuleCalculationRelationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MarkAnnualModuleCalculationRelationRepository::class)]
#[ORM\Table(name: 'school_mark_annual_module_calculation_relation')]
class MarkAnnualModuleCalculationRelation
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
    #[Assert\NotNull(message: 'module NotNull')]
    private ?Module $module = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'classProgram NotNull')]
    private ?ClassProgram $classProgram = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'courseCalculated NotNull')]
    private ?MarkAnnualCourseCalculated $markCourseCalculated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'moduleCalculated NotNull')]
    private ?MarkAnnualModuleCalculated $markModuleCalculated = null;

    #[ORM\Column(nullable: true)]
    private ?float $markCalculated = null;

    #[ORM\Column(nullable: true)]
    private ?float $markUsed = null;

    #[ORM\Column(nullable: true)]
    private ?float $validationBase = null;

    #[ORM\Column(nullable: true)]
    private ?float $coeff = null;

    #[ORM\Column(nullable: true)]
    private ?string $isCourseValidated = null;

    #[ORM\ManyToOne]
    private ?MarkGrade $gradeUsed = null;

    #[ORM\Column(nullable: true)]
    private ?float $averageGpaCalculated = null;

    #[ORM\Column(nullable: true)]
    private ?float $gpaUsed = null;

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

    public function setInstitution(?Institution $institution): MarkAnnualModuleCalculationRelation
    {
        $this->institution = $institution;
        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): MarkAnnualModuleCalculationRelation
    {
        $this->year = $year;
        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): MarkAnnualModuleCalculationRelation
    {
        $this->school = $school;
        return $this;
    }

    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function setClass(?SchoolClass $class): MarkAnnualModuleCalculationRelation
    {
        $this->class = $class;
        return $this;
    }

    public function getStudent(): ?StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(?StudentRegistration $student): MarkAnnualModuleCalculationRelation
    {
        $this->student = $student;
        return $this;
    }

    public function getClassProgram(): ?ClassProgram
    {
        return $this->classProgram;
    }

    public function setClassProgram(?ClassProgram $classProgram): MarkAnnualModuleCalculationRelation
    {
        $this->classProgram = $classProgram;
        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): MarkAnnualModuleCalculationRelation
    {
        $this->module = $module;
        return $this;
    }

    public function getMarkCourseCalculated(): ?MarkAnnualCourseCalculated
    {
        return $this->markCourseCalculated;
    }

    public function setMarkCourseCalculated(?MarkAnnualCourseCalculated $markCourseCalculated): MarkAnnualModuleCalculationRelation
    {
        $this->markCourseCalculated = $markCourseCalculated;
        return $this;
    }

    public function getMarkModuleCalculated(): ?MarkAnnualModuleCalculated
    {
        return $this->markModuleCalculated;
    }

    public function setMarkModuleCalculated(?MarkAnnualModuleCalculated $markModuleCalculated): MarkAnnualModuleCalculationRelation
    {
        $this->markModuleCalculated = $markModuleCalculated;
        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): MarkAnnualModuleCalculationRelation
    {
        $this->isValidated = $isValidated;
        return $this;
    }

    public function getIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(?bool $is_enable): MarkAnnualModuleCalculationRelation
    {
        $this->is_enable = $is_enable;
        return $this;
    }

    public function getIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(?bool $is_archive): MarkAnnualModuleCalculationRelation
    {
        $this->is_archive = $is_archive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): MarkAnnualModuleCalculationRelation
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): MarkAnnualModuleCalculationRelation
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): MarkAnnualModuleCalculationRelation
    {
        $this->user = $user;
        return $this;
    }

    public function getMarkCalculated(): ?float
    {
        return $this->markCalculated;
    }

    public function setMarkCalculated(?float $markCalculated): MarkAnnualModuleCalculationRelation
    {
        $this->markCalculated = $markCalculated;
        return $this;
    }

    public function getMarkUsed(): ?float
    {
        return $this->markUsed;
    }

    public function setMarkUsed(?float $markUsed): MarkAnnualModuleCalculationRelation
    {
        $this->markUsed = $markUsed;
        return $this;
    }

    public function getAverageGpaCalculated(): ?float
    {
        return $this->averageGpaCalculated;
    }

    public function setAverageGpaCalculated(?float $averageGpaCalculated): MarkAnnualModuleCalculationRelation
    {
        $this->averageGpaCalculated = $averageGpaCalculated;
        return $this;
    }

    public function getGpaUsed(): ?float
    {
        return $this->gpaUsed;
    }

    public function setGpaUsed(?float $gpaUsed): MarkAnnualModuleCalculationRelation
    {
        $this->gpaUsed = $gpaUsed;
        return $this;
    }

    public function getCoeff(): ?float
    {
        return $this->coeff;
    }

    public function setCoeff(?float $coeff): MarkAnnualModuleCalculationRelation
    {
        $this->coeff = $coeff;
        return $this;
    }

    public function getGradeUsed(): ?MarkGrade
    {
        return $this->gradeUsed;
    }

    public function setGradeUsed(?MarkGrade $gradeUsed): MarkAnnualModuleCalculationRelation
    {
        $this->gradeUsed = $gradeUsed;
        return $this;
    }

    public function getValidationBase(): ?float
    {
        return $this->validationBase;
    }

    public function setValidationBase(?float $validationBase): MarkAnnualModuleCalculationRelation
    {
        $this->validationBase = $validationBase;
        return $this;
    }

    public function getIsCourseValidated(): ?string
    {
        return $this->isCourseValidated;
    }

    public function setIsCourseValidated(?string $isCourseValidated): MarkAnnualModuleCalculationRelation
    {
        $this->isCourseValidated = $isCourseValidated;
        return $this;
    }
}
