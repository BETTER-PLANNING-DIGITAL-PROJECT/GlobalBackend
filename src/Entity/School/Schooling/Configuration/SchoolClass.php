<?php

namespace App\Entity\School\Schooling\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Billing\Sale\GetRegistrationByCustomerUsingClassController;
use App\Controller\Billing\Sale\SchoolFeesClassController;
use App\Controller\DeleteSelectedResourceController;
use App\Controller\School\Schooling\Configuration\GetSchoolClassController;
use App\Controller\School\Schooling\Configuration\GetStudentFeeByClassController;
use App\Controller\School\Schooling\Configuration\ImportClassController;
use App\Controller\School\Schooling\Configuration\PostSchoolClassController;
use App\Controller\School\Schooling\Configuration\PutSchoolClassController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SchoolClassRepository::class)]
#[ORM\Table(name: 'school_class')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/class/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Class:collection'],
            ],
        ),

        new Get(
            uriTemplate: '/get/student/registration/by/class/{id}',
            requirements: ['id' => '\d+'],
            controller: GetRegistrationByCustomerUsingClassController::class,
            normalizationContext: [
                'groups' => ['get:Class:collection'],
            ]
        ),

        new GetCollection(
            uriTemplate: '/get/class-schools',
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:Class:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/class',
            controller: GetSchoolClassController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:Class:collection'],
            ],
        ),

        new GetCollection(
            uriTemplate: '/get/class/student/fee',
            controller: GetStudentFeeByClassController::class,
            normalizationContext: [
                'groups' => ['get:Class:collection'],
            ]
        ),

        new Get(
            uriTemplate: '/get/fee/by/class/{id}',
            requirements: ['id' => '\d+'],
            controller: SchoolFeesClassController::class,
            normalizationContext: [
                'groups' => ['get:Class:collection']
            ]
        ),
        new Post(
            uriTemplate: '/create/class',
            controller: PostSchoolClassController::class,
            denormalizationContext: [
                'groups' => ['write:Class'],
            ],
        ),
        new Post(
            uriTemplate: '/import/class',
            controller: ImportClassController::class,
            openapiContext: [
                "summary" => "Add multiple Class resources.",
            ],
            denormalizationContext: [
                'groups' => ['write:Class']
            ],
        ),
        new Put(
            uriTemplate: '/edit/class/{id}',
            requirements: ['id' => '\d+'],
            controller: PutSchoolClassController::class,
            denormalizationContext: [
                'groups' => ['write:Class'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/class/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/class',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
class SchoolClass
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Class:collection','get:ClassYearlyQuota:collection','get:StudentCourseRegistration:collection','get:ClassProgram:collection','get:StudentInternship:collection','get:StudentRegistration:collection','get:ClassWeighting:collection', 'get:StudentOnline:collection', 'get:StudentOnline:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?Year $year = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:Fee:collection','get:Class:collection', 'write:Class','get:ClassYearlyQuota:collection', 'get:StudentCourseRegistration:collection', 'get:ClassProgram:collection', 'get:StudentInternship:collection', 'get:StudentRegistration:collection', 'get:StudentCourseRegistration:collection','get:ClassWeighting:collection', 'get:StudentOnline:collection', 'get:StudentOnline:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?Guardianship $guardianship = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Class:collection', 'write:Class','get:StudentRegistration:collection'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?Department $department = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?ClassCategory $classCategory = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?Speciality $speciality = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?Level $level = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?TrainingType $trainingType = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?Room $mainRoom = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?int $maximumStudentNumber = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?bool $isOptional = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?Option $registrantOption = null;

    #[ORM\Column(length: 100)]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?string $classExam = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?int $ageLimit = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?float $simpleHourlyRate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?float $multipleHourlyRate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Class:collection', 'write:Class'])]
    private ?string $nextClass = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Class:collection','get:StudentCourseRegistration:collection','write:StudentCourseRegistration'])]
    private ?bool $isChoiceStudentCourse = null;

    #[ORM\ManyToOne]
    private ?User $user = null;
    
    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Class:collection','get:StudentCourseRegistration:collection','write:StudentCourseRegistration'])]
    private ?bool $isPaymentFee = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->isOptional = false;

        $this->is_enable = true;
        $this->isChoiceStudentCourse = false;
        $this->isPaymentFee = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getGuardianship(): ?Guardianship
    {
        return $this->guardianship;
    }

    public function setGuardianship(?Guardianship $guardianship): static
    {
        $this->guardianship = $guardianship;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): static
    {
        $this->department = $department;

        return $this;
    }

    public function getClassCategory(): ?ClassCategory
    {
        return $this->classCategory;
    }

    public function setClassCategory(?ClassCategory $classCategory): static
    {
        $this->classCategory = $classCategory;

        return $this;
    }

    public function getSpeciality(): ?Speciality
    {
        return $this->speciality;
    }

    public function setSpeciality(?Speciality $speciality): static
    {
        $this->speciality = $speciality;

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getTrainingType(): ?TrainingType
    {
        return $this->trainingType;
    }

    public function setTrainingType(?TrainingType $trainingType): static
    {
        $this->trainingType = $trainingType;

        return $this;
    }

    public function getMainRoom(): ?Room
    {
        return $this->mainRoom;
    }

    public function setMainRoom(?Room $mainRoom): static
    {
        $this->mainRoom = $mainRoom;

        return $this;
    }

    public function getMaximumStudentNumber(): ?int
    {
        return $this->maximumStudentNumber;
    }

    public function setMaximumStudentNumber(int $maximumStudentNumber): static
    {
        $this->maximumStudentNumber = $maximumStudentNumber;

        return $this;
    }

    public function getRegistrantOption(): ?Option
    {
        return $this->registrantOption;
    }

    public function setRegistrantOption(?Option $registrantOption): static
    {
        $this->registrantOption = $registrantOption;

        return $this;
    }

    public function getClassExam(): ?string
    {
        return $this->classExam;
    }

    public function setClassExam(string $classExam): static
    {
        $this->classExam = $classExam;

        return $this;
    }

    public function getAgeLimit(): ?int
    {
        return $this->ageLimit;
    }

    public function setAgeLimit(?int $ageLimit): static
    {
        $this->ageLimit = $ageLimit;

        return $this;
    }

    public function getSimpleHourlyRate(): ?float
    {
        return $this->simpleHourlyRate;
    }

    public function setSimpleHourlyRate(?float $simpleHourlyRate): static
    {
        $this->simpleHourlyRate = $simpleHourlyRate;

        return $this;
    }

    public function getMultipleHourlyRate(): ?float
    {
        return $this->multipleHourlyRate;
    }

    public function setMultipleHourlyRate(?float $multipleHourlyRate): static
    {
        $this->multipleHourlyRate = $multipleHourlyRate;

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

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): static
    {
        $this->institution = $institution;

        return $this;
    }

    public function isIsOptional(): ?bool
    {
        return $this->isOptional;
    }

    public function setIsOptional(?bool $isOptional): self
    {
        $this->isOptional = $isOptional;

        return $this;
    }

    public function getNextClass(): ?string
    {
        return $this->nextClass;
    }

    public function setNextClass(?string $nextClass): self
    {
        $this->nextClass = $nextClass;

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

    public function getIsChoiceStudentCourse(): ?bool
    {
        return $this->isChoiceStudentCourse;
    }

    public function setIsChoiceStudentCourse(?bool $isChoiceStudentCourse): SchoolClass
    {
        $this->isChoiceStudentCourse = $isChoiceStudentCourse;
        return $this;
    }

    public function isIsPaymentFee(): ?bool
    {
        return $this->isPaymentFee;
    }

    public function setIsPaymentFee(?bool $isPaymentFee): self
    {
        $this->isPaymentFee = $isPaymentFee;

        return $this;
    }

}
