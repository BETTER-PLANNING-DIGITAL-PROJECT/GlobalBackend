<?php

namespace App\Entity\School\Study\TimeTable;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Study\TimeTable\TimeTableModelDayRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TimeTableModelDayRepository::class)]
#[ORM\Table(name: 'school_time_table_model_day')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/timetable-model-day',
            normalizationContext: [
                'groups' => ['get:TimeTableModelDay:collection'],
            ],
        ),
    ]
)]
class TimeTableModelDay
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:TimeTableModelDay:collection','get:TimeTableModelDayCell:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Day may not be blank!')]
    #[Groups(['get:TimeTableModelDay:collection', 'write:TimeTableModelDay','get:TimeTableModelDayCell:collection'])]
    private ?string $day = null;

    #[ORM\ManyToOne]
    #[Groups(['get:TimeTableModelDay:collection', 'write:TimeTableModelDay', 'get:TimeTableModelDayCell:collection'])]
    private ?TimeTableModel $model = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModelDay:collection', 'write:TimeTableModelDay','get:TimeTableModelDayCell:collection'])]
    private ?\DateTimeInterface $startAt = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['get:TimeTableModelDay:collection', 'write:TimeTableModelDay','get:TimeTableModelDayCell:collection'])]
    private ?\DateTimeInterface $endAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModelDay:collection', 'write:TimeTableModelDay'])]
    private ?bool $isChecked = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:TimeTableModelDay:collection', 'write:TimeTableModelDay'])]
    private ?bool $isValidated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Year $year = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isEnable = null;


    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->isEnable = true;
        $this->isValidated = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getDay(): ?string
    {
        return $this->day;
    }

    public function setDay(?string $day): self
    {
        $this->day = $day;

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

    public function getModel(): ?TimeTableModel
    {
        return $this->model;
    }

    public function setModel(?TimeTableModel $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }
    public function isIsChecked(): ?bool
    {
        return $this->isChecked;
    }

    public function setIsChecked(?bool $isChecked): self
    {
        $this->isChecked = $isChecked;

        return $this;
    }

    public function isIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(?bool $isValidated): self
    {
        $this->isValidated = $isValidated;

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
