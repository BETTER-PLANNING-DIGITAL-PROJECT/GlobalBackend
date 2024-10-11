<?php

namespace App\Entity\School\Study\Teacher;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\School\Study\Teacher\HomeWork\GetHomeWorkRegistrationByHomeWorkIdController;
use App\Entity\School\Schooling\Registration\StudentRegistration;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\State\Current\InstitutionProcessor;
use App\Repository\School\Study\Teacher\HomeWorkRegistrationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: HomeWorkRegistrationRepository::class)]
#[ORM\Table(name: 'school_home_work_registration')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/home-work-registration-by-id/get/{id}',
            controller: GetHomeWorkRegistrationByHomeWorkIdController::class,
            normalizationContext: [
                'groups' => ['get:HomeWorkRegistration:collection'],
            ]
        ),

        new GetCollection(
            uriTemplate: '/home-work-registration/get',
            normalizationContext: [
                'groups' => ['get:HomeWorkRegistration:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/home-work-registration/create',
            denormalizationContext: [
                'groups' => ['write:HomeWorkRegistration'],
            ],
            processor: InstitutionProcessor::class,
        ),
        new Put(
            uriTemplate: '/home-work-registration/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:HomeWorkRegistration'],
            ],
        ),
        new Delete(
            uriTemplate: '/home-work-registration/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class HomeWorkRegistration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:HomeWorkRegistration:collection', 'get:HomeWorkStudentReply:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:HomeWorkRegistration:collection', 'write:HomeWorkRegistration', 'get:HomeWorkStudentReply:collection'])]
    private ?StudentRegistration $student = null;

    #[ORM\ManyToOne]
    #[Groups(['get:HomeWorkRegistration:collection', 'write:HomeWorkRegistration', 'get:HomeWorkStudentReply:collection'])]
    private ?HomeWork $homeWork = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:HomeWorkRegistration:collection', 'write:HomeWorkRegistration'])]
    private ?bool $isReceived = null;

    #[ORM\ManyToOne]
    #[Groups(['get:HomeWorkRegistration:collection', 'write:HomeWorkRegistration'])]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?bool $is_enable = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
        $this->isReceived = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?StudentRegistration
    {
        return $this->student;
    }

    public function setStudent(?StudentRegistration $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getHomeWork(): ?HomeWork
    {
        return $this->homeWork;
    }

    public function setHomeWork(?HomeWork $homeWork): self
    {
        $this->homeWork = $homeWork;

        return $this;
    }

    public function isIsReceived(): ?bool
    {
        return $this->isReceived;
    }

    public function setIsReceived(bool $isReceived): self
    {
        $this->isReceived = $isReceived;

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

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): self
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
