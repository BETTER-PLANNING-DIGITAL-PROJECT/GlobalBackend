<?php

namespace App\Entity\School\Schooling\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Controller\School\Schooling\Configuration\DepartmentController;
use App\Controller\School\Schooling\Configuration\GetDepartmentController;
use App\Controller\School\Schooling\Configuration\ImportDepartmentController;
use App\Controller\School\Schooling\Configuration\PostDepartmentController;
use App\Controller\School\Schooling\Configuration\PutDepartmentController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\DepartmentRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
#[ORM\Table(name: 'school_department')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/department/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Department:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/department-schools',
            normalizationContext: [
                'groups' => ['get:Department:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/department',
            controller: GetDepartmentController::class,
            normalizationContext: [
                'groups' => ['get:Department:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/department',
            controller: PostDepartmentController::class,
            denormalizationContext: [
                'groups' => ['write:Department'],
            ],

        ),
        new Post(
            uriTemplate: '/import/department',
            controller: ImportDepartmentController::class,
            openapiContext: [
                "summary" => "Add multiple Department resources.",

            ],
            denormalizationContext: [
                'groups' => ['post:Department']
            ],
        ),
        new Put(
            uriTemplate: '/edit/department/{id}',
            requirements: ['id' => '\d+'],
            controller: PutDepartmentController::class,
            denormalizationContext: [
                'groups' => ['write:Department'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/department/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/department',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
class Department
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Department:collection','get:Class:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Department:collection', 'write:Department'])]
    private ?School $school = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank!')]
    #[Groups(['get:Department:collection', 'write:Department'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Department:collection', 'write:Department','get:Class:collection'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Department:collection', 'write:Department'])]
    private ?int $position = null;

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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

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
