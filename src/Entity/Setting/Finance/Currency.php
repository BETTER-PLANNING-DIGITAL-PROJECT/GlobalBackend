<?php

namespace App\Entity\Setting\Finance;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\User;
use App\Repository\Setting\Finance\CurrencyRepository;
use App\State\Processor\Global\SettingProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
#[ORM\Table(name: 'setting_currency')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/currency/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Currency:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/currency',
            normalizationContext: [
                'groups' => ['get:Currency:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/currency',
            denormalizationContext: [
                'groups' => ['write:Currency'],
            ],
            processor: SettingProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/currency/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:Currency'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/currency/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['code'],
    message: 'This code already exist.',
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This name already exist.',
)]
class Currency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Currency:collection','get:CashDesk:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:Currency:collection', 'write:Currency','get:CashDesk:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Currency:collection', 'write:Currency','get:CashDesk:collection'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Currency:collection', 'write:Currency'])]
    private ?bool $isDefault = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    private ?User $user = null;

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

    public function isIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(?bool $isDefault): self
    {
        $this->isDefault = $isDefault;

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
}
