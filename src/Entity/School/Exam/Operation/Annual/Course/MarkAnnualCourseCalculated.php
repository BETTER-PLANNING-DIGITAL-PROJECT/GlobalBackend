<?php

namespace App\Entity\School\Exam\Operation\Annual\Course;

use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\MarkGrade;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Operation\Annual\Course\MarkAnnualCourseCalculatedRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MarkAnnualCourseCalculatedRepository::class)]
#[ORM\Table(name: 'school_mark_annual_course_calculated')]
class MarkAnnualCourseCalculated
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
    #[Assert\NotNull(message: 'classProgram NotNull')]
    private ?ClassProgram $classProgram = null;

    #[ORM\Column(nullable: true)]
    private ?float $mark = null;

    #[ORM\ManyToOne]
    private ?Module $module = null;

    #[ORM\Column]
    private ?string $isCourseValidated = null;

    #[ORM\Column]
    private ?bool $isModuleEliminated = null;

    // L'etudiant est classe par rapport a la matiere
    #[ORM\Column(nullable: true)]
    private ?bool $isClassed = null;

    #[ORM\Column(name: '`rank`',nullable: true)]
    private ?int $rank = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalCourseStudentsRegistered = null;

    #[ORM\Column(nullable: true)]
    private ?float $validationBase = null;

    #[ORM\Column(nullable: true)]
    private ?float $coeff = null;

    #[ORM\Column(nullable: true)]
    private ?float $total = null;

    #[ORM\Column]
    private ?bool $isValidated = null;

    #[ORM\ManyToOne]
    private ?MarkGrade $grade = null;

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
        $this->isModuleEliminated = false;
        $this->isClassed = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): MarkAnnualCourseCalculated
    {
        $this->id = $id;
        return $this;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): MarkAnnualCourseCalculated
    {
        $this->institution = $institution;
        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): MarkAnnualCourseCalculated
    {
        $this->year = $year;
        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): MarkAnnualCourseCalculated
    {
        $this->school = $school;
        return $this;
    }

    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function setClass(?SchoolClass $class): MarkAnnualCourseCalculated
    {
        $this->class = $class;
        return $this;
    }

    public function getStudent(): ?StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(?StudentRegistration $student): MarkAnnualCourseCalculated
    {
        $this->student = $student;
        return $this;
    }

    public function getClassProgram(): ?ClassProgram
    {
        return $this->classProgram;
    }

    public function setClassProgram(?ClassProgram $classProgram): MarkAnnualCourseCalculated
    {
        $this->classProgram = $classProgram;
        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): MarkAnnualCourseCalculated
    {
        $this->module = $module;
        return $this;
    }

    public function getMark(): ?float
    {
        return $this->mark;
    }

    public function setMark(?float $mark): MarkAnnualCourseCalculated
    {
        $this->mark = $mark;
        return $this;
    }

    public function getIsCourseValidated(): ?string
    {
        return $this->isCourseValidated;
    }

    public function setIsCourseValidated(?string $isCourseValidated): MarkAnnualCourseCalculated
    {
        $this->isCourseValidated = $isCourseValidated;
        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): MarkAnnualCourseCalculated
    {
        $this->isValidated = $isValidated;
        return $this;
    }

    public function getGrade(): ?MarkGrade
    {
        return $this->grade;
    }

    public function setGrade(?MarkGrade $grade): MarkAnnualCourseCalculated
    {
        $this->grade = $grade;
        return $this;
    }

    public function getIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(?bool $is_enable): MarkAnnualCourseCalculated
    {
        $this->is_enable = $is_enable;
        return $this;
    }

    public function getIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(?bool $is_archive): MarkAnnualCourseCalculated
    {
        $this->is_archive = $is_archive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): MarkAnnualCourseCalculated
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): MarkAnnualCourseCalculated
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): MarkAnnualCourseCalculated
    {
        $this->user = $user;
        return $this;
    }

    public function getValidationBase(): ?float
    {
        return $this->validationBase;
    }

    public function setValidationBase(?float $validationBase): MarkAnnualCourseCalculated
    {
        $this->validationBase = $validationBase;
        return $this;
    }

    public function getCoeff(): ?float
    {
        return $this->coeff;
    }

    public function setCoeff(?float $coeff): MarkAnnualCourseCalculated
    {
        $this->coeff = $coeff;
        return $this;
    }

    public function getIsModuleEliminated(): ?bool
    {
        return $this->isModuleEliminated;
    }

    public function setIsModuleEliminated(?bool $isModuleEliminated): MarkAnnualCourseCalculated
    {
        $this->isModuleEliminated = $isModuleEliminated;
        return $this;
    }

    public function getTotalCourseStudentsRegistered(): ?int
    {
        return $this->totalCourseStudentsRegistered;
    }

    public function setTotalCourseStudentsRegistered(?int $totalCourseStudentsRegistered): MarkAnnualCourseCalculated
    {
        $this->totalCourseStudentsRegistered = $totalCourseStudentsRegistered;
        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): MarkAnnualCourseCalculated
    {
        $this->rank = $rank;
        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): MarkAnnualCourseCalculated
    {
        $this->total = $total;
        return $this;
    }

    public function getIsClassed(): ?bool
    {
        return $this->isClassed;
    }

    public function setIsClassed(?bool $isClassed): MarkAnnualCourseCalculated
    {
        $this->isClassed = $isClassed;
        return $this;
    }
}
