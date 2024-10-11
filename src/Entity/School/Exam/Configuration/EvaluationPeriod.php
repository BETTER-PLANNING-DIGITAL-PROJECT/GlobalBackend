<?php

namespace App\Entity\School\Exam\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\School\PeriodType;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\State\Processor\Global\SystemProcessor;
use App\State\Provider\School\Exam\Configuration\EvaluationPeriodProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvaluationPeriodRepository::class)]
#[ORM\Table(name: 'school_evaluation_period')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/evaluation-period/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:EvaluationPeriod:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/evaluation-period',
            normalizationContext: [
                'groups' => ['get:EvaluationPeriod:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
            provider: EvaluationPeriodProvider::class
        ),
        new Post(
            uriTemplate: '/create/evaluation-period',
            denormalizationContext: [
                'groups' => ['write:EvaluationPeriod'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/evaluation-period/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:EvaluationPeriod'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/evaluation-period/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'this evaluation period already exist',
)]
class EvaluationPeriod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:EvaluationPeriod:collection','get:NationalExam:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:EvaluationPeriod:collection', 'write:EvaluationPeriod'])]
    private ?PeriodType $periodType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:EvaluationPeriod:collection', 'write:EvaluationPeriod'])]
    private ?int $number = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Name cannot be smaller than {{ limit }} characters',
        maxMessage: 'Name cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:EvaluationPeriod:collection', 'write:EvaluationPeriod','get:NationalExam:collection', 'get:ClassProgram:collection'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:EvaluationPeriod:collection', 'write:EvaluationPeriod'])]
    private ?\DateTimeImmutable $beginAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:EvaluationPeriod:collection', 'write:EvaluationPeriod'])]
    private ?\DateTimeImmutable $endAt = null;

    #[ORM\ManyToOne]
    #[Groups(['get:EvaluationPeriod:collection', 'write:EvaluationPeriod'])]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column]
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

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBeginAt(): ?\DateTimeImmutable
    {
        return $this->beginAt;
    }

    public function setBeginAt(?\DateTimeImmutable $beginAt): static
    {
        $this->beginAt = $beginAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeImmutable $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function isIsEnable(): ?bool
    {
        return $this->isEnable;
    }

    public function setIsEnable(bool $isEnable): static
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

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): static
    {
        $this->institution = $institution;

        return $this;
    }

    public function getPeriodType(): ?PeriodType
    {
        return $this->periodType;
    }

    public function setPeriodType(?PeriodType $periodType): static
    {
        $this->periodType = $periodType;

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
}
