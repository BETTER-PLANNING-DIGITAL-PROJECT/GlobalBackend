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
use App\Repository\Setting\Finance\ExpenseHeadingRepository;
use App\State\Processor\Global\SettingProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExpenseHeadingRepository::class)]
#[ORM\Table(name: 'setting_expense_heading')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/expense-heading/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:ExpenseHeading:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/expense-heading/get',
            normalizationContext: [
                'groups' => ['get:ExpenseHeading:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/expense-heading/create',
            denormalizationContext: [
                'groups' => ['post:ExpenseHeading'],
            ],
            processor: SettingProcessor::class,
        ),
        new Put(
            uriTemplate: '/expense-heading/{id}/edit',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['put:ExpenseHeading'],
            ],
        ),
        new Delete(
            uriTemplate: '/expense-heading/{id}/delete',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'This name already exist.',
)]
class ExpenseHeading
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:ExpenseHeading:collection', 'get:CostArea:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:ExpenseHeading:collection', 'post:ExpenseHeading', 'put:ExpenseHeading', 'get:CostArea:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    #[Groups(['get:ExpenseHeading:collection', 'write:ExpenseHeading'])]
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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
