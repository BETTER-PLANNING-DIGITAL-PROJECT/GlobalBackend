<?php

namespace App\Entity\School\Exam\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\GetServices\GetFormulaConditionController;
use App\Entity\School\Schooling\Configuration\Level;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\FormulaConditionRepository;
use App\State\Processor\School\Exam\Result\WriteFormulaConditionProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\String_;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FormulaConditionRepository::class)]
#[ORM\Table(name: 'school_formula_condition')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/formula/condition',
            normalizationContext: [
                'groups' => ['get:FormulaCondition:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/formula/condition/previous/level/{schoolId}/{levelId}',
            requirements: ['schoolId' => '\d+','levelId' => '\d+'],
            controller: GetFormulaConditionController::class,
            normalizationContext: [
                'groups' => ['get:FormulaCondition:collection'],
            ],
        ),
        new Get(
            uriTemplate: '/get/formula/condition/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:FormulaCondition:collection','get:FormulaCondition:Item'],
            ],
        ),new Post(
            uriTemplate: '/create/formula/condition',
            denormalizationContext: [
                'groups' => ['write:FormulaCondition'],
            ],
            processor: WriteFormulaConditionProcessor::class
        ),new Put(
            uriTemplate: '/edit/formula/condition/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:FormulaCondition'],
            ],
            processor: WriteFormulaConditionProcessor::class
        ),
        new Delete(
            uriTemplate: '/delete/formula/condition/{id}',
            requirements: ['id' => '\d+']
        )
    ]
)]
#[UniqueEntity(
    fields: ['school','name'],
    message: 'double name on the same school',
    errorPath: 'name'
)]
#[UniqueEntity(
    fields: ['school','level','isMain'],
    message: 'double prinicipal formula on the same level',
    repositoryMethod: 'isSecondPrincipal',
    errorPath: 'level',
)]

class FormulaCondition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:FormulaCondition:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:FormulaCondition:collection','write:FormulaCondition'])]
    private ?Year $year = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:FormulaCondition:collection','write:FormulaCondition'])]
    private ?School $school = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['get:FormulaCondition:collection','write:FormulaCondition'])]
    private ?Level $level = null;

    #[ORM\Column]
    #[Groups(['get:FormulaCondition:collection', 'write:FormulaCondition','get:FormulaCondition:Item'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:FormulaCondition:collection', 'write:FormulaCondition'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:FormulaCondition:collection', 'write:FormulaCondition'])]
    private ?string $promotionConditionLogFn = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:FormulaCondition:collection', 'write:FormulaCondition'])]
    private ?string $logFn = null;

    #[ORM\ManyToOne]
    #[Groups(['get:FormulaCondition:collection','write:FormulaCondition'])]
    private ?PromotionCondition $promotionCondition;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:FormulaCondition:collection', 'write:FormulaCondition'])]
    private ?bool $isMain = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:FormulaCondition:collection', 'write:FormulaCondition'])]
    private ?string $attributeValues = null;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'formulaConditions')]
    #[Groups(['get:FormulaCondition:collection','write:FormulaCondition'])]
    #[ORM\JoinTable(name: 'school_formula_condition_formula_conditions')]
    private Collection $formulaConditions;

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

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;
        $this->is_archive = false;
        $this->formulaConditions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): FormulaCondition
    {
        $this->id = $id;
        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): FormulaCondition
    {
        $this->year = $year;
        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): FormulaCondition
    {
        $this->school = $school;
        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): FormulaCondition
    {
        $this->level = $level;
        return $this;
    }

    public function getIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function setIsMain(?bool $isMain): FormulaCondition
    {
        $this->isMain = $isMain;
        return $this;
    }

    public function getAttributeValues(): ?string
    {
        return $this->attributeValues;
    }

    public function setAttributeValues(?string $attributeValues): FormulaCondition
    {
        $this->attributeValues = $attributeValues;
        return $this;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): FormulaCondition
    {
        $this->institution = $institution;
        return $this;
    }

    public function getIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(?bool $is_enable): FormulaCondition
    {
        $this->is_enable = $is_enable;
        return $this;
    }

    public function getIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(?bool $is_archive): FormulaCondition
    {
        $this->is_archive = $is_archive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): FormulaCondition
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): FormulaCondition
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): FormulaCondition
    {
        $this->user = $user;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): FormulaCondition
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): FormulaCondition
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFormulaConditions(): Collection
    {
        return $this->formulaConditions;
    }

    public function addFormulaCondition(self $formulaCondition): self
    {
        if (!$this->formulaConditions->contains($formulaCondition)) {
            $this->formulaConditions->add($formulaCondition);
        }

        return $this;
    }

    public function removeFormulaCondition(self $formulaCondition): self
    {
        $this->formulaConditions->removeElement($formulaCondition);

        return $this;
    }

    public function getPromotionCondition(): ?PromotionCondition
    {
        return $this->promotionCondition;
    }

    public function setPromotionCondition(?PromotionCondition $promotionCondition): FormulaCondition
    {
        $this->promotionCondition = $promotionCondition;
        return $this;
    }

    public function getPromotionConditionLogFn(): ?string
    {
        return $this->promotionConditionLogFn;
    }

    public function setPromotionConditionLogFn(?string $promotionConditionLogFn): FormulaCondition
    {
        $this->promotionConditionLogFn = $promotionConditionLogFn;
        return $this;
    }

    public function getLogFn(): ?string
    {
        return $this->logFn;
    }

    public function setLogFn(?string $logFn): FormulaCondition
    {
        $this->logFn = $logFn;
        return $this;
    }
}
