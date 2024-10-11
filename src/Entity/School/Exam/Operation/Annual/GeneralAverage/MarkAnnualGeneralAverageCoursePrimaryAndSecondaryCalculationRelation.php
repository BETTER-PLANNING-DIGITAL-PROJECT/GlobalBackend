<?php

namespace App\Entity\School\Exam\Operation\Annual\GeneralAverage;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\MarkGrade;
use App\Entity\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculated;
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
use App\Repository\School\Exam\Operation\Annual\GeneralAverage\MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelationRepository::class)]
#[ORM\Table(name: 'school_mark_annual_general_average_course_ps_calc_relation')]
class MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
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
    private ?Module $module = null;

    // Il y a $studentCourseRegistration ici a cause des notes du superieurs ou les notes annuelles
    // sont celles du semestre
    #[ORM\ManyToOne]
    private ?StudentCourseRegistration $studentCourseRegistration = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'classProgram NotNull')]
    private ?ClassProgram $classProgram = null;

    #[ORM\ManyToOne]
    private ?MarkAnnualCourseCalculated $markCourseCalculated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'annualGeneralAverageCalculated NotNull')]
    private ?MarkAnnualGeneralAverageCalculated $annualGeneralAverageCalculated = null;

    #[ORM\Column(nullable: true)]
    private ?float $averageCalculated = null;

    #[ORM\Column(nullable: true)]
    private ?float $markUsed = null;

    #[ORM\Column(nullable: true)]
    private ?float $validationBase = null;

    #[ORM\Column(nullable: true)]
    private ?float $coeff = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isModuleEliminated = null;

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

    public function setInstitution(?Institution $institution): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->institution = $institution;
        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->year = $year;
        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->school = $school;
        return $this;
    }

    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function setClass(?SchoolClass $class): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->class = $class;
        return $this;
    }

    public function getStudent(): ?StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(?StudentRegistration $student): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->student = $student;
        return $this;
    }

    public function getEvaluationPeriod(): ?EvaluationPeriod
    {
        return $this->evaluationPeriod;
    }

    public function setEvaluationPeriod(?EvaluationPeriod $evaluationPeriod): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->evaluationPeriod = $evaluationPeriod;
        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->module = $module;
        return $this;
    }

    public function getStudentCourseRegistration(): ?StudentCourseRegistration
    {
        return $this->studentCourseRegistration;
    }

    public function setStudentCourseRegistration(?StudentCourseRegistration $studentCourseRegistration): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->studentCourseRegistration = $studentCourseRegistration;
        return $this;
    }

    public function getClassProgram(): ?ClassProgram
    {
        return $this->classProgram;
    }

    public function setClassProgram(?ClassProgram $classProgram): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->classProgram = $classProgram;
        return $this;
    }

    public function getMarkCourseCalculated(): ?MarkAnnualCourseCalculated
    {
        return $this->markCourseCalculated;
    }

    public function setMarkCourseCalculated(?MarkAnnualCourseCalculated $markCourseCalculated): void
    {
        $this->markCourseCalculated = $markCourseCalculated;
    }

    public function getAnnualGeneralAverageCalculated(): ?MarkAnnualGeneralAverageCalculated
    {
        return $this->annualGeneralAverageCalculated;
    }

    public function setAnnualGeneralAverageCalculated(?MarkAnnualGeneralAverageCalculated $annualGeneralAverageCalculated): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->annualGeneralAverageCalculated = $annualGeneralAverageCalculated;
        return $this;
    }

    public function getAverageCalculated(): ?float
    {
        return $this->averageCalculated;
    }

    public function setAverageCalculated(?float $averageCalculated): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->averageCalculated = $averageCalculated;
        return $this;
    }

    public function getMarkUsed(): ?float
    {
        return $this->markUsed;
    }

    public function setMarkUsed(?float $markUsed): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->markUsed = $markUsed;
        return $this;
    }

    public function getValidationBase(): ?float
    {
        return $this->validationBase;
    }

    public function setValidationBase(?float $validationBase): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->validationBase = $validationBase;
        return $this;
    }

    public function getCoeff(): ?float
    {
        return $this->coeff;
    }

    public function setCoeff(?float $coeff): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->coeff = $coeff;
        return $this;
    }

    public function getIsCourseValidated(): ?string
    {
        return $this->isCourseValidated;
    }

    public function setIsCourseValidated(?string $isCourseValidated): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->isCourseValidated = $isCourseValidated;
        return $this;
    }

    public function getGradeUsed(): ?MarkGrade
    {
        return $this->gradeUsed;
    }

    public function setGradeUsed(?MarkGrade $gradeUsed): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->gradeUsed = $gradeUsed;
        return $this;
    }

    public function getAverageGpaCalculated(): ?float
    {
        return $this->averageGpaCalculated;
    }

    public function setAverageGpaCalculated(?float $averageGpaCalculated): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->averageGpaCalculated = $averageGpaCalculated;
        return $this;
    }

    public function getGpaUsed(): ?float
    {
        return $this->gpaUsed;
    }

    public function setGpaUsed(?float $gpaUsed): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->gpaUsed = $gpaUsed;
        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->isValidated = $isValidated;
        return $this;
    }

    public function getIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(?bool $is_enable): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->is_enable = $is_enable;
        return $this;
    }

    public function getIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(?bool $is_archive): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->is_archive = $is_archive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->user = $user;
        return $this;
    }

    public function getIsModuleEliminated(): ?bool
    {
        return $this->isModuleEliminated;
    }

    public function setIsModuleEliminated(?bool $isModuleEliminated): MarkAnnualGeneralAverageCoursePrimaryAndSecondaryCalculationRelation
    {
        $this->isModuleEliminated = $isModuleEliminated;
        return $this;
    }
}
