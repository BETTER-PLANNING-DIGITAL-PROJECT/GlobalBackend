<?php

namespace App\Entity\School\Study\Program;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Entity\School\Study\Teacher\Teacher;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Study\Program\TeacherYearlyQuotaRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TeacherYearlyQuotaRepository::class)]
#[ORM\Table(name: 'school_teacher_yearly_quota')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/teacher-yearly-quota/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:TeacherYearlyQuota:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/teacher-yearly-quota',
            normalizationContext: [
                'groups' => ['get:TeacherYearlyQuota:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/teacher-yearly-quota',
            denormalizationContext: [
                'groups' => ['write:TeacherYearlyQuota'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/teacher-yearly-quota/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TeacherYearlyQuota'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/teacher-yearly-quota/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/teacher-yearly-quota',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['teacher', 'year'],
    message: 'this year already exist',
    errorPath: 'teacher'
)]
class TeacherYearlyQuota
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:TeacherYearlyQuota:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:TeacherYearlyQuota:collection','write:TeacherYearlyQuota'])]
    private ?Teacher $teacher = null;

    #[ORM\ManyToOne]
    #[Groups(['get:TeacherYearlyQuota:collection', 'write:TeacherYearlyQuota'])]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    #[Groups(['get:TeacherYearlyQuota:collection', 'write:TeacherYearlyQuota'])]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TeacherYearlyQuota:collection', 'write:TeacherYearlyQuota'])]
    private ?int $minAnnualQuota = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TeacherYearlyQuota:collection', 'write:TeacherYearlyQuota'])]
    private ?int $maxAnnualQuota = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TeacherYearlyQuota:collection', 'write:TeacherYearlyQuota'])]
    private ?int $minMonthlyQuota = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TeacherYearlyQuota:collection', 'write:TeacherYearlyQuota'])]
    private ?int $maxMonthlyQuota = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TeacherYearlyQuota:collection', 'write:TeacherYearlyQuota'])]
    private ?int $minWeeklyQuota = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TeacherYearlyQuota:collection', 'write:TeacherYearlyQuota'])]
    private ?int $maxWeeklyQuota = null;

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
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
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

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getMinAnnualQuota(): ?int
    {
        return $this->minAnnualQuota;
    }

    public function setMinAnnualQuota(?int $minAnnualQuota): self
    {
        $this->minAnnualQuota = $minAnnualQuota;

        return $this;
    }

    public function getMaxAnnualQuota(): ?int
    {
        return $this->maxAnnualQuota;
    }

    public function setMaxAnnualQuota(?int $maxAnnualQuota): self
    {
        $this->maxAnnualQuota = $maxAnnualQuota;

        return $this;
    }

    public function getMinMonthlyQuota(): ?int
    {
        return $this->minMonthlyQuota;
    }

    public function setMinMonthlyQuota(?int $minMonthlyQuota): self
    {
        $this->minMonthlyQuota = $minMonthlyQuota;

        return $this;
    }

    public function getMaxMonthlyQuota(): ?int
    {
        return $this->maxMonthlyQuota;
    }

    public function setMaxMonthlyQuota(?int $maxMonthlyQuota): self
    {
        $this->maxMonthlyQuota = $maxMonthlyQuota;

        return $this;
    }

    public function getMinWeeklyQuota(): ?int
    {
        return $this->minWeeklyQuota;
    }

    public function setMinWeeklyQuota(?int $minWeeklyQuota): self
    {
        $this->minWeeklyQuota = $minWeeklyQuota;

        return $this;
    }

    public function getMaxWeeklyQuota(): ?int
    {
        return $this->maxWeeklyQuota;
    }

    public function setMaxWeeklyQuota(?int $maxWeeklyQuota): self
    {
        $this->maxWeeklyQuota = $maxWeeklyQuota;

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

}
