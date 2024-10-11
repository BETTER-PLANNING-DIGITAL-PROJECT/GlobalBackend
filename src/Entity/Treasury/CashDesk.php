<?php

namespace App\Entity\Treasury;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Treasury\CashDeskOpenController;
use App\Controller\Treasury\GetCashDeskController;
use App\Controller\Treasury\GetCurrentUserCashDeskController;
use App\Controller\Treasury\PostCashDeskController;
use App\Controller\Treasury\PutCashDeskController;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Finance\Currency;
use App\Repository\Treasury\CashDeskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CashDeskRepository::class)]
#[ORM\Table(name: 'treasury_cash_desk')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/cash-desk/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:CashDesk:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/current-user/cash-desk',
            controller: GetCurrentUserCashDeskController::class,
            normalizationContext: [
                'groups' => ['get:CashDesk:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/cash-desk',
            controller: GetCashDeskController::class,
            normalizationContext: [
                'groups' => ['get:CashDesk:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Post(
            uriTemplate: '/create/cash-desk',
            controller: PostCashDeskController::class,
            denormalizationContext: [
                'groups' => ['write:CashDesk'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/cash-desk/{id}',
            requirements: ['id' => '\d+'],
            controller: PutCashDeskController::class,
            denormalizationContext: [
                'groups' => ['write:CashDesk'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Delete(
            uriTemplate: '/delete/cash-desk/{id}',
            requirements: ['id' => '\d+'],
        ),
		// Opn cash desk
        new Put(
            uriTemplate: '/open/cash-desk/{id}',
            requirements: ['id' => '\d+'],
            controller: CashDeskOpenController::class,
            denormalizationContext: [
                'groups' => ['write:CashDesk'],
            ],

        ),
    ]

)]
class CashDesk
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:CashDesk:collection','get:CashDeskHistory:collection','get:BankOperation:collection','get:CashDeskOperation:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:CashDesk:collection', 'write:CashDesk', 'get:CashDeskOperation:collection', 'get:CashDeskHistory:collection'])]
    private ?string $code = null;

    #[ORM\ManyToOne]
    #[Groups(['get:CashDesk:collection', 'write:CashDesk'])]
    private ?Currency $currency = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CashDesk:collection', 'write:CashDesk'])]
    private ?bool $isOpen = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:CashDesk:collection', 'write:CashDesk'])]
    private ?bool $isMain = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['get:CashDesk:collection', 'write:CashDesk'])]
    private ?\DateTimeImmutable $lastOpenAt = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['get:CashDesk:collection', 'write:CashDesk'])]
    private ?\DateTimeImmutable $lastClosedAt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:CashDesk:collection', 'write:CashDesk'])]
    private ?string $balance = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:CashDesk:collection', 'write:CashDesk'])]
    private ?string $beginningBalance = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:CashDesk:collection', 'write:CashDesk'])]
    private ?string $dailyDeposit = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Groups(['get:CashDesk:collection', 'write:CashDesk'])]
    private ?string $dailyWithdrawal = null;

    #[ORM\ManyToOne]
    #[Groups(['get:CashDesk:collection', 'write:CashDesk'])]
    private ?User $operator = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:CashDesk:collection', 'write:CashDesk'])]
    private ?Branch $branch;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Institution $institution;

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

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->is_enable = true;

        $this->balance = 0;
        $this->beginningBalance = 0;
        $this->dailyDeposit = 0;
        $this->dailyWithdrawal = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(?Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function isIsOpen(): ?bool
    {
        return $this->isOpen;
    }

    public function setIsOpen(?bool $isOpen): self
    {
        $this->isOpen = $isOpen;

        return $this;
    }

    public function isIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function setIsMain(?bool $isMain): self
    {
        $this->isMain = $isMain;

        return $this;
    }

    public function getLastOpenAt(): ?\DateTimeImmutable
    {
        return $this->lastOpenAt;
    }

    public function setLastOpenAt(?\DateTimeImmutable $lastOpenAt): self
    {
        $this->lastOpenAt = $lastOpenAt;

        return $this;
    }

    public function getLastClosedAt(): ?\DateTimeImmutable
    {
        return $this->lastClosedAt;
    }

    public function setLastClosedAt(?\DateTimeImmutable $lastClosedAt): self
    {
        $this->lastClosedAt = $lastClosedAt;

        return $this;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(?string $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getBeginningBalance(): ?string
    {
        return $this->beginningBalance;
    }

    public function setBeginningBalance(?string $beginningBalance): self
    {
        $this->beginningBalance = $beginningBalance;

        return $this;
    }

    public function getDailyDeposit(): ?string
    {
        return $this->dailyDeposit;
    }

    public function setDailyDeposit(?string $dailyDeposit): self
    {
        $this->dailyDeposit = $dailyDeposit;

        return $this;
    }

    public function getDailyWithdrawal(): ?string
    {
        return $this->dailyWithdrawal;
    }

    public function setDailyWithdrawal(?string $dailyWithdrawal): self
    {
        $this->dailyWithdrawal = $dailyWithdrawal;

        return $this;
    }

    public function getOperator(): ?User
    {
        return $this->operator;
    }

    public function setOperator(?User $operator): self
    {
        $this->operator = $operator;

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

    public function getBranch(): ?Branch
    {
        return $this->branch;
    }

    public function setBranch(?Branch $branch): self
    {
        $this->branch = $branch;

        return $this;
    }
}
