<?php

namespace App\Entity\Security\Institution;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Partner\Contact;
use App\Entity\Security\User;
use App\Repository\Security\Institution\BranchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BranchRepository::class)]
#[ORM\Table(name: 'security_branch')]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/get/branch/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Branch:item'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/branches',
            normalizationContext: [
                'groups' => ['get:Branch:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/branch',
            denormalizationContext: [
                'groups' => ['write:Branch']
            ]
        ),
        new Put(
            uriTemplate: '/edit/branch/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Branch']
            ]
        ),
        new Delete(
            uriTemplate: '/delete/branch/{id}',
            requirements: ['id' => '\d+'],
        ),
    ],

)]
#[UniqueEntity(
    fields: ['code'],
    message: 'This branch already exist',
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This branch already exist',
)]
class Branch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Branch:collection','get:Branch:item', 'get:User:collection', 'get:StockMovement:collection', 'get:Warehouse:collection', 'get:ItemCategory:collection', 'get:Customer:collection', 'get:Reception:collection', 'get:Delivery:collection', 'get:Supplier:collection', 'get:Location:collection', 'get:SaleInvoice:collection', 'get:SaleReturnInvoice:collection', 'get:SaleSettlement:collection', 'get:PurchaseInvoice:collection', 'get:PurchaseSettlement:collection','get:CashDesk:collection','get:CashDeskOperation:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item', 'get:StockMovement:collection', 'get:User:collection', 'get:BankAccount:collection', 'get:Item:collection', 'get:Warehouse:collection', 'get:ItemCategory:collection', 'get:Customer:collection', 'get:Reception:collection', 'get:Delivery:collection', 'get:Supplier:collection', 'get:Location:collection', 'get:SaleInvoice:collection', 'get:SaleReturnInvoice:collection', 'get:SaleSettlement:collection', 'get:PurchaseInvoice:collection', 'get:PurchaseSettlement:collection','get:CashDesk:collection','get:CashDeskOperation:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item', 'get:StockMovement:collection', 'get:User:collection', 'get:BankAccount:collection', 'get:Item:collection', 'get:Warehouse:collection', 'get:ItemCategory:collection', 'get:Customer:collection', 'get:Reception:collection', 'get:Delivery:collection', 'get:Supplier:collection', 'get:Location:collection', 'get:SaleInvoice:collection', 'get:SaleReturnInvoice:collection', 'get:SaleSettlement:collection', 'get:PurchaseInvoice:collection', 'get:PurchaseSettlement:collection','get:CashDesk:collection','get:CashDeskOperation:collection'])]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(
        max: 50,
        maxMessage: 'Email cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item'])]
    private ?string $email = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(
        max: 30,
        maxMessage: 'Phone number cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item'])]
    private ?string $phone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item'])]
    private ?string $address = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item'])]
    private ?string $website = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item'])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item'])]
    private ?string $taxPayerNumber = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item'])]
    private ?string $businessNumber = null;





    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item'])]
    private ?string $cnps = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item'])]
    private ?string $idepKeyNumber = null;

    #[ORM\Column(length: 110, nullable: true)]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item'])]
    private ?string $nsifEmployerMatriculeNo = null;

    #[ORM\Column(length: 110, nullable: true)]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item'])]
    private ?string $nsifRegime = null;





    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item'])]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(inversedBy: 'branch', cascade: ['persist', 'remove'])]
    private ?Contact $contact = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'userBranches')]
    #[ORM\JoinTable(name: 'security_branch_users')]
    #[Groups(['get:Branch:collection', 'write:Branch', 'get:Branch:item'])]
    private Collection $users;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
        $this->users = new ArrayCollection();
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getTaxPayerNumber(): ?string
    {
        return $this->taxPayerNumber;
    }

    public function setTaxPayerNumber(?string $taxPayerNumber): self
    {
        $this->taxPayerNumber = $taxPayerNumber;

        return $this;
    }

    public function getBusinessNumber(): ?string
    {
        return $this->businessNumber;
    }

    public function setBusinessNumber(?string $businessNumber): self
    {
        $this->businessNumber = $businessNumber;

        return $this;
    }

    public function getCnps(): ?string
    {
        return $this->cnps;
    }

    public function setCnps(?string $cnps): self
    {
        $this->cnps = $cnps;

        return $this;
    }

    public function getIdepKeyNumber(): ?string
    {
        return $this->idepKeyNumber;
    }

    public function setIdepKeyNumber(?string $idepKeyNumber): self
    {
        $this->idepKeyNumber = $idepKeyNumber;

        return $this;
    }

    public function getNsifEmployerMatriculeNo(): ?string
    {
        return $this->nsifEmployerMatriculeNo;
    }

    public function setNsifEmployerMatriculeNo(?string $nsifEmployerMatriculeNo): self
    {
        $this->nsifEmployerMatriculeNo = $nsifEmployerMatriculeNo;

        return $this;
    }

    public function getNsifRegime(): ?string
    {
        return $this->nsifRegime;
    }

    public function setNsifRegime(?string $nsifRegime): self
    {
        $this->nsifRegime = $nsifRegime;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function isIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(bool $is_enable): static
    {
        $this->is_enable = $is_enable;

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

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addUserBranch($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeUserBranch($this);
        }

        return $this;
    }
}