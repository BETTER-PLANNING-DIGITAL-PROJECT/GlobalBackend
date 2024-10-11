<?php

namespace App\Entity\School\Schooling\Configuration;

use ApiPlatform\Metadata\ApiResource;
use App\Controller\DeleteSelectedResourceController;
use App\Entity\Security\Session\Year;
use App\Repository\School\Schooling\Configuration\RegimeRepository;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\User;
use App\Entity\Setting\Location\Country;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RegimeRepository::class)]
#[ORM\Table(name: 'school_regime')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/regime/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Regime:item']
            ]
        ),
        new GetCollection(
            uriTemplate: '/get/regime',
            normalizationContext: [
                'groups' => [ 'get:Regime:collection']
            ]
        ),
        new Post(
            uriTemplate: '/create/regime',
            denormalizationContext: [
                'groups' => ['post:Regime'],
            ],
            processor: SystemProcessor::class
        ),
        new Put(
            uriTemplate: '/edit/regime/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['put:Regime'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/regime/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/delete/selected/regime',
            controller: DeleteSelectedResourceController::class,
            openapiContext: [
                "summary" => "Restore collections of api resource",
            ],
        ),
    ],
)]
class Regime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Regime:collection', 'get:Tuition:collection', 'get:StudentRegistration:collection', 'get:StudentOnline:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([ 'get:Regime:collection','post:Regime','put:Regime'])]
    private ?School $school = null;
    
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([ 'get:Regime:collection','post:Regime','put:Regime'])]
    private ?Campus $campus = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([ 'get:Regime:collection','post:Regime','put:Regime'])]
    private ?Country $country = null;

    #[ORM\Column(length: 100)]
    #[Groups([ 'get:Regime:collection','post:Regime','put:Regime', 'get:Tuition:collection', 'get:StudentRegistration:collection', 'get:StudentOnline:collection'])]
    private ?string $regime = null;

    #[ORM\Column(length: 255)]
    #[Groups([ 'get:Regime:collection','post:Regime','put:Regime','get:StudentRegistration:collection'])]
    private ?string $remarks = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([ 'get:Regime:collection','post:Regime','put:Regime'])]
    private ?Institution $institution = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRegime(): ?string
    {
        return $this->regime;
    }

    public function setRegime(string $regime): self
    {
        $this->regime = $regime;

        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(string $remarks): self
    {
        $this->remarks = $remarks;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

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

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

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
