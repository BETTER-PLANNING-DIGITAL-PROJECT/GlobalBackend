<?php

namespace App\Entity\Inventory;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Controller\Inventory\GetLocationController;
use App\Controller\Inventory\PostLocationController;
use App\Controller\Inventory\PutLocationController;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Inventory\LocationRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
#[ORM\Table(name: 'inventory_location')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/location/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Location:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/location',
            controller: GetLocationController::class,
            normalizationContext: [
                'groups' => ['get:Location:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/location-branches',
            normalizationContext: [
                'groups' => ['get:Location:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/location',
            controller: PostLocationController::class,
            denormalizationContext: [
                'groups' => ['write:Location'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/location/{id}',
            requirements: ['id' => '\d+'],
            controller: PutLocationController::class,
            denormalizationContext: [
                'groups' => ['write:Location'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/location/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/location',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Location:collection','get:Stock:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:Location:collection','write:Location','get:Stock:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Location:collection','write:Location'])]
    private ?Warehouse $warehouse = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Warehouse:collection','write:Warehouse'])]
    private ?Branch $branch;

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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(?Warehouse $warehouse): self
    {
        $this->warehouse = $warehouse;

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

    public function getBranch(): ?Branch
    {
        return $this->branch;
    }

    public function setBranch(?Branch $branch): self
    {
        $this->branch = $branch;

        return $this;
    }
}
