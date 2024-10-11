<?php

namespace App\Entity\Product;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Controller\Product\GetItemCategoryController;
use App\Controller\Product\ImportItemCategoryController;
use App\Controller\Product\PostItemCategoryController;
use App\Controller\Product\PutItemCategoryController;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Inventory\PriceStrategy;
use App\Entity\Setting\Inventory\StockStrategy;
use App\Repository\Product\ItemCategoryRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ItemCategoryRepository::class)]
#[ORM\Table(name: 'product_item_category')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/item-category/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:ItemCategory:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/item-category',
            controller: GetItemCategoryController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:ItemCategory:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/item-category',
            controller: PostItemCategoryController::class,
            denormalizationContext: [
                'groups' => ['write:ItemCategory'],
            ],
        ),
        new Post(
            uriTemplate: '/import/item-category',
            controller: ImportItemCategoryController::class,
            openapiContext: [
                "summary" => "Add multiple item category resources.",
            ],
            denormalizationContext: [
                'groups' => ['post:Cycle']
            ],
        ),
        new Put(
            uriTemplate: '/edit/item-category/{id}',
            requirements: ['id' => '\d+'],
            controller: PutItemCategoryController::class,
            denormalizationContext: [
                'groups' => ['write:ItemCategory'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/item-category/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/item-category',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
class ItemCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:ItemCategory:collection','get:Item:collection','get:Item:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:ItemCategory:collection','write:ItemCategory','get:Item:collection','get:Item:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ItemCategory:collection','write:ItemCategory'])]
    private ?StockStrategy $stockStrategy = null;

    #[ORM\ManyToOne]
    #[Groups(['get:ItemCategory:collection','write:ItemCategory'])]
    private ?PriceStrategy $priceStrategy = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:ItemCategory:collection','write:ItemCategory'])]
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

    public function getStockStrategy(): ?StockStrategy
    {
        return $this->stockStrategy;
    }

    public function setStockStrategy(?StockStrategy $stockStrategy): self
    {
        $this->stockStrategy = $stockStrategy;

        return $this;
    }

    public function getPriceStrategy(): ?PriceStrategy
    {
        return $this->priceStrategy;
    }

    public function setPriceStrategy(?PriceStrategy $priceStrategy): self
    {
        $this->priceStrategy = $priceStrategy;

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
