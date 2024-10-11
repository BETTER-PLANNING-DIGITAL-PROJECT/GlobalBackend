<?php

namespace App\Entity\School\Schooling\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Controller\School\Schooling\Configuration\GetRoomController;
use App\Controller\School\Schooling\Configuration\PostRoomController;
use App\Controller\School\Schooling\Configuration\PutRoomController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Schooling\Configuration\RoomRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
#[ORM\Table(name: 'school_room')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/room/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Room:item'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/room',
            controller: GetRoomController::class,
            normalizationContext: [
                'groups' => ['get:Room:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/room-schools',
            normalizationContext: [
                'groups' => ['get:Room:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/room',
            controller: PostRoomController::class,
            denormalizationContext: [
                'groups' => ['write:Room']
            ],
        ),
        new Put(
            uriTemplate: '/edit/room/{id}',
            requirements: ['id' => '\d+'],
            controller: PutRoomController::class,
            denormalizationContext: [
                'groups' => ['write:Room'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/room/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/room',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Room:collection','get:Room:item','get:Class:collection','get:ClassProgram:collection','get:TimeTableModel:collection','get:TimeTableModelDayCell:collection','get:TimeTableModelDay:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Room:collection', 'get:Room:item', 'write:Room'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Room:collection','get:Room:item', 'write:Room'])]
    private ?Building $building = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Room:collection','get:Room:item' , 'write:Room','get:Class:collection','get:ClassProgram:collection','get:TimeTableModel:collection','get:TimeTableModelDay:collection','get:TimeTableModelDayCell:collection'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Room:collection', 'get:Room:item', 'write:Room'])]
    private ?int $capacity = null;

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
    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getBuilding(): ?Building
    {
        return $this->building;
    }

    public function setBuilding(?Building $building): static
    {
        $this->building = $building;

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
