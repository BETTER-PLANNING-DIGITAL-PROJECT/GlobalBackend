<?php

namespace App\Entity\Security;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Entity\Security\Interface\Menu;
use App\Entity\Security\Interface\Module;
use App\Entity\Security\Interface\Permission;
use App\Repository\Security\RoleRepository;
use App\State\Provider\Security\TreeViewWhenCreateProfileProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table(name: 'security_role')]
#[ApiResource( operations: [
    new Get(
        uriTemplate: '/get/role/{id}',
        requirements: ['id' => '\d+'],
        normalizationContext: [
            'groups' => ['get:Role:item'],
        ],
    ),
    new Post(
        uriTemplate: '/create/role',
//        controller: CreateRoleController::class,
//        openapiContext: [
//            "summary" => "Add multiple Role resources.",
//            "requestBody" => [
//                "description" => "Array of MyEntity IDs to delete.",
//                "required" => true,
//                "content"=>[
//                    "application/json"=>[
//                        "schema"=> [
//                            "type"=>"array",
//                            "items"=> ["type"=>"integer"]
//                        ],
//                    ],
//                ]
//            ],
//
//            "responses"=>[
//                "204" => ["description" => "MyEntity resources deleted successfully."],
//                "400" => ["description" => "Invalid request body."],
//                "404" => ["description" => "MyEntity resources not found."]
//            ]
//
//        ],
        denormalizationContext: [
            'groups' => ['post:Role']
        ],
        //deserialize: false,
    ),

],


)]
#[UniqueEntity(
    fields: ['name', 'module', 'menu', 'privilege'],
    message: 'This role is already use.',
    errorPath: 'name',
)]
#[GetCollection(uriTemplate: '/get/custom/roles', provider: TreeViewWhenCreateProfileProvider::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Role:collection', 'get:Role:item', 'get:User:collection', 'get:User:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 110)]
    #[Assert\NotBlank]
    #[Groups(['get:Role:collection', 'post:Role', 'put:Role', 'get:Role:item', 'get:User:collection', 'get:User:item'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Role:collection', 'post:Role', 'put:Role'])]
    private ?Module $module = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Role:collection', 'post:Role', 'put:Role'])]
    private ?Menu $menu = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Role:collection', 'post:Role', 'put:Role'])]
    private ?Permission $privilege = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Role:collection', 'post:Role', 'put:Role'])]
    private ?Profile $profile = null;

    #[ORM\Column]
    private ?bool $status = null;

    // institution
    // branch
    // user
    // created at

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

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): self
    {
        $this->menu = $menu;

        return $this;
    }

    public function getPrivilege(): ?Permission
    {
        return $this->privilege;
    }

    public function setPrivilege(?Permission $privilege): self
    {
        $this->privilege = $privilege;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }
}
