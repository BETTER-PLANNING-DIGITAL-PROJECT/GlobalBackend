<?php

namespace App\Entity\School\Study\Teacher;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\School\Study\Teacher\HomeWork\GetStudentReplyPerStudentController;
use App\Controller\School\Study\Teacher\HomeWork\GetStudentReplyPerTeacherController;
use App\Controller\School\Study\Teacher\HomeWork\StudentReplyHomeWorkCreateController;
use App\Controller\School\Study\Teacher\HomeWork\StudentReplyHomeWorkEditController;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\User;
use App\Repository\School\Study\Teacher\HomeWorkStudentReplyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: HomeWorkStudentReplyRepository::class)]
#[ORM\Table(name: 'school_home_work_student_reply')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/home-work-student-reply/get/by/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:HomeWorkStudentReply:item'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/get/home-work-student-reply/per/student',
            controller: GetStudentReplyPerStudentController::class,
            normalizationContext: [
                'groups' => ['get:HomeWorkStudentReply:collection'],
            ],
        ),

        new GetCollection(
            uriTemplate: '/get/home-work-student-reply/per/teacher',
            controller: GetStudentReplyPerTeacherController::class,
            normalizationContext: [
                'groups' => ['get:HomeWorkStudentReply:collection'],
            ],
        ),

        new GetCollection(
            uriTemplate: '/home-work-student-reply/get',
            normalizationContext: [
                'groups' => ['get:HomeWorkStudentReply:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/home-work-student-reply/create',
            controller: StudentReplyHomeWorkCreateController::class,
            denormalizationContext: [
                'groups' => ['write:HomeWorkStudentReply'],
            ],
            deserialize: false,
        ),
        new Post(
            uriTemplate: '/home-work-student-reply/edit/{id}',
            requirements: ['id' => '\d+'],
            controller: StudentReplyHomeWorkEditController::class,
            denormalizationContext: [
                'groups' => ['write:HomeWorkStudentReply'],
            ],
            deserialize: false,
        ),
        new Delete(
            uriTemplate: '/home-work-student-reply/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class HomeWorkStudentReply
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:HomeWorkStudentReply:collection', 'write:HomeWorkStudentReply', 'get:HomeWorkStudentReply:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:HomeWorkStudentReply:collection', 'write:HomeWorkStudentReply', 'get:HomeWorkStudentReply:item'])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:HomeWorkStudentReply:collection', 'write:HomeWorkStudentReply', 'get:HomeWorkStudentReply:item'])]
    private ?string $file = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['get:HomeWorkStudentReply:collection', 'write:HomeWorkStudentReply', 'get:HomeWorkStudentReply:item'])]
    private ?\DateTimeInterface $publishAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:HomeWorkStudentReply:collection', 'write:HomeWorkStudentReply', 'get:HomeWorkStudentReply:item'])]
    private ?string $comment = null;

    #[ORM\ManyToOne]
    #[Groups(['get:HomeWorkStudentReply:collection', 'write:HomeWorkStudentReply', 'get:HomeWorkStudentReply:item'])]
    private ?HomeWorkRegistration $homeWorkRegistration = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:HomeWorkStudentReply:collection', 'write:HomeWorkStudentReply', 'get:HomeWorkStudentReply:item'])]
    private ?string $fileName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:HomeWorkStudentReply:collection', 'write:HomeWorkStudentReply', 'get:HomeWorkStudentReply:item'])]
    private ?string $fileType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:HomeWorkStudentReply:collection', 'write:HomeWorkStudentReply', 'get:HomeWorkStudentReply:item'])]
    private ?int $fileSize = null;

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

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): self
    {
        $this->file = $file;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getHomeWorkRegistration(): ?HomeWorkRegistration
    {
        return $this->homeWorkRegistration;
    }

    public function setHomeWorkRegistration(?HomeWorkRegistration $homeWorkRegistration): self
    {
        $this->homeWorkRegistration = $homeWorkRegistration;

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
}
