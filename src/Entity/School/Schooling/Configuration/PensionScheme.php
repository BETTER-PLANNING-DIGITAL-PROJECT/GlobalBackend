<?php

namespace App\Entity\School\Schooling\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Location\Country;
use App\Repository\School\Schooling\Configuration\PensionSchemeRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PensionSchemeRepository::class)]
#[ORM\Table(name: 'school_pension_scheme')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/pension-scheme/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:PensionScheme:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/pension-scheme',
            normalizationContext: [
                'groups' => ['get:PensionScheme:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/pension-scheme',
            denormalizationContext: [
                'groups' => ['write:PensionScheme'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/pension-scheme/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:PensionScheme'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/pension-scheme/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/pension-scheme',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ]
)]
#[UniqueEntity(
    fields: ['name'],
    message: 'this name already exist',
)]
class PensionScheme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:PensionScheme:collection', 'get:Tuition:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:PensionScheme:collection', 'write:PensionScheme', 'get:Tuition:collection'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Remark may not be blank')]
    #[Groups(['get:PensionScheme:collection', 'write:PensionScheme', 'get:Tuition:collection'])]
    private ?string $remark = null;

    #[ORM\ManyToMany(targetEntity: School::class)]
    #[ORM\JoinTable(name: 'school_pension_scheme_school')]
    #[Groups(['get:PensionScheme:collection', 'write:PensionScheme'])]
    private Collection $schools;

    #[ORM\ManyToMany(targetEntity: Campus::class)]
    #[ORM\JoinTable(name: 'school_pension_scheme_campus')]
    #[Groups(['get:PensionScheme:collection', 'write:PensionScheme'])]
    private Collection $campuses;

    #[ORM\ManyToMany(targetEntity: Country::class)]
    #[ORM\JoinTable(name: 'school_pension_scheme_country')]
    #[Groups(['get:PensionScheme:collection', 'write:PensionScheme'])]
    private Collection $countries;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

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

    public function __construct()
    {
        $this->campuses = new ArrayCollection();
        $this->countries = new ArrayCollection();
        $this->schools = new ArrayCollection();

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

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(string $remark): static
    {
        $this->remark = $remark;

        return $this;
    }

    /**
     * @return Collection<int, Campus>
     */
    public function getCampuses(): Collection
    {
        return $this->campuses;
    }

    public function addCampus(Campus $campus): static
    {
        if (!$this->campuses->contains($campus)) {
            $this->campuses->add($campus);
        }

        return $this;
    }

    public function removeCampus(Campus $campus): static
    {
        $this->campuses->removeElement($campus);

        return $this;
    }

    /**
     * @return Collection<int, Country>
     */
    public function getCountries(): Collection
    {
        return $this->countries;
    }

    public function addCountry(Country $country): static
    {
        if (!$this->countries->contains($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    public function removeCountry(Country $country): static
    {
        $this->countries->removeElement($country);

        return $this;
    }

    /**
     * @return Collection<int, School>
     */
    public function getSchools(): Collection
    {
        return $this->schools;
    }

    public function addSchool(School $school): static
    {
        if (!$this->schools->contains($school)) {
            $this->schools->add($school);
        }

        return $this;
    }

    public function removeSchool(School $school): static
    {
        $this->schools->removeElement($school);

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
