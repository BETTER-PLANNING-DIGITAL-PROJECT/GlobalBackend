<?php

namespace App\Entity\Security\Session;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Security\SetUserCurrentYearController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\User;
use App\Repository\Security\Session\YearRepository;
use App\State\Processor\Global\SystemProcessor;
use App\State\Provider\Security\UserCurrentYearProvider;
use App\State\Provider\Security\UserYearLessThanCurrentYearProvider;
use App\State\Security\SetCurrentYearProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: YearRepository::class)]
#[ORM\Table(name: 'security_year')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/year',
            normalizationContext: [
                'groups' => ['get:Year:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Post(
            uriTemplate: '/create/year',
            denormalizationContext: [
                'groups' => ['write:Year'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/year/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Year'],
            ]
        ),
        new Delete(
            uriTemplate: '/delete/year/{id}',
            requirements: ['id' => '\d+'],
        ),
        /* others */
        new Delete(
            uriTemplate: '/set/user/current/year/{id}',
            requirements: ['id' => '\d+'],
            controller: SetUserCurrentYearController::class
        ),
        new Delete(
            uriTemplate: '/set/current/year/{id}',
            requirements: ['id' => '\d+'],
            processor: SetCurrentYearProcessor::class
        ),
//        new GetCollection(
//            uriTemplate: '/year/get/stud-course-reg/{matricule}',
//            uriVariables: [
//                'matricule'=>String_::class
//            ],
//            normalizationContext: [
//                'groups' => ['get:Year:collection'],
//                'datetime_format'=> 'Y-m-d'
//            ],
//            provider: SchoolYearStudCourseRegProvider::class
//        ),
        new GetCollection(
            uriTemplate: '/year/get/stud-course-reg',
            normalizationContext: [
                'groups' => ['get:Year:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
            provider: UserCurrentYearProvider::class
        ),
        new GetCollection(
            uriTemplate: '/get/user/current/year',
            normalizationContext: [
                'groups' => ['get:Year:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
            provider: UserCurrentYearProvider::class
        ),
        new GetCollection(
            uriTemplate: '/get/years/less/current/year',
            normalizationContext: [
                'groups' => ['get:Year:collection'],
            ],
            provider: UserYearLessThanCurrentYearProvider::class
        ),
    ]
)]
#[UniqueEntity(
    fields: ['year'],
    message: 'This year already exist.',
)]
class Year
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Fee:collection', 'get:Year:collection','get:Class:collection','get:Concour:collection','get:StudentCourseRegistration:collection','get:ClassProgram:collection','get:FormulaTh:collection','get:User:item','get:User:collection','get:NationalExam:collection','get:Diplomamention:collection','get:CycleYearlyQuota:collection','get:ClassYearlyQuota:collection','get:SpecialityYearlyQuota:collection','get:Sequence:collection','get:SchoolWeighting:collection','get:ClassWeighting:collection','get:SpecialityWeighting:collection','get:CycleWeighting:collection','get:StudentRegistration:collection','get:StudentInternship:collection','get:Graduation:collection','get:Certification:collection','get:ResearchWork:collection','get:EvaluationPeriod:collection','get:SchoolPeriod:collection', 'get:Student:collection', 'get:Teacher:collection', 'get:TeacherYearlyQuota:collection', 'get:TimeTablePeriod:collection', 'get:User:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Groups(['get:Fee:collection','get:Year:collection', 'write:Year','get:Class:collection','get:StudentCourseRegistration:collection','get:User:collection','get:ClassProgram:collection','get:FormulaTh:collection','get:User:item','get:Concour:collection','get:Diplomamention:collection','get:ClassYearlyQuota:collection','get:NationalExam:collection','get:CycleYearlyQuota:collection','get:SpecialityYearlyQuota:collection','get:SchoolWeighting:collection','get:ClassWeighting:collection','get:SpecialityWeighting:collection','get:CycleWeighting:collection','get:Sequence:collection','get:StudentRegistration:collection','get:StudentInternship:collection','get:Graduation:collection','get:Certification:collection','get:ResearchWork:collection','get:EvaluationPeriod:collection','get:SchoolPeriod:collection', 'get:Student:collection', 'get:Teacher:collection', 'get:TeacherYearlyQuota:collection', 'get:TimeTablePeriod:collection', 'get:User:collection', 'get:MarkGrade:collection'])]
    private ?string $year = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(['get:Year:collection', 'write:Year'])]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(['get:Year:collection', 'write:Year'])]
    private ?\DateTimeImmutable $endAt = null;

    // #[ORM\ManyToOne(targetEntity: self::class)]
    // #[Groups(['get:Year:collection', 'write:Year'])]
    // private ?self $lastYear = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:Year:collection', 'write:Year'])]
    private ?string $objective = null;

    #[ORM\Column]
    #[Groups(['get:Year:collection', 'write:Year'])]
    private ?bool $isCurrent = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Year:collection'])]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    #[Groups(['get:Year:collection', 'write:Year'])]
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

        $this->isCurrent = false;

        $this->is_enable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(string $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeImmutable $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeImmutable $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    // public function getLastYear(): ?self
    // {
    //     return $this->lastYear;
    // }

    // public function setLastYear(?self $lastYear): static
    // {
    //     $this->lastYear = $lastYear;

    //     return $this;
    // }

    public function getObjective(): ?string
    {
        return $this->objective;
    }

    public function setObjective(?string $objective): static
    {
        $this->objective = $objective;

        return $this;
    }

    public function isIsCurrent(): ?bool
    {
        return $this->isCurrent;
    }

    public function setIsCurrent(bool $isCurrent): static
    {
        $this->isCurrent = $isCurrent;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

}
