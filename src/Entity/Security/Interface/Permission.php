<?php

namespace App\Entity\Security\Interface;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\Security\Interface\PermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Table(name: 'security_permission')]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/get/permission/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Permission:item'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/permissions',
            normalizationContext: [
                'groups' => ['get:Permission:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/permission',
            denormalizationContext: [
                'groups' => ['post:Permission']
            ]
        ),
        new Put(
            uriTemplate: '/edit/permission/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['put:Permission']
            ]
        ),
        new Delete(
            uriTemplate: '/delete/permission/{id}',
            requirements: ['id' => '\d+'],
        ),
//        new Delete(
//            uriTemplate: '/delete/selected/permission',
//            controller: DeleteSelectedResourceController::class,
//            openapiContext: [
//                "summary" => "Deletes multiple MyEntity resources.",
//                "requestBody" => [
//                    "description" => "Array of MyEntity IDs to delete.",
//                    "required" => true,
//                    "content"=>[
//                        "application/json"=>[
//                            "schema"=> [
//                                "type"=>"array",
//                                "items"=> ["type"=>"integer"]
//                            ],
//                        ],
//                    ]
//                ],
//
//                "responses"=>[
//                    "204" => ["description" => "MyEntity resources deleted successfully."],
//                    "400" => ["description" => "Invalid request body."],
//                    "404" => ["description" => "MyEntity resources not found."]
//                ]
//
//            ]
//        ),
    ],

)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This permission name is already exist.',
)]
class Permission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Permission:collection', 'get:Menu:collection', 'get:Role:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 110)]
    #[Assert\NotBlank]
    #[Groups(['get:Permission:collection', 'get:Menu:collection', 'post:Permission', 'put:Permission', 'get:Role:collection'])]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Menu::class, mappedBy: 'permissions')]
    private Collection $menus;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
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

    /**
     * @return Collection<int, Menu>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): self
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->addPermission($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): self
    {
        if ($this->menus->removeElement($menu)) {
            $menu->removePermission($this);
        }

        return $this;
    }

}
