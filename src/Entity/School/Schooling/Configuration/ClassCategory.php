<?php

namespace App\Entity\School\Schooling\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Controller\School\Schooling\Configuration\GetClassCategoryController;
use App\Controller\School\Schooling\Configuration\PostClassCategoryController;
use App\Controller\School\Schooling\Configuration\PutClassCategoryController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\ClassCategoryRepository;;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClassCategoryRepository::class)]
#[ORM\Table(name: 'school_class_category')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/class-category/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:ClassCategory:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/class-category-schools',
            normalizationContext: [
                'groups' => ['get:ClassCategory:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/class-category',
            controller: GetClassCategoryController::class,
            normalizationContext: [
                'groups' => ['get:ClassCategory:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/class-category',
            controller: PostClassCategoryController::class,
            denormalizationContext: [
                'groups' => ['write:ClassCategory'],
            ],

        ),
        new Put(
            uriTemplate: '/edit/class-category/{id}',
            requirements: ['id' => '\d+'],
            controller: PutClassCategoryController::class,
            denormalizationContext: [
                'groups' => ['write:ClassCategory'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/class-category/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/class-category',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
class ClassCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:ClassCategory:collection','get:Class:collection','get:Class:collection','get:Institution:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ClassCategory:collection', 'write:ClassCategory'])]
    private ?School $school = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:ClassCategory:collection', 'write:ClassCategory'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank!')]
    #[Groups(['get:ClassCategory:collection', 'write:ClassCategory','get:Class:collection','get:Class:collection','get:Institution:collection'])]
    private ?string $name = null;

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
