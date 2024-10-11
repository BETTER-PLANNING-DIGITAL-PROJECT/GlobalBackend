<?php

namespace App\Entity\Partner;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Person\Civility;
use App\Entity\Treasury\BankAccount;
use App\Repository\Partner\ContactRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ORM\Table(name: 'partner_contact')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/contact/{id}/',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Contact:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/contact',
            normalizationContext: [
                'groups' => ['get:Contact:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/contact',
            denormalizationContext: [
                'groups' => ['write:Contact'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/contact/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Contact'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/contact/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]

)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Contact:collection','get:SaleInvoice:collection','get:SaleReturnInvoice:collection','get:SaleInvoice:collection','get:SaleSettlement:collection','get:Delivery:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Contact:collection', 'write:Contact','get:Delivery:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 110, nullable: true)]
    #[Groups(['get:Contact:collection', 'write:Contact','get:SaleInvoice:collection','get:SaleReturnInvoice:collection','get:SaleInvoice:collection','get:SaleSettlement:collection','get:Delivery:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Contact:collection', 'write:Contact'])]
    private ?Civility $civility = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Contact:collection', 'write:Contact','get:SaleInvoice:collection'])]
    private ?string $phone = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Contact:collection', 'write:Contact','get:SaleInvoice:collection'])]
    private ?string $email = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Contact:collection', 'write:Contact'])]
    private ?string $address = null;

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

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCivility(): ?Civility
    {
        return $this->civility;
    }

    public function setCivility(?Civility $civility): self
    {
        $this->civility = $civility;

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

}
