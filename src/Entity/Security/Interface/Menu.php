<?php

namespace App\Entity\Security\Interface;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Security\CreateLabelController;
use App\Controller\Security\CreateMenuController;
use App\Controller\Security\CreateSingleController;
use App\Controller\Security\CreateSubMenuController;
use App\Controller\Security\EditLabelController;
use App\Controller\Security\EditMenuController;
use App\Controller\Security\EditSingleController;
use App\Controller\Security\EditSubMenuController;
use App\Controller\Security\GetLabelController;
use App\Controller\Security\GetMenuController;
use App\Controller\Security\GetParentMenuController;
use App\Controller\Security\GetSingleController;
use App\Controller\Security\GetSubmenuController;
use App\Controller\Security\GetUserPermissionsByMenuIdController;
use App\Repository\Security\Interface\MenuRepository;
use App\State\Processor\Security\PostLabelProcessor;
use App\State\Processor\Security\PostMenuProcessor;
use App\State\Processor\Security\PostSingleProcessor;
use App\State\Processor\Security\PostSubMenuProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
#[ORM\Table(name: 'security_menu')]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/get/menu/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Menu:item'],
            ],
        ),
        new Get(
            uriTemplate: '/get/permissions/menu/{id}',
            requirements: ['id' => '\d+'],
            controller: GetUserPermissionsByMenuIdController::class,
            normalizationContext: [
                'groups' => ['get:Menu:item'],
            ]),
        new GetCollection(
            uriTemplate: '/get/menu',
            controller: GetMenuController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:Menu:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/singles',
            controller: GetSingleController::class,
            //order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:Menu:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/submenus',
            controller: GetSubmenuController::class,
            normalizationContext: [
                'groups' => ['get:Menu:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: 'get/parent/menus',
            controller: GetParentMenuController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:Menu:collection'],
            ]
        ),
        new GetCollection(
            uriTemplate: 'get/labels',
            controller: GetLabelController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:Menu:collection'],
            ]
        ),
        new Post(
            uriTemplate: '/create/single',
            denormalizationContext: [
                'groups' => ['post:Menu']
            ],
            processor: PostSingleProcessor::class
        ),
        new Post(
            uriTemplate: '/create/menu',
            denormalizationContext: [
                'groups' => ['post:Menu']
            ],
            processor: PostMenuProcessor::class
        ),
        new Post(
            uriTemplate: '/create/submenu',
            denormalizationContext: [
                'groups' => ['post:Menu']
            ],
            processor: PostSubMenuProcessor::class
        ),
        new Post(
            uriTemplate: '/create/label',
            denormalizationContext: [
                'groups' => ['post:Menu']
            ],
            processor: PostLabelProcessor::class
        ),

        new Put(
            uriTemplate: '/edit/menu/{id}',
            requirements: ['id' => '\d+'],
            controller: EditMenuController::class,
            denormalizationContext: [
                'groups' => ['put:Menu']
            ],
            deserialize: false
        ),
        new Put(
            uriTemplate: '/edit/single/{id}',
            requirements: ['id' => '\d+'],
            controller: EditSingleController::class,
            denormalizationContext: [
                'groups' => ['put:Menu']
            ],
            deserialize: false
        ),
        new Put(
            uriTemplate: '/edit/submenu/{id}',
            requirements: ['id' => '\d+'],
            controller: EditSubMenuController::class,
            denormalizationContext: [
                'groups' => ['put:Menu']
            ],
            deserialize: false
        ),
        new Put(
            uriTemplate: '/edit/label/{id}',
            requirements: ['id' => '\d+'],
            controller: EditLabelController::class,
            denormalizationContext: [
                'groups' => ['put:Menu']
            ],
            deserialize: false
        ),

        new Delete(
            uriTemplate: '/delete/menu/{id}',
            requirements: ['id' => '\d+'],
        ),
//        new Delete(
//            uriTemplate: '/delete/selected/menus',
//            controller: DeleteSelectedResourceController::class,
//            openapiContext: [
//                "summary" => "Restore collections of api resource",
//            ],
//        ),
    ],

)]
#[UniqueEntity(
    fields: ['module', 'name'],
    message: 'This menu name already exist in that module',
    errorPath: 'name',
)]
//#[UniqueEntity(
//    fields: ['module', 'position'],
//    message: 'This position already exist in that module on {{ value }}',
//    errorPath: 'position',
//)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Menu:collection', 'post:Menu', 'put:Menu', 'get:Menu:item', 'get:Permission:collection', 'get:Role:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 110)]
    #[Assert\NotBlank]
    #[Groups(['get:Menu:collection', 'post:Menu', 'put:Menu', 'get:Menu:item', 'get:Permission:collection', 'get:Role:collection'])]
    private ?string $name = null;

    #[ORM\Column(length: 110, nullable: true)]
    #[Groups(['get:Menu:collection', 'post:Menu', 'put:Menu', 'get:Menu:item'])]
    private ?string $title = null;

    #[ORM\ManyToOne]
    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Menu:collection', 'post:Menu', 'put:Menu', 'get:Permission:collection'])]
    private ?Module $module = null;

    #[ORM\Column(length: 110, nullable: true)]
    #[Groups(['get:Menu:collection', 'post:Menu', 'put:Menu', 'get:Permission:collection'])]
    private ?string $headTitle1 = null;

    #[ORM\Column(length: 110, nullable: true)]
    #[Groups(['get:Menu:collection', 'post:Menu', 'put:Menu', 'get:Permission:collection'])]
    private ?string $path = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Menu:collection', 'post:Menu', 'put:Menu', 'get:Permission:collection'])]
    private ?string $icon = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Menu:collection', 'post:Menu', 'put:Menu', 'get:Permission:collection'])]
    private ?string $type = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Menu:collection', 'post:Menu', 'put:Menu', 'get:Permission:collection'])]
    private ?string $badgeType = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Menu:collection', 'post:Menu', 'put:Menu', 'get:Permission:collection'])]
    private ?string $badgeValue = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Menu:collection', 'post:Menu', 'put:Menu', 'get:Permission:collection'])]
    private ?bool $active = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[Groups(['get:Menu:collection', 'post:Menu', 'put:Menu', 'get:Permission:collection'])]
    private ?self $parent = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Menu:collection', 'post:Menu', 'put:Menu', 'get:Permission:collection'])]
    private ?int $position = null;

    #[ORM\ManyToMany(targetEntity: Permission::class, inversedBy: 'menus')]
    #[Groups(['get:Menu:collection', 'get:Menu:item', 'post:Menu', 'put:Menu', 'get:Permission:collection'])]
    #[ORM\JoinTable(name: 'security_menu_permission')]
    private Collection $permissions;

    public function __construct()
    {
        $this->active = false;
        $this->position = null;
        $this->permissions = new ArrayCollection();
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

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getHeadTitle1(): ?string
    {
        return $this->headTitle1;
    }

    public function setHeadTitle1(?string $headTitle1): self
    {
        $this->headTitle1 = $headTitle1;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getBadgeType(): ?string
    {
        return $this->badgeType;
    }

    public function setBadgeType(?string $badgeType): self
    {
        $this->badgeType = $badgeType;

        return $this;
    }

    public function getBadgeValue(): ?string
    {
        return $this->badgeValue;
    }

    public function setBadgeValue(?string $badgeValue): self
    {
        $this->badgeValue = $badgeValue;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
        }

        return $this;
    }

    public function removePermission(Permission $permission): self
    {
        $this->permissions->removeElement($permission);

        return $this;
    }

    public function __toString(): string
    {
        // TODO: Implement __toString() method.
        return $this->getName();
    }
}
