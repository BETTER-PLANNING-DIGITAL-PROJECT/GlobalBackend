<?php

namespace App\Entity\School\Exam\Configuration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\School\Schooling\Configuration\School;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\PromotionConditionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PromotionConditionRepository::class)]
#[ORM\Table(name: 'school_promotion_conditions')]
#[ApiResource(
    operations:[
        new GetCollection(
            uriTemplate: '/get/promotion/condition',
            normalizationContext: [
                'groups' => ['get:PromotionCondition:collection','get:PromotionCondition:collection1'],
            ],
        ),
        new Get(
            uriTemplate: '/get/promotion/condition/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:PromotionCondition:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/promotion/condition',
            denormalizationContext: [
                'groups' => ['write:PromotionCondition'],
            ],
        ),new Put(
            uriTemplate: '/edit/promotion/condition/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:PromotionCondition'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/promotion/condition/{id}',
            requirements: ['id' => '\d+']
        )
    ]
)]
#[UniqueEntity(fields: 'name',errorPath: 'name')]
#[UniqueEntity(fields: 'attribute',errorPath: 'attribute')]
#[UniqueEntity(fields: 'label',errorPath: 'label')]
class PromotionCondition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:PromotionCondition:collection'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['get:PromotionCondition:collection', 'write:PromotionCondition','get:FormulaCondition:collection','get:PromotionCondition:collection1'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:PromotionCondition:collection', 'write:PromotionCondition'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PromotionCondition:collection', 'write:PromotionCondition'])]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PromotionCondition:collection', 'write:PromotionCondition'])]
    private ?string $attribute = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PromotionCondition:collection', 'write:PromotionCondition'])]
    private ?int $position = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:PromotionCondition:collection', 'write:PromotionCondition'])]
    private ?string $conditionFn = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PromotionCondition:collection', 'write:PromotionCondition'])]
    private ?string $conditionFnCall = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:PromotionCondition:collection', 'write:PromotionCondition'])]
    private ?string $label = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:PromotionCondition:collection', 'write:PromotionCondition'])]
    private ?string $log = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:PromotionCondition:collection', 'write:PromotionCondition'])]
    private ?string $evalCondition = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:PromotionCondition:collection', 'write:PromotionCondition','get:FormulaCondition:collection'])]
    private ?string $logFn = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['get:PromotionCondition:collection', 'write:PromotionCondition','get:FormulaCondition:collection'])]
    private ?string $labels = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PromotionCondition:collection', 'write:PromotionCondition'])]
    private ?string $andOr = null;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'promotionConditions')]
    #[ORM\JoinTable(name: 'school_promotion_condition_promotion_conditions')]
    #[Groups(['get:PromotionCondition:collection','write:PromotionCondition','get:PromotionCondition:collection1'])]
    private Collection $promotionConditions;

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
        $this->promotionConditions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): PromotionCondition
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): PromotionCondition
    {
        $this->description = $description;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): PromotionCondition
    {
        $this->type = $type;
        return $this;
    }

    public function getAttribute(): ?string
    {
        return $this->attribute;
    }

    public function setAttribute(?string $attribute): PromotionCondition
    {
        $this->attribute = $attribute;
        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): PromotionCondition
    {
        $this->position = $position;
        return $this;
    }

    public function getConditionFn(): ?string
    {
        return $this->conditionFn;
    }

    public function setConditionFn(?string $conditionFn): PromotionCondition
    {
        $this->conditionFn = $conditionFn;
        return $this;
    }

    public function getConditionFnCall(): ?string
    {
        return $this->conditionFnCall;
    }

    public function setConditionFnCall(?string $conditionFnCall): PromotionCondition
    {
        $this->conditionFnCall = $conditionFnCall;
        return $this;
    }


    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): PromotionCondition
    {
        $this->label = $label;
        return $this;
    }

    public function getLog(): ?string
    {
        return $this->log;
    }

    public function setLog(?string $log): PromotionCondition
    {
        $this->log = $log;
        return $this;
    }

    public function getEvalCondition(): ?string
    {
        return $this->evalCondition;
    }

    public function setEvalCondition(?string $evalCondition): PromotionCondition
    {
        $this->evalCondition = $evalCondition;
        return $this;
    }

    public function getLogFn(): ?string
    {
        return $this->logFn;
    }

    public function setLogFn(?string $logFn): PromotionCondition
    {
        $this->logFn = $logFn;
        return $this;
    }

    public function getAndOr(): ?string
    {
        return $this->andOr;
    }

    public function setAndOr(?string $andOr): PromotionCondition
    {
        $this->andOr = $andOr;
        return $this;
    }

    public function getIsEnable(): ?bool
    {
        return $this->is_enable;
    }

    public function setIsEnable(?bool $is_enable): PromotionCondition
    {
        $this->is_enable = $is_enable;
        return $this;
    }

    public function getIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    public function setIsArchive(?bool $is_archive): PromotionCondition
    {
        $this->is_archive = $is_archive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): PromotionCondition
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): PromotionCondition
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): PromotionCondition
    {
        $this->user = $user;
        return $this;
    }

    public function getLabels(): ?string
    {
        return $this->labels;
    }

    public function setLabels(?string $labels): PromotionCondition
    {
        $this->labels = $labels;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getPromotionConditions(): Collection
    {
        return $this->promotionConditions;
    }

    public function addPromotionCondition(self $promotionCondition): self
    {
        if (!$this->promotionConditions->contains($promotionCondition)) {
            $this->promotionConditions->add($promotionCondition);
        }

        return $this;
    }

    public function removePromotionCondition(self $promotionCondition): self
    {
        $this->promotionConditions->removeElement($promotionCondition);

        return $this;
    }
}
