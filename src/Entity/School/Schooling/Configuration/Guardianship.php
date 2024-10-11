<?php

namespace App\Entity\School\Schooling\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Controller\School\Schooling\Configuration\GetGuardianshipController;
use App\Controller\School\Schooling\Configuration\PostGuardianshipController;
use App\Controller\School\Schooling\Configuration\PutGuardianshipController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\GuardianshipRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GuardianshipRepository::class)]
#[ORM\Table(name: 'school_guardianship')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/guardianship/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Guardianship:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/guardianship-schools',
            normalizationContext: [
                'groups' => ['get:Guardianship:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/guardianship',
            controller: GetGuardianshipController::class,
            normalizationContext: [
                'groups' => ['get:Guardianship:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/guardianship',
            controller: PostGuardianshipController::class,
            denormalizationContext: [
                'groups' => ['post:Guardianship'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/guardianship/{id}',
            requirements: ['id' => '\d+'],
            controller: PutGuardianshipController::class,
            denormalizationContext: [
                'groups' => ['put:Guardianship'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/guardianship/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/guardianship',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
class Guardianship
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Guardianship:collection','get:Class:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Guardianship:collection', 'post:Guardianship', 'put:Guardianship'])]
    private ?School $school = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:Guardianship:collection', 'post:Guardianship', 'put:Guardianship'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Guardianship:collection', 'post:Guardianship', 'put:Guardianship','get:Class:collection'])]
    private ?string $name = null;

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

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): static
    {
        $this->school = $school;

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
