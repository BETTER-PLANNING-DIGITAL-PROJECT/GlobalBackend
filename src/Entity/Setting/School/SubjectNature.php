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
use App\Repository\Setting\School\SubjectNatureRepository;
use Doctrine\ORM\Mapping as ORM;
use App\State\Current\InstitutionProcessor;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SubjectNatureRepository::class)]
#[ORM\Table(name: 'school_subject_nature')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/subject-nature/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:SubjectNature:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/subject-nature/get',
            normalizationContext: [
                'groups' => ['get:SubjectNature:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/subject-nature/create',
            denormalizationContext: [
                'groups' => ['write:SubjectNature']
            ],
            processor: InstitutionProcessor::class,
        ),
        new Put(
            uriTemplate: '/subject-nature/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SubjectNature'],
            ],
        ),
        new Delete(
            uriTemplate: '/subject-nature/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'this Subject nature already exist',
)]
class SubjectNature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:SubjectNature:collection','get:ClassProgram:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['get:SubjectNature:collection','write:SubjectNature'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:SubjectNature:collection','write:SubjectNature','get:ClassProgram:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    #[Groups(['get:SubjectNature:collection', 'write:SubjectNature'])]
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

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
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
