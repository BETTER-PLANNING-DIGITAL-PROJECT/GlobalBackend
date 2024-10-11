<?php

namespace App\Entity\School\Study\Program;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Controller\School\Study\Program\DuplicateClassProgramsController;
use App\Controller\School\Study\Program\GetClassProgramCollectionController;
use App\Entity\School\Exam\Configuration\EvaluationPeriod;
use App\Entity\School\Schooling\Configuration\Room;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Study\Configuration\Module;
use App\Entity\School\Study\Configuration\Subject;
use App\Entity\School\Study\TimeTable\TimeTableModelDay;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\School\SubjectNature;
use App\Repository\School\Study\Program\ClassProgramRepository;
use App\State\ClassProgram\ClassProgramStudCourseRegAdminProvider;
use App\State\ClassProgram\ClassProgramStudCourseRegProvider;
use App\State\Processor\School\Study\Program\PostClassProgramProcessor;
use App\State\Processor\School\Study\Program\PutClassProgramProcessor;
use App\State\Provider\School\Study\Program\ClassProgramProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\String_;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClassProgramRepository::class)]
#[ORM\Table(name: 'school_class_program')]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/get/class-program/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:ClassProgram:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/class-program',
            controller: GetClassProgramCollectionController::class,
            order: ['id' => 'DESC'],
//            provider: ClassProgramProvider::class
            normalizationContext: [
                'groups' => ['get:ClassProgram:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/class-programs',
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:ClassProgram:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/class-program',
            denormalizationContext: [
                'groups' => ['write:ClassProgram'],
            ],
            processor: PostClassProgramProcessor::class,
        ),
        new Post(
            uriTemplate: '/duplicate/class-program',
            controller: DuplicateClassProgramsController::class,
            denormalizationContext: [
                'groups' => ['write:ClassProgram'],
            ]
        ),
        new Put(
            uriTemplate: '/edit/class-program/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:ClassProgram'],
            ],
            processor: PutClassProgramProcessor::class
        ),
        new Delete(
            uriTemplate: '/delete/class-program/{id}',
            requirements: ['id' => '\d+'],
        ),

        new Delete(
            uriTemplate: '/delete/class-program',
            controller: DeleteSelectedResourceController::class,
        ),

        new GetCollection(
            uriTemplate: '/class-program/get/stud-course-reg/{matricule}',
            uriVariables: [
                'matricule'=>String_::class
            ],
            normalizationContext: [
                'groups' => ['get:ClassProgram:collection'],
            ],
            provider: ClassProgramStudCourseRegProvider::class
        ),
        new GetCollection(
            uriTemplate: '/class-program/get/stud-course-reg',
            normalizationContext: [
                'groups' => ['get:ClassProgram:collection'],
            ],
            provider: ClassProgramStudCourseRegAdminProvider::class
        ),

    ],
    forceEager: false
)]
class ClassProgram
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:ClassProgram:collection', 'write:ClassProgram','get:TimeTableModelDayCell:collection', 'get:TeacherCourseRegistration:collection', 'get:StudentAttendance:collection','get:StudentAttendance:collection', 'get:HomeWork:collection', 'get:HomeWork:item', 'get:CoursePermutation:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram','get:TimeTableModelDayCell:collection'])]
    private ?SchoolClass $class = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram','get:TimeTableModelDayCell:collection','get:StudentAttendance:collection','get:StudentAttendance:collection'])]
    private ?Subject $subject = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?Module $module = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?EvaluationPeriod $evaluationPeriod = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram','get:TimeTableModelDayCell:collection', 'get:CoursePostponement:collection', 'get:CoursePermutation:collection', 'get:HomeWork:collection', 'get:HomeWork:item','get:StudentCourseRegistration:collection', 'get:TeacherCourseRegistration:collection'])]
    private ?string $codeuvc = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram','get:TimeTableModelDayCell:collection', 'get:TeacherCourseRegistration:collection', 'get:CoursePostponement:collection', 'get:CoursePermutation:collection', 'get:HomeWork:collection', 'get:HomeWork:item','get:StudentCourseRegistration:collection'])]
    private ?string $nameuvc = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?int $position = null;

    #[ORM\Column]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?int $coeff = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?float $validationBase = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram','get:TimeTableModelDayCell:collection'])]
    private ?Room $principalRoom = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?SubjectNature $nature = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?int $numberSubChap = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?TimeTableModelDay $modelDay = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $startAt = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $endAt = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $mondayStartCm = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $mondayEndCm = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $tuesdayStartCm = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $tuesdayEndCm = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $wednesdayStartCm = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $wednesdayEndCm = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $thursdayStartCm = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $thursdayEndCm = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $fridayStartCm = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $fridayEndCm = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $saturdayStartCm = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $saturdayEndCm = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $sundayStartCm = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $sundayEndCm = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $mondayCm = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $tuesdayCm = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $wednesdayCm = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $thursdayCm = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $fridayCm = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $saturdayCm = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $sundayCm = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $mondayStartTp = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $mondayEndTp = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $tuesdayStartTp = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $tuesdayEndTp = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $wednesdayStartTp = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $wednesdayEndTp = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $thursdayStartTp = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $thursdayEndTp = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $fridayStartTp = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $fridayEndTp = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $saturdayStartTp = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $saturdayEndTp = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $sundayStartTp = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $sundayEndTp = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $mondayTp = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $tuesdayTp = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $wednesdayTp = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $thursdayTp = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $fridayTp = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $saturdayTp = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $sundayTp = null;

    // ******************************************************************************************************************************

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $mondayStartTd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $mondayEndTd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $tuesdayStartTd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $tuesdayEndTd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $wednesdayStartTd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $wednesdayEndTd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $thursdayStartTd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $thursdayEndTd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $fridayStartTd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $fridayEndTd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $saturdayStartTd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $saturdayEndTd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $sundayStartTd = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?\DateTimeInterface $sundayEndTd = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $mondayTd = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $tuesdayTd = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $wednesdayTd = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $thursdayTd = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $fridayTd = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $saturdayTd = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?bool $sundayTd = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?TeacherCourseRegistration $teacherCourse = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram','get:StudentCourseRegistration:collection'])]
    private ?bool $isSubjectObligatory = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram','get:StudentCourseRegistration:collection', 'write:StudentCourseRegistration'])]
    private ?bool $isChoiceStudCourseOpen = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:ClassProgram:collection','write:ClassProgram'])]
    private ?Branch $branch;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isEnable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->isEnable = true;
        $this->isChoiceStudCourseOpen = false;
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

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

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

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getCoeff(): ?int
    {
        return $this->coeff;
    }

    public function setCoeff(?int $coeff): self
    {
        $this->coeff = $coeff;

        return $this;
    }

    public function getPrincipalRoom(): ?Room
    {
        return $this->principalRoom;
    }

    public function setPrincipalRoom(?Room $principalRoom): self
    {
        $this->principalRoom = $principalRoom;

        return $this;
    }

    public function isIsEnable(): ?bool
    {
        return $this->isEnable;
    }

    public function setIsEnable(?bool $isEnable): self
    {
        $this->isEnable = $isEnable;

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

    public function getNature(): ?SubjectNature
    {
        return $this->nature;
    }

    public function setNature(?SubjectNature $nature): self
    {
        $this->nature = $nature;

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

    public function getCodeuvc(): ?string
    {
        return $this->codeuvc;
    }

    public function setCodeuvc(string $codeuvc): self
    {
        $this->codeuvc = $codeuvc;

        return $this;
    }

    public function getNameuvc(): ?string
    {
        return $this->nameuvc;
    }

    public function setNameuvc(string $nameuvc): self
    {
        $this->nameuvc = $nameuvc;

        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getModelDay(): ?TimeTableModelDay
    {
        return $this->modelDay;
    }

    public function setModelDay(?TimeTableModelDay $modelDay): self
    {
        $this->modelDay = $modelDay;

        return $this;
    }

    public function getMondayStartCm(): ?\DateTimeInterface
    {
        return $this->mondayStartCm;
    }

    public function setMondayStartCm(?\DateTimeInterface $mondayStartCm): self
    {
        $this->mondayStartCm = $mondayStartCm;

        return $this;
    }

    public function getMondayEndCm(): ?\DateTimeInterface
    {
        return $this->mondayEndCm;
    }

    public function setMondayEndCm(?\DateTimeInterface $mondayEndCm): self
    {
        $this->mondayEndCm = $mondayEndCm;

        return $this;
    }

    public function getTuesdayStartCm(): ?\DateTimeInterface
    {
        return $this->tuesdayStartCm;
    }

    public function setTuesdayStartCm(?\DateTimeInterface $tuesdayStartCm): self
    {
        $this->tuesdayStartCm = $tuesdayStartCm;

        return $this;
    }

    public function getTuesdayEndCm(): ?\DateTimeInterface
    {
        return $this->tuesdayEndCm;
    }

    public function setTuesdayEndCm(?\DateTimeInterface $tuesdayEndCm): self
    {
        $this->tuesdayEndCm = $tuesdayEndCm;

        return $this;
    }


    public function getWednesdayStartCm(): ?\DateTimeInterface
    {
        return $this->wednesdayStartCm;
    }

    public function setWednesdayStartCm(?\DateTimeInterface $wednesdayStartCm): self
    {
        $this->wednesdayStartCm = $wednesdayStartCm;

        return $this;
    }

    public function getWednesdayEndCm(): ?\DateTimeInterface
    {
        return $this->wednesdayEndCm;
    }

    public function setWednesdayEndCm(?\DateTimeInterface $wednesdayEndCm): self
    {
        $this->wednesdayEndCm = $wednesdayEndCm;

        return $this;
    }

    public function getThursdayStartCm(): ?\DateTimeInterface
    {
        return $this->thursdayStartCm;
    }

    public function setThursdayStartCm(?\DateTimeInterface $thursdayStartCm): self
    {
        $this->thursdayStartCm = $thursdayStartCm;

        return $this;
    }

    public function getThursdayEndCm(): ?\DateTimeInterface
    {
        return $this->thursdayEndCm;
    }

    public function setThursdayEndCm(?\DateTimeInterface $thursdayEndCm): self
    {
        $this->thursdayEndCm = $thursdayEndCm;

        return $this;
    }

    public function getFridayStartCm(): ?\DateTimeInterface
    {
        return $this->fridayStartCm;
    }

    public function setFridayStartCm(?\DateTimeInterface $fridayStartCm): self
    {
        $this->fridayStartCm = $fridayStartCm;

        return $this;
    }

    public function getFridayEndCm(): ?\DateTimeInterface
    {
        return $this->fridayEndCm;
    }

    public function setFridayEndCm(?\DateTimeInterface $fridayEndCm): self
    {
        $this->fridayEndCm = $fridayEndCm;

        return $this;
    }

    public function getSaturdayStartCm(): ?\DateTimeInterface
    {
        return $this->saturdayStartCm;
    }

    public function setSaturdayStartCm(?\DateTimeInterface $saturdayStartCm): self
    {
        $this->saturdayStartCm = $saturdayStartCm;

        return $this;
    }

    public function getSaturdayEndCm(): ?\DateTimeInterface
    {
        return $this->saturdayEndCm;
    }

    public function setSaturdayEndCm(?\DateTimeInterface $saturdayEndCm): self
    {
        $this->saturdayEndCm = $saturdayEndCm;

        return $this;
    }

    public function getSundayStartCm(): ?\DateTimeInterface
    {
        return $this->sundayStartCm;
    }

    public function setSundayStartCm(?\DateTimeInterface $sundayStartCm): self
    {
        $this->sundayStartCm = $sundayStartCm;

        return $this;
    }

    public function getSundayEndCm(): ?\DateTimeInterface
    {
        return $this->sundayEndCm;
    }

    public function setSundayEndCm(?\DateTimeInterface $sundayEndCm): self
    {
        $this->sundayEndCm = $sundayEndCm;

        return $this;
    }

    public function isMondayCm(): ?bool
    {
        return $this->mondayCm;
    }

    public function setMondayCm(?bool $mondayCm): self
    {
        $this->mondayCm = $mondayCm;

        return $this;
    }

    public function isTuesdayCm(): ?bool
    {
        return $this->tuesdayCm;
    }

    public function setTuesdayCm(?bool $tuesdayCm): self
    {
        $this->tuesdayCm = $tuesdayCm;

        return $this;
    }

    public function isWednesdayCm(): ?bool
    {
        return $this->wednesdayCm;
    }

    public function setWednesdayCm(?bool $wednesdayCm): self
    {
        $this->wednesdayCm = $wednesdayCm;

        return $this;
    }

    public function isThursdayCm(): ?bool
    {
        return $this->thursdayCm;
    }

    public function setThursdayCm(?bool $thursdayCm): self
    {
        $this->thursdayCm = $thursdayCm;

        return $this;
    }

    public function isFridayCm(): ?bool
    {
        return $this->fridayCm;
    }

    public function setFridayCm(?bool $fridayCm): self
    {
        $this->fridayCm = $fridayCm;

        return $this;
    }

    public function isSaturdayCm(): ?bool
    {
        return $this->saturdayCm;
    }

    public function setSaturdayCm(?bool $saturdayCm): self
    {
        $this->saturdayCm = $saturdayCm;

        return $this;
    }

    public function isSundayCm(): ?bool
    {
        return $this->sundayCm;
    }

    public function setSundayCm(?bool $sundayCm): self
    {
        $this->sundayCm = $sundayCm;

        return $this;
    }
// ***********************************************************************************************************************************

public function getMondayStartTp(): ?\DateTimeInterface
{
    return $this->mondayStartTp;
}

public function setMondayStartTp(?\DateTimeInterface $mondayStartTp): self
{
    $this->mondayStartTp = $mondayStartTp;

    return $this;
}

public function getMondayEndTp(): ?\DateTimeInterface
{
    return $this->mondayEndTp;
}

public function setMondayEndTp(?\DateTimeInterface $mondayEndTp): self
{
    $this->mondayEndTp = $mondayEndTp;

    return $this;
}

public function getTuesdayStartTp(): ?\DateTimeInterface
{
    return $this->tuesdayStartTp;
}

public function setTuesdayStartTp(?\DateTimeInterface $tuesdayStartTp): self
{
    $this->tuesdayStartTp = $tuesdayStartTp;

    return $this;
}

public function getTuesdayEndTp(): ?\DateTimeInterface
{
    return $this->tuesdayEndTp;
}

public function setTuesdayEndTp(?\DateTimeInterface $tuesdayEndTp): self
{
    $this->tuesdayEndTp = $tuesdayEndTp;

    return $this;
}


public function getWednesdayStartTp(): ?\DateTimeInterface
{
    return $this->wednesdayStartTp;
}

public function setWednesdayStartTp(?\DateTimeInterface $wednesdayStartTp): self
{
    $this->wednesdayStartTp = $wednesdayStartTp;

    return $this;
}

public function getWednesdayEndTp(): ?\DateTimeInterface
{
    return $this->wednesdayEndTp;
}

public function setWednesdayEndTp(?\DateTimeInterface $wednesdayEndTp): self
{
    $this->wednesdayEndTp = $wednesdayEndTp;

    return $this;
}

public function getThursdayStartTp(): ?\DateTimeInterface
{
    return $this->thursdayStartTp;
}

public function setThursdayStartTp(?\DateTimeInterface $thursdayStartTp): self
{
    $this->thursdayStartTp = $thursdayStartTp;

    return $this;
}

public function getThursdayEndTp(): ?\DateTimeInterface
{
    return $this->thursdayEndTp;
}

public function setThursdayEndTp(?\DateTimeInterface $thursdayEndTp): self
{
    $this->thursdayEndTp = $thursdayEndTp;

    return $this;
}

public function getFridayStartTp(): ?\DateTimeInterface
{
    return $this->fridayStartTp;
}

public function setFridayStartTp(?\DateTimeInterface $fridayStartTp): self
{
    $this->fridayStartTp = $fridayStartTp;

    return $this;
}

public function getFridayEndTp(): ?\DateTimeInterface
{
    return $this->fridayEndTp;
}

public function setFridayEndTp(?\DateTimeInterface $fridayEndTp): self
{
    $this->fridayEndTp = $fridayEndTp;

    return $this;
}

public function getSaturdayStartTp(): ?\DateTimeInterface
{
    return $this->saturdayStartTp;
}

public function setSaturdayStartTp(?\DateTimeInterface $saturdayStartTp): self
{
    $this->saturdayStartTp = $saturdayStartTp;

    return $this;
}

public function getSaturdayEndTp(): ?\DateTimeInterface
{
    return $this->saturdayEndTp;
}

public function setSaturdayEndTp(?\DateTimeInterface $saturdayEndTp): self
{
    $this->saturdayEndTp = $saturdayEndTp;

    return $this;
}

public function getSundayStartTp(): ?\DateTimeInterface
{
    return $this->sundayStartTp;
}

public function setSundayStartTp(?\DateTimeInterface $sundayStartTp): self
{
    $this->sundayStartTp = $sundayStartTp;

    return $this;
}

public function getSundayEndTp(): ?\DateTimeInterface
{
    return $this->sundayEndTp;
}

public function setSundayEndTp(?\DateTimeInterface $sundayEndTp): self
{
    $this->sundayEndTp = $sundayEndTp;

    return $this;
}

public function isMondayTp(): ?bool
{
    return $this->mondayTp;
}

public function setMondayTp(?bool $mondayTp): self
{
    $this->mondayTp = $mondayTp;

    return $this;
}

public function isTuesdayTp(): ?bool
{
    return $this->tuesdayTp;
}

public function setTuesdayTp(?bool $tuesdayTp): self
{
    $this->tuesdayTp = $tuesdayTp;

    return $this;
}

public function isWednesdayTp(): ?bool
{
    return $this->wednesdayTp;
}

public function setWednesdayTp(?bool $wednesdayTp): self
{
    $this->wednesdayTp = $wednesdayTp;

    return $this;
}

public function isThursdayTp(): ?bool
{
    return $this->thursdayTp;
}

public function setThursdayTp(?bool $thursdayTp): self
{
    $this->thursdayTp = $thursdayTp;

    return $this;
}

public function isFridayTp(): ?bool
{
    return $this->fridayTp;
}

public function setFridayTp(?bool $fridayTp): self
{
    $this->fridayTp = $fridayTp;

    return $this;
}

public function isSaturdayTp(): ?bool
{
    return $this->saturdayTp;
}

public function setSaturdayTp(?bool $saturdayTp): self
{
    $this->saturdayTp = $saturdayTp;

    return $this;
}

public function isSundayTp(): ?bool
{
    return $this->sundayTp;
}

public function setSundayTp(?bool $sundayTp): self
{
    $this->sundayTp = $sundayTp;

    return $this;
}

// ***********************************************************************************************************************************

public function getMondayStartTd(): ?\DateTimeInterface
{
    return $this->mondayStartTd;
}

public function setMondayStartTd(?\DateTimeInterface $mondayStartTd): self
{
    $this->mondayStartTd = $mondayStartTd;

    return $this;
}

public function getMondayEndTd(): ?\DateTimeInterface
{
    return $this->mondayEndTd;
}

public function setMondayEndTd(?\DateTimeInterface $mondayEndTd): self
{
    $this->mondayEndTd = $mondayEndTd;

    return $this;
}

public function getTuesdayStartTd(): ?\DateTimeInterface
{
    return $this->tuesdayStartTd;
}

public function setTuesdayStartTd(?\DateTimeInterface $tuesdayStartTd): self
{
    $this->tuesdayStartTd = $tuesdayStartTd;

    return $this;
}

public function getTuesdayEndTd(): ?\DateTimeInterface
{
    return $this->tuesdayEndTd;
}

public function setTuesdayEndTd(?\DateTimeInterface $tuesdayEndTd): self
{
    $this->tuesdayEndTd = $tuesdayEndTd;

    return $this;
}


public function getWednesdayStartTd(): ?\DateTimeInterface
{
    return $this->wednesdayStartTd;
}

public function setWednesdayStartTd(?\DateTimeInterface $wednesdayStartTd): self
{
    $this->wednesdayStartTd = $wednesdayStartTd;

    return $this;
}

public function getWednesdayEndTd(): ?\DateTimeInterface
{
    return $this->wednesdayEndTd;
}

public function setWednesdayEndTd(?\DateTimeInterface $wednesdayEndTd): self
{
    $this->wednesdayEndTd = $wednesdayEndTd;

    return $this;
}

public function getThursdayStartTd(): ?\DateTimeInterface
{
    return $this->thursdayStartTd;
}

public function setThursdayStartTd(?\DateTimeInterface $thursdayStartTd): self
{
    $this->thursdayStartTd = $thursdayStartTd;

    return $this;
}

public function getThursdayEndTd(): ?\DateTimeInterface
{
    return $this->thursdayEndTd;
}

public function setThursdayEndTd(?\DateTimeInterface $thursdayEndTd): self
{
    $this->thursdayEndTd = $thursdayEndTd;

    return $this;
}

public function getFridayStartTd(): ?\DateTimeInterface
{
    return $this->fridayStartTd;
}

public function setFridayStartTd(?\DateTimeInterface $fridayStartTd): self
{
    $this->fridayStartTd = $fridayStartTd;

    return $this;
}

public function getFridayEndTd(): ?\DateTimeInterface
{
    return $this->fridayEndTd;
}

public function setFridayEndTd(?\DateTimeInterface $fridayEndTd): self
{
    $this->fridayEndTd = $fridayEndTd;

    return $this;
}

public function getSaturdayStartTd(): ?\DateTimeInterface
{
    return $this->saturdayStartTd;
}

public function setSaturdayStartTd(?\DateTimeInterface $saturdayStartTd): self
{
    $this->saturdayStartTd = $saturdayStartTd;

    return $this;
}

public function getSaturdayEndTd(): ?\DateTimeInterface
{
    return $this->saturdayEndTd;
}

public function setSaturdayEndTd(?\DateTimeInterface $saturdayEndTd): self
{
    $this->saturdayEndTd = $saturdayEndTd;

    return $this;
}

public function getSundayStartTd(): ?\DateTimeInterface
{
    return $this->sundayStartTd;
}

public function setSundayStartTd(?\DateTimeInterface $sundayStartTd): self
{
    $this->sundayStartTd = $sundayStartTd;

    return $this;
}

public function getSundayEndTd(): ?\DateTimeInterface
{
    return $this->sundayEndTd;
}

public function setSundayEndTd(?\DateTimeInterface $sundayEndTd): self
{
    $this->sundayEndTd = $sundayEndTd;

    return $this;
}

public function isMondayTd(): ?bool
{
    return $this->mondayTd;
}

public function setMondayTd(?bool $mondayTd): self
{
    $this->mondayTd = $mondayTd;

    return $this;
}

public function isTuesdayTd(): ?bool
{
    return $this->tuesdayTd;
}

public function setTuesdayTd(?bool $tuesdayTd): self
{
    $this->tuesdayTd = $tuesdayTd;

    return $this;
}

public function isWednesdayTd(): ?bool
{
    return $this->wednesdayTd;
}

public function setWednesdayTd(?bool $wednesdayTd): self
{
    $this->wednesdayTd = $wednesdayTd;

    return $this;
}

public function isThursdayTd(): ?bool
{
    return $this->thursdayTd;
}

public function setThursdayTd(?bool $thursdayTd): self
{
    $this->thursdayTd = $thursdayTd;

    return $this;
}

public function isFridayTd(): ?bool
{
    return $this->fridayTd;
}

public function setFridayTd(?bool $fridayTd): self
{
    $this->fridayTd = $fridayTd;

    return $this;
}

public function isSaturdayTd(): ?bool
{
    return $this->saturdayTd;
}

public function setSaturdayTd(?bool $saturdayTd): self
{
    $this->saturdayTd = $saturdayTd;

    return $this;
}

public function isSundayTd(): ?bool
{
    return $this->sundayTd;
}

public function setSundayTd(?bool $sundayTd): self
{
    $this->sundayTd = $sundayTd;

    return $this;
}


    public function getNumberSubChap(): ?int
    {
        return $this->numberSubChap;
    }

    public function setNumberSubChap(?int $numberSubChap): self
    {
        $this->numberSubChap = $numberSubChap;

        return $this;
    }

    public function getTeacherCourse(): ?TeacherCourseRegistration
    {
        return $this->teacherCourse;
    }

    public function setTeacherCourse(?TeacherCourseRegistration $teacherCourse): self
    {
        $this->teacherCourse = $teacherCourse;

        return $this;
    }

    public function isIsSubjectObligatory(): ?bool
    {
        return $this->isSubjectObligatory;
    }

    public function setIsSubjectObligatory(?bool $isSubjectObligatory): ClassProgram
    {
        $this->isSubjectObligatory = $isSubjectObligatory;
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

    public function getIsChoiceStudCourseOpen(): ?bool
    {
        return $this->isChoiceStudCourseOpen;
    }

    public function setIsChoiceStudCourseOpen(?bool $isChoiceStudCourseOpen): ClassProgram
    {
        $this->isChoiceStudCourseOpen = $isChoiceStudCourseOpen;
        return $this;
    }

    public function getEvaluationPeriod(): ?EvaluationPeriod
    {
        return $this->evaluationPeriod;
    }

    public function setEvaluationPeriod(?EvaluationPeriod $evaluationPeriod): self
    {
        $this->evaluationPeriod = $evaluationPeriod;

        return $this;
    }

    public function getValidationBase(): ?float
    {
        return $this->validationBase;
    }

    public function setValidationBase(?float $validationBase): ClassProgram
    {
        $this->validationBase = $validationBase;
        return $this;
    }

    public function getBranch(): ?Branch
    {
        return $this->branch;
    }

    public function setBranch(?Branch $branch): self
    {
        $this->branch = $branch;

        return $this;
    }
}
