<?php

namespace App\Entity\Inventory;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Controller\Inventory\GetWareHouseController;
use App\Controller\Inventory\GetWareHouseCurrentBranchController;
use App\Controller\Inventory\PostWareHouseController;
use App\Controller\Inventory\PutWareHouseController;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Inventory\WarehouseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: WarehouseRepository::class)]
#[ORM\Table(name: 'inventory_warehouse')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/warehouse/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Warehouse:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/warehouse',
            normalizationContext: [
                'groups' => ['get:Warehouse:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/warehouse-current-branch',
            controller: GetWareHouseCurrentBranchController::class,
            normalizationContext: [
                'groups' => ['get:Warehouse:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/warehouse-branches',
            controller: GetWareHouseController::class,
            normalizationContext: [
                'groups' => ['get:Warehouse:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/warehouse',
            controller: PostWareHouseController::class,
            denormalizationContext: [
                'groups' => ['write:Warehouse'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/warehouse/{id}',
            requirements: ['id' => '\d+'],
            controller: PutWareHouseController::class,
            denormalizationContext: [
                'groups' => ['write:Warehouse'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/warehouse/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/warehouse',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
class Warehouse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Warehouse:collection','get:Location:collection','get:Stock:collection','get:StockMovement:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['get:Warehouse:collection','write:Warehouse'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:Warehouse:collection','write:Warehouse','get:Location:collection','get:Stock:collection','get:StockMovement:collection'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Warehouse:collection','write:Warehouse'])]
    private ?string $address = null;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

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
