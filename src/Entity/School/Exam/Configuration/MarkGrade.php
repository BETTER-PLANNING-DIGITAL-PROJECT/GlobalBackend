<?php

namespace App\Entity\School\Exam\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteSelectedResourceController;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\MarkGradeRepository;
use App\State\SchoolMarkGradeAddProcessor;
use App\State\SchoolMarkGradeEditProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MarkGradeRepository::class)]
#[ORM\Table(name: 'school_mark_grade')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/mark/grade/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:MarkGrade:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: 'school/mark/grade/get',
            normalizationContext: [
                'groups' => ['get:MarkGrade:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/school/mark/grade/create',
            denormalizationContext: [
                'groups' => ['write:MarkGrade'],
            ],
            processor: SchoolMarkGradeAddProcessor::class,
        ),
        new Put(
            uriTemplate: 'school/mark/grade/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:MarkGrade'],
            ],
            processor: SchoolMarkGradeEditProcessor::class,
        ),
        new Delete(
            uriTemplate: 'school/mark/grade/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
        new Delete(
            uriTemplate: '/school/mark/grade/delete/multiple',
            controller: DeleteSelectedResourceController::class,
        ),
    ]
)]
class MarkGrade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:MarkGrade:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:MarkGrade:collection'])]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:MarkGrade:collection', 'write:MarkGrade'])]
    private ?School $school = null;

    #[ORM\Column]
    #[Groups(['get:MarkGrade:collection', 'write:MarkGrade'])]
    private ?string $code = null;

    #[ORM\Column]
    #[Groups(['get:MarkGrade:collection', 'write:MarkGrade'])]
    private ?float $min = null;

    #[ORM\Column]
    #[Groups(['get:MarkGrade:collection', 'write:MarkGrade'])]
    private ?float $max = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:MarkGrade:collection', 'write:MarkGrade'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['get:MarkGrade:collection', 'write:MarkGrade'])]
    private ?float $gpa = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?bool $is_enable = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?bool $is_archive = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    public function __toString(): string
    {
        // TODO: Implement __toString() method.
        return $this->getCode();
    }

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

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): MarkGrade
    {
        $this->year = $year;
        return $this;
    }


    /**
     * @return School|null
     */
    public function getSchool(): ?School
    {
        return $this->school;
    }

    /**
     * @param School|null $school
     * @return MarkGrade
     */
    public function setSchool(?School $school): MarkGrade
    {
        $this->school = $school;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     * @return MarkGrade
     */
    public function setCode(?string $code): MarkGrade
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getMin(): ?float
    {
        return $this->min;
    }

    /**
     * @param float|null $min
     * @return MarkGrade
     */
    public function setMin(?float $min): MarkGrade
    {
        $this->min = $min;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getMax(): ?float
    {
        return $this->max;
    }

    /**
     * @param float|null $max
     * @return MarkGrade
     */
    public function setMax(?float $max): MarkGrade
    {
        $this->max = $max;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return MarkGrade
     */
    public function setDescription(?string $description): MarkGrade
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getGpa(): ?float
    {
        return $this->gpa;
    }

    /**
     * @param float|null $gpa
     * @return MarkGrade
     */
    public function setGpa(?float $gpa): MarkGrade
    {
        $this->gpa = $gpa;
        return $this;
    }

    /**
     * @return Institution|null
     */
    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    /**
     * @param Institution|null $institution
     * @return MarkGrade
     */
    public function setInstitution(?Institution $institution): MarkGrade
    {
        $this->institution = $institution;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    /**
     * @param bool|null $is_enable
     * @return MarkGrade
     */
    public function setIsEnable(?bool $is_enable): MarkGrade
    {
        $this->is_enable = $is_enable;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    /**
     * @param bool|null $is_archive
     * @return MarkGrade
     */
    public function setIsArchive(?bool $is_archive): MarkGrade
    {
        $this->is_archive = $is_archive;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeImmutable|null $createdAt
     * @return MarkGrade
     */
    public function setCreatedAt(?\DateTimeImmutable $createdAt): MarkGrade
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeImmutable|null $updatedAt
     * @return MarkGrade
     */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): MarkGrade
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return MarkGrade
     */
    public function setUser(?User $user): MarkGrade
    {
        $this->user = $user;
        return $this;
    }
}
