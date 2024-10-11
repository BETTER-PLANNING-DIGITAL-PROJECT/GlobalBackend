<?php

namespace App\Entity\School\Exam\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\GetServices\GetNoteTypeController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\NoteTypeRepository;
use App\State\Current\InstitutionProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NoteTypeRepository::class)]
#[ORM\Table(name: 'school_note_type')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/note-type/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:NoteType:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/note-type/get',
            normalizationContext: [
                'groups' => ['get:NoteType:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/note-type/get/is-active',
            controller: GetNoteTypeController::class,
            normalizationContext: [
                'groups' => ['get:NoteType:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/note-type/create',
            denormalizationContext: [
                'groups' => ['write:NoteType'],
            ],
            processor: InstitutionProcessor::class
        ),
        new Put(
            uriTemplate: '/note-type/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:NoteType'],
            ],
        ),
        new Delete(
            uriTemplate: '/note-type/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This note type already exist.',
)]
class NoteType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:NoteType:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Name cannot be smaller than {{ limit }} characters',
        maxMessage: 'Name cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:NoteType:collection', 'write:NoteType'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:NoteType:collection'])]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column]
    #[Groups(['get:NoteType:collection', 'write:NoteType'])]
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

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

    public function setInstitution(?Institution $institution): NoteType
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
