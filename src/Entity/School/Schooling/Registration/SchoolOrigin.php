<?php

namespace App\Entity\School\Schooling\Registration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Setting\Location\Country;
use App\Repository\School\Schooling\Registration\SchoolOriginRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SchoolOriginRepository::class)]
#[ORM\Table(name: 'school_of_origin')]
#[ApiResource(
    operations:[
        /*new Get(
            uriTemplate: '/school/origin/{id}/get',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:SchoolOrigin:item'],
            ],
        ),*/
        new GetCollection(
            uriTemplate: '/schoolorigin/get',
            normalizationContext: [
                'groups' => ['get:SchoolOrigin:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/schoolorigin/create',
            denormalizationContext: [
                'groups' => ['write:SchoolOrigin'],
            ],
        ),
        new Put(
            uriTemplate: '/schoolorigin/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SchoolOrigin'],
            ],
        ),
        new Delete(
            uriTemplate: '/schoolorigin/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This school of origin already exist.',
)]
class SchoolOrigin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:SchoolOrigin:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Code cannot be smaller than {{ limit }} characters',
        maxMessage: 'Code cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:SchoolOrigin:collection', 'write:SchoolOrigin'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Name cannot be smaller than {{ limit }} characters',
        maxMessage: 'Name cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:SchoolOrigin:collection', 'write:SchoolOrigin'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:SchoolOrigin:collection', 'write:SchoolOrigin'])]
    private ?Country $country = null;

    #[ORM\Column(length: 22)]
    #[Groups(['get:SchoolOrigin:collection', 'write:SchoolOrigin'])]
    private ?string $postcode = null;

    #[ORM\Column(length: 22)]
    #[Groups(['get:SchoolOrigin:collection', 'write:SchoolOrigin'])]
    private ?string $city = null;

    #[ORM\Column(length: 22)]
    #[Groups(['get:SchoolOrigin:collection', 'write:SchoolOrigin'])]
    private ?string $phone = null;

    #[ORM\Column(length: 22)]
    #[Groups(['get:SchoolOrigin:collection', 'write:SchoolOrigin'])]
    private ?string $fax = null;

    #[ORM\Column(length: 22)]
    #[Groups(['get:SchoolOrigin:collection', 'write:SchoolOrigin'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?bool $is_enable = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?bool $is_archive = null;

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
        $this->is_archive = false;
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

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(string $fax): self
    {
        $this->fax = $fax;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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

    public function isIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(bool $is_archive): static
    {
        $this->is_archive = $is_archive;

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
}
