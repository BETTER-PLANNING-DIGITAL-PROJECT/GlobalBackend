<?php

namespace App\Entity\Setting\Institution;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Entity\Security\User;
use App\Entity\Security\Institution\Institution;
use App\Repository\Setting\Institution\MinistryRepository;
use App\State\Current\InstitutionProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MinistryRepository::class)]
#[ORM\Table(name: 'setting_ministry')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/ministry/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Ministry:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/ministry/get',
            normalizationContext: [
                'groups' => ['get:Ministry:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/ministry/create',
            denormalizationContext: [
                'groups' => ['write:Ministry'],
            ],
            processor: InstitutionProcessor::class,
        ),

        new Put(
            uriTemplate: '/ministry/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Ministry'],
            ],
        ),
        new Delete(
            uriTemplate: '/ministry/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This name already exist.',
)]
class Ministry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Ministry:collection', 'get:Cycle:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:Ministry:collection', 'write:Ministry', 'get:Cycle:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Ministry:collection', 'write:Ministry', 'get:Cycle:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Ministry:collection', 'write:Ministry'])]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([ 'get:Ministry:collection','post:Ministry'])]
    private ?Institution $institution = null;

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

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(bool $is_enable): self
    {
        $this->is_enable = $is_enable;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
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

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

}
