<?php

namespace App\Entity\Security;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Security\CreateUserController;
use App\Controller\Security\EditUserController;
use App\Controller\Security\Institution\GetUserBranchesController;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Repository\Security\UserRepository;
use App\State\Processor\Security\DeleteUserProcessor;
use App\State\Processor\Security\UpdateUserPasswordProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'security_user')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/user/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:User:item'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/get/user/by/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:User:item'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/get/users',
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:User:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/user-branches',
            controller: GetUserBranchesController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:User:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/user',
            controller: CreateUserController::class,
            normalizationContext: [
                'groups' => ['write:User'],
            ],
            deserialize: false,
        ),
        new Post(
            uriTemplate: '/edit/user/{id}',
            requirements: ['id' => '\d+'],
            controller: EditUserController::class,
            denormalizationContext: [
                'groups' => ['write:User'],
            ],
            deserialize: false,
        ),
        new Put(
            uriTemplate: '/update/user-branches/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:User'],
            ],
        ),
        new Post(
            uriTemplate: '/update/password/user/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:User'],
            ],
            processor: UpdateUserPasswordProcessor::class,
        ),
        new Delete(
            uriTemplate: '/delete/user/{id}',
            requirements: ['id' => '\d+'],
            processor: DeleteUserProcessor::class
        ),
//        new Delete(
//            uriTemplate: '/delete/states',
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
    fields: ['username'],
    message: 'This username is already in use on that system.',
)]
#[UniqueEntity(
    fields: ['email'],
    message: 'This email is already in use on that system.',
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:User:collection', 'get:User:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 110)]
    #[Groups(['get:User:collection', 'get:User:item', 'get:CashDesk:collection'])]
    #[Assert\NotBlank]
    private ?string $firstname = null;

    #[ORM\Column(length: 110)]
    #[Groups(['get:User:collection', 'get:User:item', 'get:CashDesk:collection'])]
    #[Assert\NotBlank]
    private ?string $lastname = null;

    #[ORM\Column(length: 30)]
    #[Assert\Length(
        max: 30,
        maxMessage: 'Phone number cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:User:collection', 'get:User:item'])]
    private ?string $phone = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Email(
        message: 'The email {{ value }} is not a valid email.',
    )]
    #[Groups(['get:User:collection', 'get:User:item'])]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Groups(['get:User:collection', 'get:User:item'])]
    private ?string $username = null;

    #[ORM\Column(length: 110)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 5,
        minMessage: 'Your password must be at least {{ limit }} characters long',
    )]
    #[Groups(['get:User:collection', 'get:User:item'])]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $is_lock = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $firstLoginAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastLoginAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $loginCount = null;

    #[ORM\Column(nullable: true)]
    private array $roles = [];

    #[ORM\Column(length: 110, nullable: true)]
    private ?string $token = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:User:collection', 'get:User:item'])]
    private ?string $picture = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:User:collection', 'get:User:item'])]
    private ?string $fileName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:User:collection', 'get:User:item'])]
    private ?string $fileType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:User:collection', 'get:User:item'])]
    private ?int $fileSize = null;

    #[ORM\ManyToOne]
    #[Groups(['get:User:collection', 'get:User:item', 'write:User'])]
    private ?Profile $profile = null;

    #[ORM\ManyToOne]
    #[Groups(['get:User:collection', 'get:User:item', 'write:User'])]
    private ?Branch $branch = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:User:collection', 'get:User:item'])]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    private ?Year $currentYear = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column]
    #[Groups(['get:User:collection', 'get:User:item'])]
    private ?bool $is_enable = null;

    #[ORM\Column]
    #[Groups(['get:User:collection', 'get:User:item'])]
    private ?bool $isBranchManager = null;

    #[ORM\ManyToMany(targetEntity: Branch::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'security_user_branches')]
    #[Groups(['get:User:collection', 'get:User:item', 'write:User'])]
    private Collection $userBranches;

    public function __construct()
    {
        $this->userBranches = new ArrayCollection();
        $this->is_lock = false;
        $this->createdAt = new \DateTime();
        $this->is_enable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function isIsLock(): ?bool
    {
        return $this->is_lock;
    }

    public function setIsLock(bool $is_lock): self
    {
        $this->is_lock = $is_lock;

        return $this;
    }

    public function getFirstLoginAt(): ?\DateTimeInterface
    {
        return $this->firstLoginAt;
    }

    public function setFirstLoginAt(?\DateTimeInterface $firstLoginAt): self
    {
        $this->firstLoginAt = $firstLoginAt;

        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeInterface
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeInterface $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function getLoginCount(): ?int
    {
        return $this->loginCount;
    }

    public function setLoginCount(?int $loginCount): self
    {
        $this->loginCount = $loginCount;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(?array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(bool $is_enable): self
    {
        $this->is_enable = $is_enable;

        return $this;
    }

    public function isIsBranchManager(): ?bool
    {
        return $this->isBranchManager;
    }

    public function setIsBranchManager(bool $isBranchManager): self
    {
        $this->isBranchManager = $isBranchManager;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        // TODO: Implement getUserIdentifier() method.
        return (string) $this->username;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(?string $fileType): self
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(?int $fileSize): self
    {
        $this->fileSize = $fileSize;

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

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): self
    {
        $this->profile = $profile;

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

    public function getCurrentYear(): ?Year
    {
        return $this->currentYear;
    }

    public function setCurrentYear(?Year $currentYear): self
    {
        $this->currentYear = $currentYear;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'phone' => $this->getPhone(),
            'email' => $this->getEmail(),
            'picture' => $this->getPicture(),
            'profile' => $this->getProfile() ? $this->getProfile()->getName() : '',
            'branch' => $this->getBranch() ? $this->getBranch()->getName() : '',
            'currentYear' => $this->getCurrentYear()
        ];
    }

    public function profileArray(): array
    {
        return [
            'id' => $this->getId(),
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'phone' => $this->getPhone(),
            'email' => $this->getEmail(),
        ];
    }

    /**
     * @return Collection<int, Branch>
     */
    public function getUserBranches(): Collection
    {
        return $this->userBranches;
    }

    public function addUserBranch(Branch $userBranch): self
    {
        if (!$this->userBranches->contains($userBranch)) {
            $this->userBranches->add($userBranch);
        }

        return $this;
    }

    public function removeUserBranch(Branch $userBranch): self
    {
        $this->userBranches->removeElement($userBranch);

        return $this;
    }
}
