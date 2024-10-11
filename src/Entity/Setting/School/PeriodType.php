<?php

namespace App\Entity\Setting\School;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\User;
use App\Repository\Setting\School\PeriodTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use App\State\Current\InstitutionProcessor;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PeriodTypeRepository::class)]
#[ORM\Table(name: 'setting_period_type')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/period-type/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:PeriodType:collection'],
            ],
        ),

        new GetCollection(
            uriTemplate: '/period-type/get',
            normalizationContext: [
                'groups' => ['get:PeriodType:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/period-type/create',
            denormalizationContext: [
                'groups' => ['write:PeriodType'],
            ],
            processor: InstitutionProcessor::class,
        ),
        new Put(
            uriTemplate: '/period-type/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:PeriodType'],
            ],
        ),
        new Delete(
            uriTemplate: '/period-type/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This period type already exist.',
)]
class PeriodType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:PeriodType:collection','get:SpecialityWeighting:collection','get:ClassProgram:collection','get:TimeTableModel:collection','get:TimeTableModelDay:collection','get:Institution:collection','get:Concour:collection','get:NationalExam:collection','get:ClassWeighting:collection','get:SchoolWeighting:collection','get:CycleWeighting:collection','get:EvaluationPeriod:collection','get:SchoolPeriod:collection','get:SchoolPeriod:collection', 'get:TimeTablePeriod:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:PeriodType:collection', 'write:PeriodType'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Name cannot be smaller than {{ limit }} characters',
        maxMessage: 'Name cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:PeriodType:collection', 'write:PeriodType','get:SpecialityWeighting:collection','get:ClassProgram:collection','get:TimeTableModel:collection','get:TimeTableModelDay:collection','get:Institution:collection','get:Concour:collection','get:NationalExam:collection','get:ClassWeighting:collection','get:SchoolWeighting:collection','get:CycleWeighting:collection','get:EvaluationPeriod:collection','get:SchoolPeriod:collection','get:SchoolPeriod:collection', 'get:TimeTablePeriod:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PeriodType:collection', 'write:PeriodType'])]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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
