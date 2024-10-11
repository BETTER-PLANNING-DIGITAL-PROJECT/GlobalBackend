<?php

namespace App\Entity\School\Schooling\Registration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Controller\ImportationControllers\StudentRegistrationImportationController;
use App\Controller\School\Schooling\Registration\DeleteNewStudentRegistrationController;
use App\Controller\School\Schooling\Registration\UpdateRegistrationClassController;
use App\Entity\School\Schooling\Configuration\Cycle;
use App\Entity\School\Schooling\Configuration\Level;
use App\Entity\School\Schooling\Configuration\Option;
use App\Entity\School\Schooling\Configuration\Regime;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\School\Schooling\Configuration\SchoolClass;
use App\Entity\School\Schooling\Configuration\Speciality;
use App\Entity\School\Schooling\Discipline\AbsencePermit;
use App\Entity\School\Schooling\Discipline\ConsignmentDay;
use App\Entity\School\Schooling\Discipline\ConsignmentHour;
use App\Entity\School\Schooling\Discipline\LateComing;
use App\Entity\School\Schooling\Discipline\StudentFollowUp;
use App\Entity\School\Schooling\Discipline\SummonParent;
use App\Entity\School\Schooling\Discipline\Suspension;
use App\Entity\School\Schooling\Discipline\SuspensionHour;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\School\Diploma;
use App\Entity\Setting\School\Repeating;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\State\MatriculeEditState;
use App\State\Processor\School\Schooling\Registration\DismissalStudentRegistrationProcessor;
use App\State\Processor\School\Schooling\Registration\PostNewStudentRegistrationProcessor;
use App\State\Processor\School\Schooling\Registration\PostOldStudentRegistrationPerClassProcessor;
use App\State\Processor\School\Schooling\Registration\PostOldStudentRegistrationProcessor;
use App\State\Processor\School\Schooling\Registration\PutNewStudentRegistrationProcessor;
use App\State\Processor\School\Schooling\Registration\PutOldStudentRegistrationProcessor;
use App\State\Processor\School\Schooling\Registration\ReadmissionStudentRegistrationProcessor;
use App\State\Processor\School\Schooling\Registration\ResignationStudentRegistrationProcessor;
use App\State\Provider\School\Schooling\Registration\NewStudentRegistrationProvider;
use App\State\Provider\School\Schooling\Registration\OldStudentRegistrationProvider;
use App\State\Provider\School\Schooling\Registration\StudentCurrentClassProvider;
use App\State\Provider\School\Schooling\Registration\StudentRegistrationForDismissalProvider;
use App\State\Provider\School\Schooling\Registration\StudentRegistrationForResignationProvider;
use App\State\Registration3Provider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\State\StudregistrationStudCourseRegProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StudentRegistrationRepository::class)]
#[ORM\Table(name: 'school_student_registration')]
#[ApiResource(
    operations:[

        new Get(
            uriTemplate: '/get/student/registration/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:StudentRegistration:collection']
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/student/registration',
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => [ 'get:StudentRegistration:collection']
            ]
        ),
        new GetCollection(
            uriTemplate: '/get/student/registration/current/class',
            normalizationContext: [
                'groups' => ['get:StudentRegistration:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
            provider: StudentCurrentClassProvider::class
        ),

        // New registration
        new Post(
            uriTemplate: '/create/new/student/registration',
            denormalizationContext: [
                'groups' => ['write:StudentRegistration'],
            ],
            processor: PostNewStudentRegistrationProcessor::class
        ),
        new Put(
            uriTemplate: '/edit/new/student/registration/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:StudentRegistration'],
            ],
            processor: PutNewStudentRegistrationProcessor::class
        ),
        new Delete(
            uriTemplate: '/delete/new/student/registration/{id}',
            requirements: ['id' => '\d+'],
            controller: DeleteNewStudentRegistrationController::class,
        ),
        // /New registration

        // Old registration
        new Get(
            uriTemplate: '/get/old/student/registration/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:StudentRegistration:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/old/student/registration',
            denormalizationContext: [
                'groups' => ['write:StudentRegistration'],
            ],
            processor: PostOldStudentRegistrationProcessor::class
        ),
        new Put(
            uriTemplate: '/edit/old/student/registration/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:StudentRegistration'],
            ],
            processor: PutOldStudentRegistrationProcessor::class
        ),

        // Old registration per class
        new Get(
            uriTemplate: '/get/old/student/registration/per/class/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:StudentRegistration:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/old/student/registration/per/class',
            denormalizationContext: [
                'groups' => ['write:StudentRegistration'],
            ],
            processor: PostOldStudentRegistrationPerClassProcessor::class
        ),
        // /Old registration

//        new Delete(
//            uriTemplate: '/delete/student/registration/{id}',
//            requirements: ['id' => '\d+'],
//            processor: StudentRegistrationDeleteProcessor::class
//        ),



        // Modification of matricule
        new Put(
            uriTemplate: '/edit/matricule/student/registration/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:StudentRegistration'],
            ],
            processor: MatriculeEditState::class
        ),

        // Modification of regime and option
        new Put(
            uriTemplate: '/edit/classe/regime/option/student/registration/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:StudentRegistration'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/class/student/registration/{id}',
            requirements: ['id' => '\d+'],
            controller: UpdateRegistrationClassController::class,
            denormalizationContext: [
                'groups' => ['write:StudentRegistration'],
            ],
        ),
        
        // Resignation
        new Post(
            uriTemplate: '/resignation/student/registration',
            denormalizationContext: [
                'groups' => ['write:StudentRegistration'],
            ],
            processor: ResignationStudentRegistrationProcessor::class
        ),
        
        // Dismissal
        new Post(
            uriTemplate: '/dismissal/student/registration',
            denormalizationContext: [
                'groups' => ['write:StudentRegistration'],
            ],
            processor: DismissalStudentRegistrationProcessor::class
        ),
        
        // Readmission
        new Post(
            uriTemplate: '/readmission/student/registration',
            denormalizationContext: [
                'groups' => ['write:StudentRegistration'],
            ],
            processor: ReadmissionStudentRegistrationProcessor::class
        ),






        new Get(
            uriTemplate: '/studregistration/get/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:StudentRegistration:item']
            ],
            provider: Registration3Provider::class
        ),

        // Start stud course registration
        new GetCollection(
            uriTemplate: '/studregistration/get/stud-course-reg',
            normalizationContext: [
                'groups' => [ 'get:StudentRegistration:collection']
            ],
            provider: StudregistrationStudCourseRegProvider::class

        ),
        // End stud course registration

        new Post(
            uriTemplate: '/import/student-registration',
            controller: StudentRegistrationImportationController::class,
            openapiContext: [
                "summary" => "Add multiple Student Registration resources.",

            ],
            denormalizationContext: [
                'groups' => ['post:Student Registration']
            ],
        ),


        // Delete
        new Delete(
            uriTemplate: '/delete/student/registration',
            controller: DeleteSelectedResourceController::class,
        ),

    ],
)]
#[GetCollection(uriTemplate: '/get/new/student/registration', provider: NewStudentRegistrationProvider::class)]
#[GetCollection(uriTemplate: '/get/old/student/registration', provider: OldStudentRegistrationProvider::class)]
#[GetCollection(uriTemplate: '/get/student/registration/for/dismissal', provider: StudentRegistrationForDismissalProvider::class)]
#[GetCollection(uriTemplate: '/get/student/registration/for/resignation', provider: StudentRegistrationForResignationProvider::class)]

class StudentRegistration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:StudentRegistration:collection', 'get:StudentCourseRegistration:collection', 'get:Customer:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentRegistration:collection', 'write:StudentRegistration'])]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[Groups(['get:StudentRegistration:collection', 'write:StudentRegistration'])]
    private ?Year $currentYear = null;

    #[ORM\ManyToOne]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration', 'get:StudentCourseRegistration:collection','get:Mark:collection', 'get:Customer:collection'])]
    private ?Student $student = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?string $center = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?string $pvdiplome = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?string $pvselection = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?Repeating $repeating = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?string $elementsprovided = null;

    #[ORM\Column(nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?float $average = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?string $region = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?\DateTimeImmutable $registrationdate = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?Option $options = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?string $transactions = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration', 'get:StudentCourseRegistration:collection'])]
    private ?self $studentRegistration = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?Diploma $diploma = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?Regime $regime = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?SchoolClass $classe = null;

    #[ORM\Column(length: 33,  nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?string $ranks = null;

    #[ORM\ManyToOne]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?SchoolClass $currentClass = null;

    #[ORM\ManyToOne]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?Cycle $cycle = null;
    
    #[ORM\ManyToOne]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?Level $level = null; 
    
    #[ORM\ManyToOne]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?Speciality $speciality = null;

    #[ORM\Column(length: 33, nullable: true)]
    #[Groups([ 'get:StudentRegistration:collection','write:StudentRegistration'])]
    private ?string $enrollIn = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Student:collection', 'write:Student', 'get:StudentRegistration:collection'])]
    private ?string $status = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_enable = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_archive = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: AbsencePermit::class, mappedBy: 'studentRegistrations')]
    #[ORM\JoinTable(name: 'school_student_registration_absence_permit')]
    private Collection $absencePermits;

    #[ORM\ManyToMany(targetEntity: ConsignmentDay::class, mappedBy: 'studentRegistrations')]
    #[ORM\JoinTable(name: 'school_consignment_day_student_registration')]
    private Collection $consignmentDays;

    #[ORM\ManyToMany(targetEntity: ConsignmentHour::class, mappedBy: 'studentRegistrations')]
    #[ORM\JoinTable(name: 'school_student_registration_consignment_hour')]
    private Collection $consignmentHours;

    #[ORM\ManyToMany(targetEntity: LateComing::class, mappedBy: 'studentRegistrations')]
    #[ORM\JoinTable(name: 'school_student_registration_late_coming')]
    private Collection $lateComings;

    #[ORM\ManyToMany(targetEntity: StudentFollowUp::class, mappedBy: 'studentRegistrations')]
    #[ORM\JoinTable(name: 'school_student_registration_student_follow_up')]
    private Collection $studentFollowUps;

    #[ORM\ManyToMany(targetEntity: SummonParent::class, mappedBy: 'studentRegistrations')]
    #[ORM\JoinTable(name: 'school_student_registration_summon_parent')]
    private Collection $summonParents;

    #[ORM\ManyToMany(targetEntity: Suspension::class, mappedBy: 'studentRegistrations')]
    #[ORM\JoinTable(name: 'school_student_registration_suspension')]
    private Collection $suspensions;

    #[ORM\ManyToMany(targetEntity: SuspensionHour::class, mappedBy: 'studentRegistrations')]
    #[ORM\JoinTable(name: 'school_student_registration_suspension_hour')]
    private Collection $suspensionHours;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
        $this->is_archive = false;

        $this->consignmentHours = new ArrayCollection();
        $this->consignmentDays = new ArrayCollection();
        $this->summonParents = new ArrayCollection();
        $this->absencePermits = new ArrayCollection();
        $this->lateComings = new ArrayCollection();
        $this->studentFollowUps = new ArrayCollection();
        $this->suspensions = new ArrayCollection();
        $this->suspensionHours = new ArrayCollection();

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

    public function getCenter(): ?string
    {
        return $this->center;
    }

    public function setCenter(string $center): self
    {
        $this->center = $center;

        return $this;
    }

    public function getPvdiplome(): ?string
    {
        return $this->pvdiplome;
    }

    public function setPvdiplome(string $pvdiplome): self
    {
        $this->pvdiplome = $pvdiplome;

        return $this;
    }

    public function getPvselection(): ?string
    {
        return $this->pvselection;
    }

    public function setPvselection(string $pvselection): self
    {
        $this->pvselection = $pvselection;

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


    public function getRegistrationdate(): ?\DateTimeImmutable
    {
        return $this->registrationdate;
    }

    public function setRegistrationdate(?\DateTimeImmutable $registrationdate): self
    {
        $this->registrationdate = $registrationdate;

        return $this;
    }

    public function getRepeating(): ?Repeating
    {
        return $this->repeating;
    }

    public function setRepeating(?Repeating $repeating): self
    {
        $this->repeating = $repeating;

        return $this;
    }

    public function getElementsprovided(): ?string
    {
        return $this->elementsprovided;
    }

    public function setElementsprovided(string $elementsprovided): self
    {
        $this->elementsprovided = $elementsprovided;

        return $this;
    }

    public function isIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(bool $is_enable): self
    {
        $this->is_enable = $is_enable;

        return $this;
    }

    public function isIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(bool $is_archive): self
    {
        $this->is_archive = $is_archive;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getOptions(): ?Option
    {
        return $this->options;
    }

    public function setOptions(?Option $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getTransactions(): ?string
    {
        return $this->transactions;
    }

    public function setTransactions(?string $transactions): self
    {
        $this->transactions = $transactions;

        return $this;
    }


    public function getStudentRegistration(): ?self
    {
        return $this->studentRegistration;
    }

    public function setStudentRegistration(?self $studentRegistration): self
    {
        $this->studentRegistration = $studentRegistration;

        return $this;
    }

    public function getDiploma(): ?Diploma
    {
        return $this->diploma;
    }

    public function setDiploma(?Diploma $diploma): self
    {
        $this->diploma = $diploma;

        return $this;
    }

    public function getRegime(): ?Regime
    {
        return $this->regime;
    }

    public function setRegime(?Regime $regime): self
    {
        $this->regime = $regime;

        return $this;
    }

    public function getClasse(): ?SchoolClass
    {
        return $this->classe;
    }

    public function setClasse(?SchoolClass $classe): self
    {
        $this->classe = $classe;

        return $this;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getRanks(): ?string
    {
        return $this->ranks;
    }

    public function setRanks(string $ranks): self
    {
        $this->ranks = $ranks;

        return $this;
    }

    public function getAverage(): ?float
    {
        return $this->average;
    }

    public function setAverage(float $average): self
    {
        $this->average = $average;

        return $this;
    }

    public function getCycle(): ?Cycle
    {
        return $this->cycle;
    }

    public function setCycle(?Cycle $cycle): self
    {
        $this->cycle = $cycle;

        return $this;
    } 
    
    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getSpeciality(): ?Speciality
    {
        return $this->speciality;
    }

    public function setSpeciality(?Speciality $speciality): self
    {
        $this->speciality = $speciality;

        return $this;
    }

    public function getEnrollIn(): ?string
    {
        return $this->enrollIn;
    }

    public function setEnrollIn(string $enrollIn): self
    {
        $this->enrollIn = $enrollIn;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegion(): ?string
    {
        return $this->region;
    }

    /**
     * @param string|null $region
     * @return StudentRegistration
     */
    public function setRegion(?string $region): StudentRegistration
    {
        $this->region = $region;
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

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getCurrentYear(): ?Year
    {
        return $this->currentYear;
    }

    public function setCurrentYear(?Year $currentYear): void
    {
        $this->currentYear = $currentYear;
    }

    public function getCurrentClass(): ?SchoolClass
    {
        return $this->currentClass;
    }

    public function setCurrentClass(?SchoolClass $currentClass): void
    {
        $this->currentClass = $currentClass;
    }

    /**
     * @return Collection<int, AbsencePermit>
     */
    public function getAbsencePermits(): Collection
    {
        return $this->absencePermits;
    }

    public function addAbsencePermit(AbsencePermit $absencePermit): self
    {
        if (!$this->absencePermits->contains($absencePermit)) {
            $this->absencePermits->add($absencePermit);
            $absencePermit->addStudentRegistration($this);
        }

        return $this;
    }

    public function removeAbsencePermit(AbsencePermit $absencePermit): self
    {
        if ($this->absencePermits->removeElement($absencePermit)) {
            $absencePermit->removeStudentRegistration($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, ConsignmentDay>
     */
    public function getConsignmentDays(): Collection
    {
        return $this->consignmentDays;
    }

    public function addConsignmentDay(ConsignmentDay $consignmentDay): self
    {
        if (!$this->consignmentDays->contains($consignmentDay)) {
            $this->consignmentDays->add($consignmentDay);
            $consignmentDay->addStudentRegistration($this);
        }

        return $this;
    }

    public function removeConsignmentDay(ConsignmentDay $consignmentDay): self
    {
        if ($this->consignmentDays->removeElement($consignmentDay)) {
            $consignmentDay->removeStudentRegistration($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, ConsignmentHour>
     */
    public function getConsignmentHours(): Collection
    {
        return $this->consignmentHours;
    }

    public function addConsignmentHour(ConsignmentHour $consignmentHour): self
    {
        if (!$this->consignmentHours->contains($consignmentHour)) {
            $this->consignmentHours->add($consignmentHour);
            $consignmentHour->addStudentRegistration($this);
        }

        return $this;
    }

    public function removeConsignmentHour(ConsignmentHour $consignmentHour): self
    {
        if ($this->consignmentHours->removeElement($consignmentHour)) {
            $consignmentHour->removeStudentRegistration($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, LateComing>
     */
    public function getLateComings(): Collection
    {
        return $this->lateComings;
    }

    public function addLateComing(LateComing $lateComing): self
    {
        if (!$this->lateComings->contains($lateComing)) {
            $this->lateComings->add($lateComing);
            $lateComing->addStudentRegistration($this);
        }

        return $this;
    }

    public function removeLateComing(LateComing $lateComing): self
    {
        if ($this->lateComings->removeElement($lateComing)) {
            $lateComing->removeStudentRegistration($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, StudentFollowUp>
     */
    public function getStudentFollowUps(): Collection
    {
        return $this->studentFollowUps;
    }

    public function addStudentFollowUp(StudentFollowUp $studentFollowUp): self
    {
        if (!$this->studentFollowUps->contains($studentFollowUp)) {
            $this->studentFollowUps->add($studentFollowUp);
            $studentFollowUp->addStudentRegistration($this);
        }

        return $this;
    }

    public function removeStudentFollowUp(StudentFollowUp $studentFollowUp): self
    {
        if ($this->studentFollowUps->removeElement($studentFollowUp)) {
            $studentFollowUp->removeStudentRegistration($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, SummonParent>
     */
    public function getSummonParents(): Collection
    {
        return $this->summonParents;
    }

    public function addSummonParent(SummonParent $summonParent): self
    {
        if (!$this->summonParents->contains($summonParent)) {
            $this->summonParents->add($summonParent);
            $summonParent->addStudentRegistration($this);
        }

        return $this;
    }

    public function removeSummonParent(SummonParent $summonParent): self
    {
        if ($this->summonParents->removeElement($summonParent)) {
            $summonParent->removeStudentRegistration($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Suspension>
     */
    public function getSuspensions(): Collection
    {
        return $this->suspensions;
    }

    public function addSuspension(Suspension $suspension): self
    {
        if (!$this->suspensions->contains($suspension)) {
            $this->suspensions->add($suspension);
            $suspension->addStudentRegistration($this);
        }

        return $this;
    }

    public function removeSuspension(Suspension $suspension): self
    {
        if ($this->suspensions->removeElement($suspension)) {
            $suspension->removeStudentRegistration($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, SuspensionHour>
     */
    public function getSuspensionHours(): Collection
    {
        return $this->suspensionHours;
    }

    public function addSuspensionHour(SuspensionHour $suspensionHour): self
    {
        if (!$this->suspensionHours->contains($suspensionHour)) {
            $this->suspensionHours->add($suspensionHour);
            $suspensionHour->addStudentRegistration($this);
        }

        return $this;
    }

    public function removeSuspensionHour(SuspensionHour $suspensionHour): self
    {
        if ($this->suspensionHours->removeElement($suspensionHour)) {
            $suspensionHour->removeStudentRegistration($this);
        }

        return $this;
    }


}
