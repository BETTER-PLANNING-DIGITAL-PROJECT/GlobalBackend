<?php

namespace App\Entity\Product;

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
use App\Repository\Product\PackagingRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PackagingRepository::class)]
#[ORM\Table(name: 'product_packaging')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/packaging/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Packaging:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/packaging',
            normalizationContext: [
                'groups' => ['get:Packaging:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/packaging',
            denormalizationContext: [
                'groups' => ['write:Packaging'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/packaging/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Packaging'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/packaging/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/packaging',
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
#[ApiResource]
class Packaging
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Packaging:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Packaging:collection','write:Packaging'])]
    private ?Item $item = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:Packaging:collection','write:Packaging'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Packaging:collection','write:Packaging'])]
    private ?bool $isPurchase = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Packaging:collection','write:Packaging'])]
    private ?bool $isSale = null;

    #[ORM\Column]
    #[Groups(['get:Packaging:collection','write:Packaging'])]
    private ?float $quantity = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Packaging:collection','write:Packaging'])]
    private ?string $barcode = null;

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
        $this->isPurchase = false;
        $this->isSale = false;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

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

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(?string $barcode): self
    {
        $this->barcode = $barcode;

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

    public function isIsPurchase(): ?bool
    {
        return $this->isPurchase;
    }

    public function setIsPurchase(?bool $isPurchase): self
    {
        $this->isPurchase = $isPurchase;

        return $this;
    }

    public function isIsSale(): ?bool
    {
        return $this->isSale;
    }

    public function setIsSale(?bool $isSale): self
    {
        $this->isSale = $isSale;

        return $this;
    }
}
