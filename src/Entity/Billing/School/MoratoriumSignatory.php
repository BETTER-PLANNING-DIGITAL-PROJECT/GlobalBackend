<?php

namespace App\Entity\Billing\School;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\Billing\School\MoratoriumSignatoryRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MoratoriumSignatoryRepository::class)]
#[ORM\Table(name: 'billing_school_moratorium_signatory')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/moratorium-signatory',
            normalizationContext: [
                'groups' => ['get:MoratoriumSignatory:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/moratorium-signatory',
            denormalizationContext: [
                'groups' => ['write:MoratoriumSignatory'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/moratorium-signatory/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:MoratoriumSignatory'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/moratorium-signatory/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class MoratoriumSignatory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:MoratoriumSignatory:collection','get:Increase:collection','get:Discount:collection','get:Moratorium:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:MoratoriumSignatory:collection', 'write:MoratoriumSignatory'])]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:MoratoriumSignatory:collection', 'write:MoratoriumSignatory'])]
    private ?School $school = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:MoratoriumSignatory:collection', 'write:MoratoriumSignatory','get:Discount:collection','get:Increase:collection','get:Moratorium:collection'])]
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
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): static
    {
        $this->school = $school;

        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): static
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

}
