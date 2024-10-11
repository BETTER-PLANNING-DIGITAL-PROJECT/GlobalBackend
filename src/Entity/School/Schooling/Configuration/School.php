<?php

namespace App\Entity\School\Schooling\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Billing\Sale\GetClassBySchoolController;
use App\Controller\DeleteSelectedResourceController;
use App\Controller\School\Schooling\Configuration\PostSchoolController;
use App\Controller\School\Schooling\Configuration\PutSchoolController;
use App\Controller\School\Schooling\Configuration\SchoolController;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Setting\Institution\ManagerType;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\State\Processor\Global\SystemProcessor;
use App\State\SchoolStudCourseRegProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SchoolRepository::class)]
#[ORM\Table(name: 'school_school')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/school/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:School:item'],
            ],
        ),
        new Get(
            uriTemplate: '/get/class/by/school/{id}',
            requirements: ['id' => '\d+'],
            controller: GetClassBySchoolController::class,
            normalizationContext: [
                'groups' => ['get:School:item'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/get/school',
//            controller: SchoolController::class,
            normalizationContext: [
                'groups' => ['get:School:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/school/get/stud-course-reg',
            normalizationContext: [
                'groups' => ['get:School:collection']
            ],
            provider: SchoolStudCourseRegProvider::class
        ),
        new Post(
            uriTemplate: '/create/school',
            controller: PostSchoolController::class,
            denormalizationContext: [
                'groups' => ['write:School'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/school/{id}',
            requirements: ['id' => '\d+'],
            controller: PutSchoolController::class,
            denormalizationContext: [
                'groups' => ['write:School'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/school/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/school',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['code'],
    message: 'this code already exist',
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'this name already exist',
)]
class School
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:School:collection','get:School:item','get:StudentRegistration:collection','get:Room:collection','get:PensionScheme:collection','get:ClassProgram:collection','get:SchoolWeighting:collection','get:ClassWeighting:collection','get:Program:collection','get:Department:collection','get:ClassCategory:collection','get:Class:collection', 'get:Guardianship:collection', 'get:Tuition:collection','get:PromotionConditions:collection','get:GraduationConditions:collection','get:Class:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:School:collection','get:School:item', 'write:School', 'get:Tuition:collection', 'get:Class:collection','get:StudentRegistration:collection', 'get:Fee:collection','get:ClassProgram:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Fee:collection','get:School:collection','get:School:item', 'write:School', 'get:Room:collection','get:ClassProgram:collection','get:SchoolWeighting:collection','get:ClassWeighting:collection','get:PensionScheme:collection','get:Program:collection','get:Department:collection','get:ClassCategory:collection','get:Class:collection', 'get:Guardianship:collection', 'get:Tuition:collection','get:MarkGrade:collection','get:SchoolWeighting:collection','get:PromotionConditions:collection','get:GraduationConditions:collection','get:StudentRegistration:collection'])]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups([ 'get:School:collection','get:School:item', 'write:School'])]
    private ?string $email = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['get:School:collection','get:School:item', 'write:School'])]
    private ?string $phone = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['get:School:collection','get:School:item', 'write:School'])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:School:collection','get:School:item','write:School'])]
    private ?string $city = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:School:collection','get:School:item', 'write:School'])]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:School:collection','get:School:item', 'write:School'])]
    private ?string $manager = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:School:collection','get:School:item', 'write:School'])]
    private ?string $type = null;

    #[ORM\Column]
    #[Groups(['get:School:collection','get:School:item', 'write:School'])]
    private ?bool $managesNoteType = null;

    #[ORM\ManyToOne]
    #[Groups(['get:School:collection','get:School:item', 'write:School'])]
    private ?ManagerType $managerType = null;

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

    #[ORM\ManyToOne]
    private ?Branch $branch = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Branch $schoolBranch = null;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getManager(): ?string
    {
        return $this->manager;
    }

    public function setManager(?string $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    public function getManagerType(): ?ManagerType
    {
        return $this->managerType;
    }

    public function setManagerType(?ManagerType $managerType): static
    {
        $this->managerType = $managerType;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getManagesNoteType(): ?bool
    {
        return $this->managesNoteType;
    }

    public function setManagesNoteType(?bool $managesNoteType): void
    {
        $this->managesNoteType = $managesNoteType;
    }

    public function getBranch(): ?Branch
    {
        return $this->branch;
    }

    public function setBranch(?Branch $branch): self
    {
        $this->branch = $branch;

        return $this;
    }

    public function getSchoolBranch(): ?Branch
    {
        return $this->schoolBranch;
    }

    public function setSchoolBranch(?Branch $schoolBranch): self
    {
        $this->schoolBranch = $schoolBranch;

        return $this;
    }
}
