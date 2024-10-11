<?php

namespace App\Entity\School\Study\Program;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\CurrentTeacherCountCoursesCompletedController;
use App\Controller\CurrentTeacherCountCoursesController;
use App\Controller\CurrentTeacherCountCoursesInprogressController;
use App\Controller\School\Study\Program\TeacherCourseRegistrationAdminController;
use App\Controller\School\Study\Program\TeacherCourseRegistrationController;
use App\Controller\School\Study\Teacher\CurrentTeacherCoursesController;
use App\Entity\School\Study\Teacher\Teacher;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Study\Program\TeacherCourseRegistrationRepository;
use App\State\Processor\Global\SystemProcessor;
use App\State\Processor\School\Study\Program\DuplicateTeacherCourseRegistrationProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TeacherCourseRegistrationRepository::class)]
#[ORM\Table(name: 'school_teacher_course_registration')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/teacher-course-registration/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:TeacherCourseRegistration:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/teacher-course-registration-admin',
            controller: TeacherCourseRegistrationAdminController::class,
            normalizationContext: [
                'groups' => ['get:TeacherCourseRegistration:collection'],
            ],
        ),

        new GetCollection(
            uriTemplate: '/get/teacher-course-registration',
            controller: TeacherCourseRegistrationController::class,
            normalizationContext: [
                'groups' => ['get:TeacherCourseRegistration:collection'],
            ],
        ),

        new GetCollection(
            uriTemplate: '/get/current/teacher-course-registration',
            controller: CurrentTeacherCoursesController::class,
            normalizationContext: [
                'groups' => ['get:TeacherCourseRegistration:collection'],
            ],
        ),

        new GetCollection(
            uriTemplate: '/get/teacher-count-course',
            controller: CurrentTeacherCountCoursesController::class,
            normalizationContext: [
                'groups' => ['get:TeacherCourseRegistration:collection'],
            ],

        ),
        new GetCollection(
            uriTemplate: '/get/teacher-count-course-in-progress',
            controller: CurrentTeacherCountCoursesInprogressController::class,
            normalizationContext: [
                'groups' => ['get:TeacherCourseRegistration:collection'],
            ],

        ),
        new GetCollection(
            uriTemplate: '/get/teacher-count-course-completed',
            controller: CurrentTeacherCountCoursesCompletedController::class,
            normalizationContext: [
                'groups' => ['get:TeacherCourseRegistration:collection'],
            ],

        ),
        new Post(
            uriTemplate: '/create/teacher-course-registration',
            denormalizationContext: [
                'groups' => ['write:TeacherCourseRegistration'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/teacher-course-registration/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TeacherCourseRegistration'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/teacher-course-registration/{id}',
            requirements: ['id' => '\d+'],
        ),

        //Duplicate teacher course registration begin
        new Delete(
            uriTemplate: '/teacher-course-registration/duplicate/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TeacherCourseRegistration'],
            ],
            processor: DuplicateTeacherCourseRegistrationProcessor::class,

        ),

    ]
)]
class TeacherCourseRegistration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:TeacherCourseRegistration:collection','write:TeacherCourseRegistration','get:StudentAttendance:collection', 'get:HomeWork:collection', 'get:HomeWork:item', 'get:ClassProgram:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:TeacherCourseRegistration:collection','write:TeacherCourseRegistration','get:StudentAttendance:collection'])]
    private ?Teacher $teacher = null;

    #[ORM\ManyToOne]
    #[Groups(['get:TeacherCourseRegistration:collection','write:TeacherCourseRegistration','get:StudentAttendance:collection', 'get:HomeWork:collection', 'get:HomeWork:item', 'get:ClassProgram:collection'])]
    private ?ClassProgram $course = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TeacherCourseRegistration:collection','write:TeacherCourseRegistration'])]
    private ?int $hourlyRateVolume = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TeacherCourseRegistration:collection','write:TeacherCourseRegistration'])]
    private ?float $hourlyRateExhausted = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TeacherCourseRegistration:collection','write:TeacherCourseRegistration'])]
    private ?float $hourlyRateNotExhausted = null;

    #[ORM\Column(length: 50)]
    #[Groups(['get:TeacherCourseRegistration:collection','write:TeacherCourseRegistration'])]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TeacherCourseRegistration:collection','write:TeacherCourseRegistration'])]
    private ?bool $isValidated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:TeacherCourseRegistration:collection','write:TeacherCourseRegistration'])]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[Groups(['get:TeacherCourseRegistration:collection'])]
    private ?Year $year = null;

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
        $this->isValidated = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): self
    {
        $this->teacher = $teacher;

        return $this;
    }

    public function getHourlyRateVolume(): ?int
    {
        return $this->hourlyRateVolume;
    }

    public function setHourlyRateVolume(int $hourlyRateVolume): self
    {
        $this->hourlyRateVolume = $hourlyRateVolume;

        return $this;
    }

    public function getCourse(): ?ClassProgram
    {
        return $this->course;
    }

    public function setCourse(?ClassProgram $course): self
    {
        $this->course = $course;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getHourlyRateExhausted(): ?float
    {
        return $this->hourlyRateExhausted;
    }

    public function setHourlyRateExhausted(?float $hourlyRateExhausted): self
    {
        $this->hourlyRateExhausted = $hourlyRateExhausted;

        return $this;
    }

    public function getHourlyRateNotExhausted(): ?float
    {
        return $this->hourlyRateNotExhausted;
    }

    public function setHourlyRateNotExhausted(?float $hourlyRateNotExhausted): self
    {
        $this->hourlyRateNotExhausted = $hourlyRateNotExhausted;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
    public function isIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): self
    {
        $this->isValidated = $isValidated;

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

}
