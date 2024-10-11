<?php

namespace App\Entity\School\Study\TimeTable;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Entity\Security\Session\Year;
use App\Entity\Setting\School\PeriodType;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\User;
use App\Repository\School\Study\TimeTable\TimeTablePeriodRepository;
use App\State\Processor\Global\SystemProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TimeTablePeriodRepository::class)]
#[ORM\Table(name: 'school_time_table_period')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/timetable-period/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:TimeTablePeriod:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/timetable-period',
            normalizationContext: [
                'groups' => ['get:TimeTablePeriod:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/timetable-period',
            denormalizationContext: [
                'groups' => ['write:TimeTablePeriod'],
            ],
            processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/timetable-period/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTablePeriod'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/timetable-period/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class TimeTablePeriod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:TimeTablePeriod:collection','get:TimeTableModel:collection', 'get:TimeTableModelDay:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:TimeTablePeriod:collection', 'write:TimeTablePeriod'])]
    private ?PeriodType $type = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:TimeTablePeriod:collection', 'write:TimeTablePeriod','get:TimeTableModel:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[Groups(['get:TimeTablePeriod:collection', 'write:TimeTablePeriod'])]
    private ?Year $year = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTablePeriod:collection', 'write:TimeTablePeriod'])]
    private ?bool $isEnable = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->isEnable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?PeriodType
    {
        return $this->type;
    }

    public function setType(?PeriodType $type): self
    {
        $this->type = $type;

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

    public function isIsEnable(): ?bool
    {
        return $this->isEnable;
    }

    public function setIsEnable(?bool $isEnable): self
    {
        $this->isEnable = $isEnable;

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
