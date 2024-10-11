<?php

namespace App\Entity\Security;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Security\DeleteRoleController;
use App\Controller\Security\EditRoleController;
use App\Controller\Security\TreeViewWhenEditProfileController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Repository\Security\ProfileRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProfileRepository::class)]
#[ORM\Table(name: 'security_profile')]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/get/profile/{id}',
            requirements: ['id' => '\d+'],
            controller: TreeViewWhenEditProfileController::class,
            normalizationContext: [
                'groups' => ['get:Profile:item'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/profiles',
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:Profile:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/profile',
            denormalizationContext: [
                'groups' => ['post:Profile']
            ],
            processor: SystemProcessor::class
        ),
        new Put(
            uriTemplate: '/edit/profile/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['put:Profile']
            ],
            processor: SystemProcessor::class
        ),
        new Put(
            uriTemplate: '/setting/profile/{id}',
            requirements: ['id' => '\d+'],
            controller: EditRoleController::class,
            denormalizationContext: [
                'groups' => ['put:Profile']
            ],
            deserialize: false,
        ),
        new Delete(
            uriTemplate: '/delete/profile/{id}',
            requirements: ['id' => '\d+'],
            controller: DeleteRoleController::class
        ),
    ],
)]
class Profile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Profile:collection', 'get:Role:collection', 'get:Role:item', 'get:User:collection', 'get:User:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 110)]
    #[Groups(['get:Profile:collection', 'post:Profile', 'put:Profile', 'get:Role:collection', 'get:Role:item', 'get:User:collection', 'get:User:item'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Profile:collection', 'post:Profile', 'put:Profile'])]
    private ?bool $isTeacherSystem = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Profile:collection', 'post:Profile', 'put:Profile'])]
    private ?bool $isStudentSystem = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item'])]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    #[Groups(['get:Profile:collection'])]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

    #[ORM\Column]
    private ?bool $isEnable = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTimeImmutable();

        $this->isEnable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function isIsTeacherSystem(): ?bool
    {
        return $this->isTeacherSystem;
    }

    public function setIsTeacherSystem(?bool $isTeacherSystem): self
    {
        $this->isTeacherSystem = $isTeacherSystem;

        return $this;
    }

    public function isIsStudentSystem(): ?bool
    {
        return $this->isStudentSystem;
    }

    public function setIsStudentSystem(?bool $isStudentSystem): self
    {
        $this->isStudentSystem = $isStudentSystem;

        return $this;
    }

    public function isIsEnable(): ?bool
    {
        return $this->isEnable;
    }

    public function setIsEnable(bool $isEnable): self
    {
        $this->isEnable = $isEnable;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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
