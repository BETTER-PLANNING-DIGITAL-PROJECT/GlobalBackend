<?php

namespace App\Entity\Setting\School;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\School\Schooling\Configuration\Level;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\User;
use App\Repository\Setting\School\DiplomaRepository;
use App\State\Current\InstitutionProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DiplomaRepository::class)]
#[ORM\Table(name: 'setting_diploma')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/diploma/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Diploma:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/diploma/get',
            normalizationContext: [
                'groups' => ['get:Diploma:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/diploma/create',
            denormalizationContext: [
                'groups' => ['write:Diploma'],
            ],
            processor: InstitutionProcessor::class,
        ),
        new Put(
            uriTemplate: '/diploma/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Diploma'],
            ],
        ),
        new Delete(
            uriTemplate: '/diploma/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This diploma already exist.',
)]
class Diploma
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Diploma:collection','get:StudentRegistration:collection','get:Diplomamention:collection','get:NationalExam:collection','get:StudentInternship:collection','get:Graduation:collection','get:Certification:collection','get:ResearchWork:collection', 'get:StudentOnline:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:Diploma:collection', 'write:Diploma', 'get:Grading:collection',])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Diploma:collection', 'write:Diploma', 'get:Grading:collection','get:Diplomamention:collection','get:StudentRegistration:collection','get:StudentInternship:collection','get:Graduation:collection','get:Certification:collection','get:NationalExam:collection','get:ResearchWork:collection', 'get:StudentOnline:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Diploma:collection', 'write:Diploma'])]
    private ?DiplomaType $nature = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Diploma:collection', 'write:Diploma'])]
    private ?Level $level = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['write:Diploma'])]
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

    public function getNature(): ?DiplomaType
    {
        return $this->nature;
    }

    public function setNature(?DiplomaType $nature): static
    {
        $this->nature = $nature;

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
