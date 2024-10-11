<?php

namespace App\Entity\Inventory;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Inventory\Delivery\DeleteDeliveryItemController;
use App\Controller\Product\GetItemController;
use App\Entity\Product\Item;
use App\Entity\Product\Packaging;
use App\Entity\Product\Unit;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Inventory\DeliveryItemRepository;
use App\State\Processor\Global\SystemProcessor;
use App\State\Processor\Inventory\Delivery\PutDeliveryItemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DeliveryItemRepository::class)]
#[ORM\Table(name: 'inventory_delivery_item')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/delivery-item/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:DeliveryItem:collection'],
            ],
        ),
        new Get(
            uriTemplate: '/get/single/delivery-item/{id}',
            requirements: ['id' => '\d+'],
            controller: GetItemController::class,
            normalizationContext: [
                'groups' => ['get:DeliveryItem:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/delivery-item',
            normalizationContext: [
                'groups' => ['get:DeliveryItem:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/delivery-item',
            denormalizationContext: [
                'groups' => ['write:DeliveryItem'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/delivery-item/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:DeliveryItem'],
            ],
            processor: PutDeliveryItemProcessor::class,
        ),
        new Delete(
            uriTemplate: '/delete/delivery-item/{id}',
            requirements: ['id' => '\d+'],
            controller: DeleteDeliveryItemController::class
        ),
    ]
)]
class DeliveryItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:DeliveryItem:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:DeliveryItem:collection','write:DeliveryItem'])]
    private ?Delivery $delivery = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:DeliveryItem:collection','write:DeliveryItem'])]
    private ?Item $item = null;

    #[ORM\ManyToOne]
    #[Groups(['get:DeliveryItem:collection','write:DeliveryItem'])]
    private ?Packaging $packaging = null;

    #[ORM\Column]
    #[Groups(['get:DeliveryItem:collection','write:DeliveryItem'])]
    private ?float $quantity = null;

    #[ORM\ManyToOne]
    #[Groups(['get:DeliveryItem:collection','write:DeliveryItem'])]
    private ?Unit $unit = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $isEnable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->isEnable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDelivery(): ?Delivery
    {
        return $this->delivery;
    }

    public function setDelivery(?Delivery $delivery): self
    {
        $this->delivery = $delivery;

        return $this;
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

    public function getPackaging(): ?Packaging
    {
        return $this->packaging;
    }

    public function setPackaging(?Packaging $packaging): self
    {
        $this->packaging = $packaging;

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

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit): self
    {
        $this->unit = $unit;

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

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): self
    {
        $this->year = $year;

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

    public function isIsEnable(): ?bool
    {
        return $this->isEnable;
    }

    public function setIsEnable(bool $isEnable): self
    {
        $this->isEnable = $isEnable;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

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
}