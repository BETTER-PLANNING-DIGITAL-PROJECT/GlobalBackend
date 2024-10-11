<?php

namespace App\Entity\Setting\Location;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\User;
use App\Repository\Setting\Location\CountryRepository;
use App\State\Current\InstitutionProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
#[ORM\Table(name: 'setting_country')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/country/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Country:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/country/get',
            normalizationContext: [
                'groups' => ['get:Country:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/country/create',
            denormalizationContext: [
                'groups' => ['write:Country'],
            ],
            processor: InstitutionProcessor::class,
        ),

        new Put(
            uriTemplate: '/country/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Country'],
            ],
        ),
        new Delete(
            uriTemplate: '/country/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This name already exist.',
)]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Country:collection','get:Teacher:collection','get:PensionScheme:collection','get:StudentRegistration:collection', 'get:SchoolOrigin:collection', 'get:Student:collection', 'get:StudentOnline:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 2, nullable: true)]
    #[Groups(['get:Country:collection', 'write:Country'])]
    private ?string $alpha2 = null;

    #[ORM\Column(length: 3, nullable: true)]
    #[Groups(['get:Country:collection', 'write:Country'])]
    private ?string $alpha3 = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['get:Country:collection', 'write:Country'])]
    private ?string $numericCode = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Country:collection', 'write:Country','get:Teacher:collection','get:PensionScheme:collection','get:StudentRegistration:collection', 'get:SchoolOrigin:collection', 'get:Student:collection', 'get:StudentOnline:collection'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Country:collection', 'write:Country'])]
    private ?string $officialName = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    #[Groups(['get:Country:collection', 'write:Country'])]
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

    public function getAlpha2(): ?string
    {
        return $this->alpha2;
    }

    public function setAlpha2(?string $alpha2): static
    {
        $this->alpha2 = $alpha2;

        return $this;
    }

    public function getAlpha3(): ?string
    {
        return $this->alpha3;
    }

    public function setAlpha3(?string $alpha3): static
    {
        $this->alpha3 = $alpha3;

        return $this;
    }

    public function getNumericCode(): ?string
    {
        return $this->numericCode;
    }

    public function setNumericCode(?string $numericCode): static
    {
        $this->numericCode = $numericCode;

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

    public function getOfficialName(): ?string
    {
        return $this->officialName;
    }

    public function setOfficialName(?string $officialName): static
    {
        $this->officialName = $officialName;

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
