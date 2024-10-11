<?php

namespace App\Entity\School\Schooling\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\RegistrationFormRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RegistrationFormRepository::class)]
#[ORM\Table(name: 'school_registration_form')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/registration-form/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:RegistrationForm:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/registration-form',
            normalizationContext: [
                'groups' => ['get:RegistrationForm:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/registration-form',
            denormalizationContext: [
                'groups' => ['write:RegistrationForm'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/registration-form/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:RegistrationForm'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/registration-form/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/registration-form',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'this name already exist',
)]
class RegistrationForm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:RegistrationForm:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:RegistrationForm:collection', 'write:RegistrationForm'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['get:RegistrationForm:collection', 'write:RegistrationForm'])]
    private ?bool $isshowreceipt = true;

    #[ORM\ManyToOne]
    #[Groups(['get:RegistrationForm:collection', 'write:RegistrationForm'])]
    private ?Speciality $speciality = null;

    #[ORM\ManyToOne]
    #[Groups(['get:RegistrationForm:collection', 'write:RegistrationForm'])]
    private ?Level $minimumLevel = null;

    #[ORM\ManyToOne]
    #[Groups(['get:RegistrationForm:collection', 'write:RegistrationForm'])]
    private ?Level $maximumLevel = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 4)]
    #[Groups(['get:RegistrationForm:collection', 'write:RegistrationForm'])]
    private ?string $amount = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['write:RegistrationForm'])]
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


    public function isIsShowReceipt(): ?bool
    {
        return $this->isshowreceipt;
    }

    public function setIsShowReceipt(bool $isshowreceipt): static
    {
        $this->isshowreceipt = $isshowreceipt;

        return $this;
    }

    public function getSpeciality(): ?Speciality
    {
        return $this->speciality;
    }

    public function setSpeciality(?Speciality $speciality): static
    {
        $this->speciality = $speciality;

        return $this;
    }

    public function getMinimumLevel(): ?Level
    {
        return $this->minimumLevel;
    }

    public function setMinimumLevel(?Level $minimumLevel): static
    {
        $this->minimumLevel = $minimumLevel;

        return $this;
    }

    public function getMaximumLevel(): ?Level
    {
        return $this->maximumLevel;
    }

    public function setMaximumLevel(?Level $maximumLevel): static
    {
        $this->maximumLevel = $maximumLevel;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

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
