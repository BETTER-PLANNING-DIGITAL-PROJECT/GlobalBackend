<?php

namespace App\Entity\School\Schooling\Registration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\CourseStudentRegController;
use App\Controller\School\Study\CourseStudentRegInClassController;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Configuration\Subject;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\StudentCourseRegistrationRepository;
use App\State\Current\InstitutionProcessor;
use App\State\StudCourseRegAdminProvider;
use App\State\StudCourseRegOpeningClosingProvider;
use App\State\StudCourseRegProvider;
use App\State\StudCourseRegStudentProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StudentCourseRegistrationRepository::class)]
#[ORM\Table(name: 'school_student_course_registration')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/course-stud-reg/get/{id}',
            requirements: ['id' => '\d+'],
            controller: CourseStudentRegController::class,
            normalizationContext: [
                'groups' => ['get:StudentCourseRegistration:collection'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/course-stud-reg-in-class/get/{id}',
            requirements: ['id' => '\d+'],
            controller: CourseStudentRegInClassController::class,
            normalizationContext: [
                'groups' => ['get:StudCourseReg:collection'],
            ]
        ),
    ]
)]
#[UniqueEntity(
    fields: ['Student'],
    message: 'this Student Course Reg already exist',
)]

class StudentCourseRegistration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:StudentCourseRegistration:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentCourseRegistration:collection', 'write:StudentCourseRegistration'])]
    private ?SchoolClass $class = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentCourseRegistration:collection', 'write:StudentCourseRegistration'])]
    private ?ClassProgram $classProgram = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentCourseRegistration:collection', 'write:StudentCourseRegistration'])]
    private ?StudentRegistration $StudRegistration = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentCourseRegistration:collection','write:StudentCourseRegistration'])]
    private ?School $school = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:StudentCourseRegistration:collection','write:StudentCourseRegistration'])]
    private ?bool $isSubjectObligatory = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:StudentCourseRegistration:collection','write:StudentCourseRegistration'])]
    private ?bool $hasSchoolMark = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentCourseRegistration:collection', 'write:StudentCourseRegistration'])]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentCourseRegistration:collection', 'write:StudentCourseRegistration'])]
    private ?EvaluationPeriod $evaluationPeriod = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentCourseRegistration:collection', 'write:StudentCourseRegistration'])]
    private ?Module $module = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentCourseRegistration:collection', 'write:StudentCourseRegistration'])]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
        $this->isSubjectObligatory = true;
        $this->hasSchoolMark = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

    public function getClass(): ?SchoolClass
    {
        return $this->class;
    }

    public function setClass(?SchoolClass $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getClassProgram(): ?ClassProgram
    {
        return $this->classProgram;
    }

    public function setClassProgram(?ClassProgram $classProgram): self
    {
        $this->classProgram = $classProgram;

        return $this;
    }

    public function getStudRegistration(): ?StudentRegistration
    {
        return $this->StudRegistration;
    }

    public function setStudRegistration(?StudentRegistration $StudRegistration): self
    {
        $this->StudRegistration = $StudRegistration;

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

    public function getIsSubjectObligatory(): ?bool
    {
        return $this->isSubjectObligatory;
    }

    public function setIsSubjectObligatory(?bool $isSubjectObligatory): StudentCourseRegistration
    {
        $this->isSubjectObligatory = $isSubjectObligatory;
        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): StudentCourseRegistration
    {
        $this->school = $school;
        return $this;
    }

    public function getHasSchoolMark(): ?bool
    {
        return $this->hasSchoolMark;
    }

    public function setHasSchoolMark(?bool $hasSchoolMark): StudentCourseRegistration
    {
        $this->hasSchoolMark = $hasSchoolMark;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getEvaluationPeriod(): ?EvaluationPeriod
    {
        return $this->evaluationPeriod;
    }

    public function setEvaluationPeriod(?EvaluationPeriod $evaluationPeriod): StudentCourseRegistration
    {
        $this->evaluationPeriod = $evaluationPeriod;
        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): StudentCourseRegistration
    {
        $this->module = $module;
        return $this;
    }
}

