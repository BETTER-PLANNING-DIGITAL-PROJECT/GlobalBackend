<?php

namespace App\Entity\School\Exam\Operation;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\DeleteSelectedResourceController;
use App\Controller\School\Exam\Mark\ExportMarkController;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Exam\Configuration\NoteType;
use App\Entity\School\Exam\Configuration\Sequence;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Registration\StudentCourseRegistration;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Program\ClassProgram;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\State\Provider\School\Exam\Operation\OpenOrCloseMarkEntryProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\String_;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MarkRepository::class)]
#[ORM\Table(name: 'school_mark')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/mark/open-or-close/{sequenceId}/{classId}/{evaluationPeriodId}',
            uriVariables: [
                'sequenceId'=>String_::class,
                'classId'=>String_::class,
                'evaluationPeriodId'=>String_::class
            ],
            normalizationContext: [
                'groups' => ['get:SchoolMark:collection'],
            ],
            provider: OpenOrCloseMarkEntryProvider::class
        ),
        /*new GetCollection(
           uriTemplate: '/export/mark/by-class-program/{schoolMarkData}',
           formats: ['ms-excel'=>['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
           uriVariables: ['schoolMarkData'=>String_::class],
           openapiContext: [
                'responses'=>[
                    200=>[
                        'description' => 'The marks exportated in MS Excel format',
                        'content' => [
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => [
                                'schema' => [
                                    'type' => 'string',
                                    'format' => 'binary'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
           provider: ExportMarkProvider::class
        ),*/
        // Delete
        new Delete(
            uriTemplate: '/delete/mark/{id}',
            requirements: ['id' => '\d+'],
        ),

        new Delete(
            uriTemplate: '/delete/selected/mark',
            controller: DeleteSelectedResourceController::class,
        ),
    ]
)]

class Mark
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:SchoolMark:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'school_year NotNull')]
    #[Groups(['get:SchoolMark:collection'])]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'institution NotNull')]
    #[Groups(['get:SchoolMark:collection'])]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'sequence NotNull')]
    #[Groups(['get:SchoolMark:collection'])]
    private ?Sequence $sequence = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'school_class NotNull')]
    #[Groups(['get:SchoolMark:collection'])]
    private ?SchoolClass $class = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'school NotNull')]
    #[Groups(['get:SchoolMark:collection'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'school_class NotNull')]
    #[Groups(['get:SchoolMark:collection'])]
    private ?StudentRegistration $student = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'studCourseReg NotNull')]
    #[Groups(['get:SchoolMark:collection'])]
    private ?StudentCourseRegistration $studentCourseRegistration = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'classProgram NotNull')]
    #[Groups(['get:SchoolMark:collection'])]
    private ?ClassProgram $classProgram = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:SchoolMark:collection'])]
    private ?string $anonymityCode = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SchoolMark:collection'])]
    #[Assert\NotBlank(message: 'weighting NotNull')]
    private ?float $weighting = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SchoolMark:collection'])]
    #[Assert\NotBlank(message: 'mark NotNull')]
    private ?float $mark = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:SchoolMark:collection'])]
    #[Assert\NotBlank(message: 'markEntered NotNull')]
    private ?float $markEntered = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'baseMark NotNull')]
    #[Assert\NotBlank(message: 'baseMark NotBlank')]
    #[Groups(['get:SchoolMark:collection'])]
    private ?float $base = null;

    #[ORM\Column]
    #[Groups(['get:SchoolMark:collection'])]
    private ?bool $isOpen = null;

    #[ORM\Column]
    #[Groups(['get:SchoolMark:collection'])]
    private ?bool $isCalculated = null;

    #[ORM\Column]
    #[Groups(['get:SchoolMark:collection'])]
    private ?bool $isValidated = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SchoolMark:collection'])]
    private ?NoteType $noteType = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'evaluationPeriod NotNull')]
    #[Groups(['get:SchoolMark:collection'])]
    private ?EvaluationPeriod $evaluationPeriod = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SchoolMark:collection'])]
    private ?Module $module = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:SchoolMark:collection'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE,nullable: true)]
//    #[Assert\NotBlank(message: 'assignmentDate NotBlank')]
    #[Groups(['get:SchoolMark:collection'])]
    private ?\DateTimeInterface $assignmentDate = null;

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

        $this->isCalculated = false;
        $this->isValidated = false;
        $this->isOpen = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSequence(): ?Sequence
    {
        return $this->sequence;
    }

    public function setSequence(?Sequence $sequence): self
    {
        $this->sequence = $sequence;

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

    public function getAnonymityCode(): ?string
    {
        return $this->anonymityCode;
    }

    public function setAnonymityCode(?string $anonymityCode): self
    {
        $this->anonymityCode = $anonymityCode;

        return $this;
    }

    public function getMark(): ?float
    {
        return $this->mark;
    }

    public function setMark(?float $mark): self
    {
        $this->mark = $mark;

        return $this;
    }

    public function getBase(): ?float
    {
        return $this->base;
    }

    public function setBase(float $base): self
    {
        $this->base = $base;

        return $this;
    }

    public function isIsOpen(): ?bool
    {
        return $this->isOpen;
    }

    public function setIsOpen(bool $isOpen): self
    {
        $this->isOpen = $isOpen;

        return $this;
    }

    public function getNoteType(): ?NoteType
    {
        return $this->noteType;
    }

    public function setNoteType(?NoteType $noteType): self
    {
        $this->noteType = $noteType;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAssignmentDate(): ?\DateTimeInterface
    {
        return $this->assignmentDate;
    }

    public function setAssignmentDate(?\DateTimeInterface $assignmentDate): self
    {
        $this->assignmentDate = $assignmentDate;

        return $this;
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

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): static
    {
        $this->school = $school;

        return $this;
    }

    public function getStudent(): ?StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(?StudentRegistration $student): void
    {
        $this->student = $student;
    }

    public function getStudentCourseRegistration(): ?StudentCourseRegistration
    {
        return $this->studentCourseRegistration;
    }

    public function setStudentCourseRegistration(?StudentCourseRegistration $studentCourseRegistration): void
    {
        $this->studentCourseRegistration = $studentCourseRegistration;
    }

    public function getIsSimulated(): ?bool
    {
        return $this->isCalculated;
    }

    public function setIsSimulated(?bool $isCalculated): Mark
    {
        $this->isCalculated = $isCalculated;
        return $this;
    }

    public function getEvaluationPeriod(): ?EvaluationPeriod
    {
        return $this->evaluationPeriod;
    }

    public function setEvaluationPeriod(?EvaluationPeriod $evaluationPeriod): Mark
    {
        $this->evaluationPeriod = $evaluationPeriod;
        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): Mark
    {
        $this->module = $module;
        return $this;
    }

    public function getClassProgram(): ?ClassProgram
    {
        return $this->classProgram;
    }

    public function setClassProgram(?ClassProgram $classProgram): Mark
    {
        $this->classProgram = $classProgram;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): Mark
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): Mark
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): Mark
    {
        $this->user = $user;
        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): Mark
    {
        $this->isValidated = $isValidated;
        return $this;
    }

    public function getMarkEntered(): ?float
    {
        return $this->markEntered;
    }

    public function setMarkEntered(?float $markEntered): Mark
    {
        $this->markEntered = $markEntered;
        return $this;
    }

    public function getIsCalculated(): ?bool
    {
        return $this->isCalculated;
    }

    public function setIsCalculated(?bool $isCalculated): Mark
    {
        $this->isCalculated = $isCalculated;
        return $this;
    }

    public function getWeighting(): ?float
    {
        return $this->weighting;
    }

    public function setWeighting(?float $weighting): Mark
    {
        $this->weighting = $weighting;
        return $this;
    }
}
