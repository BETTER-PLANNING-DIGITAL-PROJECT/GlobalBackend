<?php

namespace App\Entity\School\Study\Teacher;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\School\Study\Teacher\HomeWork\CreateHomeWorkController;
use App\Controller\School\Study\Teacher\HomeWork\EditHomeWorkController;
use App\Controller\School\Study\Teacher\HomeWork\GetHomeWorkByStudentController;
use App\Controller\School\Study\Teacher\HomeWork\GetHomeWorkByTeacherController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\School\Study\Program\TeacherCourseRegistration;
use App\State\Processor\School\Study\Teacher\HomeWork\PublishHomeWorkProcessor;
use App\State\Processor\StudentReceivedHomeWorkProcessor;
use Doctrine\DBAL\Types\Types;
use App\Repository\School\Study\Teacher\HomeWorkRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: HomeWorkRepository::class)]
#[ORM\Table(name: 'school_home_work')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/home-work/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:HomeWork:collection'],
            ],
        ),
        new Get(
            uriTemplate: '/home-work/get/by/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:HomeWork:item'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/home-work/get',
            normalizationContext: [
                'groups' => ['get:HomeWork:collection'],
            ],
        ),

        new GetCollection(
            uriTemplate: '/get/home-work/by/teacher',
            controller: GetHomeWorkByTeacherController::class,
            normalizationContext: [
                'groups' => ['get:HomeWork:collection'],
            ],
        ),

        new GetCollection(
            uriTemplate: '/get/home-work/by/student',
            controller: GetHomeWorkByStudentController::class,
            normalizationContext: [
                'groups' => ['get:HomeWork:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/home-work/create',
            controller: CreateHomeWorkController::class,
            denormalizationContext: [
                'groups' => ['write:HomeWork'],
            ],
            deserialize: false,
        ),
        new Post(
            uriTemplate: '/home-work/edit/{id}',
            requirements: ['id' => '\d+'],
            controller: EditHomeWorkController::class,
            denormalizationContext: [
                'groups' => ['write:HomeWork'],
            ],
            deserialize: false,
        ),
//        publish begin
        new Delete(
            uriTemplate: '/home-work-publish/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModel'],
            ],
            processor: PublishHomeWorkProcessor::class,

        ),
//        publish end

        new Delete(
            uriTemplate: '/home-work-received/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:TimeTableModel'],
            ],
            processor: StudentReceivedHomeWorkProcessor::class,

        ),
        new Delete(
            uriTemplate: '/home-work/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class HomeWork
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:HomeWork:collection', 'get:HomeWork:item', 'get:HomeWorkRegistration:collection', 'get:HomeWorkStudentReply:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:HomeWork:collection', 'write:HomeWork', 'get:HomeWork:item', 'get:HomeWorkRegistration:collection'])]
    private ?string $title = null;

    #[ORM\ManyToOne]
    #[Groups(['get:HomeWork:collection', 'write:HomeWork', 'get:HomeWork:item', 'get:HomeWorkStudentReply:collection'])]
    private ?TeacherCourseRegistration $course = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:HomeWork:collection', 'write:HomeWork', 'get:HomeWork:item'])]
    private ?string $file = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:HomeWork:collection', 'write:HomeWork', 'get:HomeWork:item'])]
    private ?string $comment = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['get:HomeWork:collection', 'write:HomeWork', 'get:HomeWork:item'])]
    private ?\DateTimeInterface $publishAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['get:HomeWork:collection', 'write:HomeWork', 'get:HomeWork:item'])]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:HomeWork:collection', 'write:HomeWork', 'get:HomeWork:item'])]
    private ?bool $isPublish = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:HomeWork:collection', 'write:HomeWork', 'get:HomeWork:item'])]
    private ?string $fileName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:HomeWork:collection', 'write:HomeWork', 'get:HomeWork:item'])]
    private ?string $fileType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:HomeWork:collection', 'write:HomeWork', 'get:HomeWork:item'])]
    private ?int $fileSize = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

    #[ORM\ManyToOne]
    #[Groups(['get:HomeWork:collection', 'write:HomeWork'])]
    private ?Year $year = null;

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
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCourse(): ?TeacherCourseRegistration
    {
        return $this->course;
    }

    public function setCourse(?TeacherCourseRegistration $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getPublishAt(): ?\DateTimeInterface
    {
        return $this->publishAt;
    }

    public function setPublishAt(?\DateTimeInterface $publishAt): self
    {
        $this->publishAt = $publishAt;

        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeInterface $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function isIsPublish(): ?bool
    {
        return $this->isPublish;
    }

    public function setIsPublish(?bool $isPublish): self
    {
        $this->isPublish = $isPublish;

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

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(?string $fileType): self
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(?int $fileSize): self
    {
        $this->fileSize = $fileSize;

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
