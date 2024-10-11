<?php

namespace App\Entity\Setting\Location;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\User;
use App\Entity\Security\Institution\Institution;
use App\Repository\Setting\Location\RegionRepository;
use Doctrine\ORM\Mapping as ORM;
use App\State\Current\InstitutionProcessor;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RegionRepository::class)]
#[ORM\Table(name: 'setting_region')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/region/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Region:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/region/get',
            normalizationContext: [
                'groups' => ['get:Region:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/region/create',
            denormalizationContext: [
                'groups' => ['write:Region'],
            ],
            processor: InstitutionProcessor::class,
        ),

        new Put(
            uriTemplate: '/region/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Region'],
            ],
        ),
        new Delete(
            uriTemplate: '/region/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This name already exist.',
)]
class Region
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Region:collection','get:Teacher:collection','get:PensionScheme:collection','get:StudentRegistration:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:Region:collection', 'write:Region','get:Teacher:collection','get:PensionScheme:collection','get:StudentRegistration:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Region:collection', 'write:Region','get:Teacher:collection','get:PensionScheme:collection','get:StudentRegistration:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    #[Groups(['get:Region:collection', 'write:Region'])]
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

    public function setCode(string $code): self
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
