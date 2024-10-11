<?php

namespace App\Entity\School\Study\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\School\Study\Configuration\ImportSubjectController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Study\Configuration\SubjectRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SubjectRepository::class)]
#[ORM\Table(name: 'school_subject')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/subject/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Subject:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/subject',
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:Subject:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/subject',
            denormalizationContext: [
                'groups' => ['write:Subject'],
            ],
           processor: SystemProcessor::class,
        ),
        new Post(
            uriTemplate: '/import/subject',
            controller: ImportSubjectController::class,
            openapiContext: [
                "summary" => "Add multiple Subject resources.",
            ],
            denormalizationContext: [
                'groups' => ['post:Subject']
            ],
        ),
        new Put(
            uriTemplate: '/edit/subject/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Subject'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/subject/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['code'],
    message: 'this code already exist',
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'this name already exist',
)]
class Subject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Subject:collection','get:Teacher:collection','get:ClassProgram:collection','get:StudentAttendance:collection','get:ClassProgram:collection','get:StudentAttendance:collection','get:TimeTableModelDayCell:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Subject:collection', 'write:Subject'])]
    private ?SubjectType $subjectType = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:Subject:collection', 'write:Subject','get:TimeTableModelDayCell:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Subject:collection', 'write:Subject','get:Teacher:collection','get:ClassProgram:collection','get:StudentAttendance:collection','get:TimeTableModelDayCell:collection', 'get:StudentCourseRegistration:collection','get:Mark:collection','get:StudentAttendance:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Subject:collection', 'write:Subject'])]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Subject:collection', 'write:Subject'])]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function setInstitution(?Institution $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

    public function getSubjectType(): ?SubjectType
    {
        return $this->subjectType;
    }

    public function setSubjectType(?SubjectType $subjectType): static
    {
        $this->subjectType = $subjectType;

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
