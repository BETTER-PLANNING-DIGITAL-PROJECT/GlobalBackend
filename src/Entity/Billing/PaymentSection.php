<?php

namespace App\Entity\Billing;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Billing\PaymentSectionRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PaymentSectionRepository::class)]
#[ORM\Table(name: 'billing_payment_section')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/payment-section',
            normalizationContext: [
                'groups' => ['get:PaymentSection:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/payment-section',
            denormalizationContext: [
                'groups' => ['write:PaymentSection'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/payment-section/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:PaymentSection'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/payment-section/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class PaymentSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:PaymentSection:collection'])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PaymentSection:collection','write:PaymentSection','get:Collection:collection'])]
    private ?bool $IsStudentRequired = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:PaymentSection:collection','write:PaymentSection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:PaymentSection:collection','write:PaymentSection','get:Collection:collection'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PaymentSection:collection','write:PaymentSection'])]
    private ?float $amount = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PaymentSection:collection','write:PaymentSection'])]
    private ?Year $year = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isIsStudentRequired(): ?bool
    {
        return $this->IsStudentRequired;
    }

    public function setIsStudentRequired(?bool $IsStudentRequired): self
    {
        $this->IsStudentRequired = $IsStudentRequired;

        return $this;
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

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): self
    {
        $this->year = $year;

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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

}
