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
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SequenceRepository::class)]
#[ORM\Table(name: 'school_sequence')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/sequence/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Sequence:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/sequence',
            normalizationContext: [
                'groups' => ['get:Sequence:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/sequence',
            denormalizationContext: [
                'groups' => ['write:Sequence'],
            ],
            processor: SystemProcessor::class,

        ),
        new Put(
            uriTemplate: '/edit/sequence/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Sequence'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/sequence/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['code'],
    message: 'this sequence already exist',
)]
class Sequence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Sequence:collection','get:StudentInternship:collection','get:StudentInternship:collection','get:Graduation:collection','get:Certification:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Code cannot be smaller than {{ limit }} characters',
        maxMessage: 'Code cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:Sequence:collection', 'write:Sequence','get:StudentInternship:collection','get:StudentInternship:collection','get:Graduation:collection','get:Certification:collection'])]
    private ?string $code = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Sequence:collection', 'write:Sequence'])]
    private ?int $number = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Sequence:collection', 'write:Sequence'])]
    private ?bool $isMarkEnable = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Sequence:collection', 'write:Sequence'])]
    private ?bool $isCourseEnable = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Sequence:collection', 'write:Sequence'])]
    private ?int $maxNumberOfAssigment = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Sequence:collection', 'write:Sequence'])]
    private ?Year $year = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Sequence:collection', 'write:Sequence'])]
    private ?float $eliminateAverage = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = false;

        $this->isMarkEnable = true;
        $this->isCourseEnable = true;
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

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function isIsMarkEnable(): ?bool
    {
        return $this->isMarkEnable;
    }

    public function setIsMarkEnable(?bool $isMarkEnable): self
    {
        $this->isMarkEnable = $isMarkEnable;

        return $this;
    }

    public function isIsCourseEnable(): ?bool
    {
        return $this->isCourseEnable;
    }

    public function setIsCourseEnable(?bool $isCourseEnable): self
    {
        $this->isCourseEnable = $isCourseEnable;

        return $this;
    }

    public function getMaxNumberOfAssigment(): ?int
    {
        return $this->maxNumberOfAssigment;
    }

    public function setMaxNumberOfAssigment(?int $maxNumberOfAssigment): self
    {
        $this->maxNumberOfAssigment = $maxNumberOfAssigment;

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

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getEliminateAverage(): ?float
    {
        return $this->eliminateAverage;
    }

    public function setEliminateAverage(?float $eliminateAverage): Sequence
    {
        $this->eliminateAverage = $eliminateAverage;
        return $this;
    }
}
