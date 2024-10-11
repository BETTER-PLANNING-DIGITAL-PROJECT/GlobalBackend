<?php

namespace App\Entity\Partner;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Billing\Sale\GetCustomerRegistrationFeeController;
use App\Controller\Partner\GetCustomerController;
use App\Controller\Partner\PostCustomerAndContactController;
use App\Controller\Partner\PutCustomerController;
use App\Entity\Security\Institution\Branch;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Entity\Security\User;
use App\Entity\Setting\Person\Civility;
use App\Entity\Treasury\BankAccount;
use App\Repository\Partner\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\Table(name: 'partner_customer')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/customer/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:Customer:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new Get(
            uriTemplate: '/get/customer/registration/item/fees/{id}/',
            requirements: ['id' => '\d+'],
            controller: GetCustomerRegistrationFeeController::class,
            normalizationContext: [
                'groups' => ['get:Customer:collection'],
                'datetime_format'=> 'Y-m-d'
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/customer',
            controller: GetCustomerController::class,
            order: ['id' => 'DESC'],
            normalizationContext: [
                'groups' => ['get:Customer:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/customer',
            controller: PostCustomerAndContactController::class,
            denormalizationContext: [
                'groups' => ['write:Customer'],
            ],
        ),
        new Put(
            uriTemplate: '/edit/customer/{id}',
            requirements: ['id' => '\d+'],
            controller: PutCustomerController::class,
            denormalizationContext: [
                'groups' => ['write:Customer'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/customer/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]

)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:Customer:collection','get:SaleInvoice:collection','get:SaleReturnInvoice:collection','get:SaleInvoice:collection','get:SaleSettlement:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Code may not be blank')]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name may not be blank')]
    #[Groups(['get:Customer:collection', 'write:Customer','get:SaleInvoice:collection','get:SaleReturnInvoice:collection','get:SaleInvoice:collection','get:SaleSettlement:collection'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?Civility $civility = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?string $idCard = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?\DateTimeImmutable $expiredAt = null;

    // businessCategory

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?string $taxpayernumber = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?string $businessnumber = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Customer:collection', 'write:Customer','get:SaleInvoice:collection'])]
    private ?string $phone = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Customer:collection', 'write:Customer','get:SaleInvoice:collection'])]
    private ?string $email = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?string $postbox = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?string $website = null;

    //type : string
    //category: partnerCategory
    //paymentMethod

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?int $paymentDelay = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?bool $isTva = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?BankAccount $bankAccount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?float $debit = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?float $credit = null;

    #[ORM\ManyToOne]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?PartnerCategory $partnerCategory = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
    private ?Contact $contact = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['get:Customer:collection', 'write:Customer'])]
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCivility(): ?Civility
    {
        return $this->civility;
    }

    public function setCivility(?Civility $civility): self
    {
        $this->civility = $civility;

        return $this;
    }

    public function getIdCard(): ?string
    {
        return $this->idCard;
    }

    public function setIdCard(?string $idCard): self
    {
        $this->idCard = $idCard;

        return $this;
    }

    public function getExpiredAt(): ?\DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(?\DateTimeImmutable $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    public function getTaxpayernumber(): ?string
    {
        return $this->taxpayernumber;
    }

    public function setTaxpayernumber(?string $taxpayernumber): self
    {
        $this->taxpayernumber = $taxpayernumber;

        return $this;
    }

    public function getBusinessnumber(): ?string
    {
        return $this->businessnumber;
    }

    public function setBusinessnumber(string $businessnumber): self
    {
        $this->businessnumber = $businessnumber;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostbox(): ?string
    {
        return $this->postbox;
    }

    public function setPostbox(?string $postbox): self
    {
        $this->postbox = $postbox;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getPaymentDelay(): ?int
    {
        return $this->paymentDelay;
    }

    public function setPaymentDelay(?int $paymentDelay): self
    {
        $this->paymentDelay = $paymentDelay;

        return $this;
    }

    public function isIsTva(): ?bool
    {
        return $this->isTva;
    }

    public function setIsTva(?bool $isTva): self
    {
        $this->isTva = $isTva;

        return $this;
    }

    public function getBankAccount(): ?BankAccount
    {
        return $this->bankAccount;
    }

    public function setBankAccount(?BankAccount $bankAccount): self
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    public function getDebit(): ?float
    {
        return $this->debit;
    }

    public function setDebit(?float $debit): self
    {
        $this->debit = $debit;

        return $this;
    }

    public function getCredit(): ?float
    {
        return $this->credit;
    }

    public function setCredit(?float $credit): self
    {
        $this->credit = $credit;

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

    public function getPartnerCategory(): ?PartnerCategory
    {
        return $this->partnerCategory;
    }

    public function setPartnerCategory(?PartnerCategory $partnerCategory): self
    {
        $this->partnerCategory = $partnerCategory;

        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): self
    {
        $this->contact = $contact;

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