<?php

namespace App\Entity\Setting\Person;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\User;
use App\Repository\Setting\Person\BloodGroupRepository;
use App\State\Current\InstitutionProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BloodGroupRepository::class)]
#[ORM\Table(name: 'setting_blood_group')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/blood-group/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:BloodGroup:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/blood-group/get',
            normalizationContext: [
                'groups' => ['get:BloodGroup:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/blood-group/create',
            denormalizationContext: [
                'groups' => ['write:BloodGroup'],
            ],
            processor: InstitutionProcessor::class,
        ),
        new Put(
            uriTemplate: '/blood-group/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:BloodGroup'],
            ],
        ),
        new Delete(
            uriTemplate: '/blood-group/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This blood group already exist.',
)]
class BloodGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:BloodGroup:collection', 'get:Teacher:collection', 'get:StudentOnline:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:BloodGroup:collection', 'write:BloodGroup'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:BloodGroup:collection', 'write:BloodGroup', 'get:Teacher:collection', 'get:StudentOnline:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    #[Groups(['get:BloodGroup:collection', 'write:BloodGroup'])]
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
