<?php

namespace App\Entity\School\Schooling\Registration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Registration\FamilyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FamilyRepository::class)]
#[ORM\Table(name: 'school_family')]
#[ApiResource(
    operations:[
        /*new Get(
            uriTemplate: '/family/{id}/get',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Family:item'],
            ],
        ),*/
        new GetCollection(
            uriTemplate: '/get/family',
            normalizationContext: [
                'groups' => ['get:Family:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/family',
            denormalizationContext: [
                'groups' => ['write:Family'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/family/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Family'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/family/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This family already exist.',
)]
class Family
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Family:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Name cannot be smaller than {{ limit }} characters',
        maxMessage: 'Name cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:Family:collection', 'write:Family'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:Family:collection', 'write:Family'])]
    private ?string $address = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Family:collection', 'write:Family'])]
    private ?string $phone = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Family:collection', 'write:Family'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups([ 'get:Family:collection','write:Family','put:Family'])]
    private ?string $fatherName = null;

    #[ORM\Column(length: 50)]
    #[Groups([ 'get:Family:collection','write:Family','put:Family'])]
    private ?string $fatherPhone = null;

    #[ORM\Column(length: 255)]
    #[Groups([ 'get:Family:collection','write:Family','put:Family'])]
    private ?string $fatherProfession = null;

    #[ORM\Column(length: 255)]
    #[Groups([ 'get:Family:collection','write:Family','put:Family'])]
    private ?string $motherName = null;

    #[ORM\Column(length: 50)]
    #[Groups([ 'get:Family:collection','write:Family','put:Family'])]
    private ?string $motherPhone = null;

    #[ORM\Column(length: 50)]
    #[Groups([ 'get:Family:collection','write:Family','put:Family'])]
    private ?string $motherProfession = null;

    #[ORM\Column]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([ 'get:Family:collection','write:Family','put:Family'])]
    private ?bool $issmssubscription = null;

    #[ORM\Column]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([ 'get:Family:collection','write:Family','put:Family'])]
    private ?bool $isemailsubscription = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getFatherName(): ?string
    {
        return $this->fatherName;
    }

    public function setFatherName(string $fatherName): self
    {
        $this->fatherName = $fatherName;

        return $this;
    }

    public function getFatherPhone(): ?string
    {
        return $this->fatherPhone;
    }

    public function setFatherPhone(string $fatherPhone): self
    {
        $this->fatherPhone = $fatherPhone;

        return $this;
    }

    public function getFatherProfession(): ?string
    {
        return $this->fatherProfession;
    }

    public function setFatherProfession(string $fatherProfession): self
    {
        $this->fatherProfession = $fatherProfession;

        return $this;
    }

    public function getMotherName(): ?string
    {
        return $this->motherName;
    }

    public function setMotherName(string $motherName): self
    {
        $this->motherName = $motherName;

        return $this;
    }

    public function getMotherPhone(): ?string
    {
        return $this->motherPhone;
    }

    public function setMotherPhone(string $motherPhone): self
    {
        $this->motherPhone = $motherPhone;

        return $this;
    }

    public function getMotherProfession(): ?string
    {
        return $this->motherProfession;
    }

    public function setMotherProfession(string $motherProfession): self
    {
        $this->motherProfession = $motherProfession;

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

    public function isIsSmsSubscription(): ?bool
    {
        return $this->issmssubscription;
    }

    public function setIsSmsSubscription(bool $issmssubscription): static
    {
        $this->issmssubscription = $issmssubscription;

        return $this;
    }

    public function isIsEmailSubscription(): ?bool
    {
        return $this->isemailsubscription;
    }

    public function setIsEmailSubscription(bool $isemailsubscription): static
    {
        $this->isemailsubscription = $isemailsubscription;

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
}
