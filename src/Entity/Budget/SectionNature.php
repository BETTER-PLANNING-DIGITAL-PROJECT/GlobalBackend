<?php

namespace App\Entity\Budget;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\User;
use App\Repository\Budget\SectionNatureRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SectionNatureRepository::class)]
#[ORM\Table(name: 'budget_section_nature')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/section-nature/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:SectionNature:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/section-nature',
            normalizationContext: [
                'groups' => ['get:SectionNature:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/section-nature',
            denormalizationContext: [
                'groups' => ['write:SectionNature'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/section-nature/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SectionNature'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/section-nature/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class SectionNature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:SectionNature:collection','get:BudgetHeading:collection', 'get:BudgetHeading:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Code cannot be smaller than {{ limit }} characters',
        maxMessage: 'Code cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:SectionNature:collection', 'write:SectionNature', 'get:BudgetHeading:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Name cannot be smaller than {{ limit }} characters',
        maxMessage: 'Name cannot be longer than {{ limit }} characters',
    )]
    #[Groups(['get:SectionNature:collection', 'write:SectionNature','get:BudgetHeading:collection'])]
    private ?string $name = null;

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
        $this->is_archive = false;
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
