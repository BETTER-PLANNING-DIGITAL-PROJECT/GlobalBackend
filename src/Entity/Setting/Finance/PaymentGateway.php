<?php

namespace App\Entity\Setting\Finance;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\Setting\Finance\PaymentGatewayRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PaymentGatewayRepository::class)]
#[ORM\Table(name: 'setting_payment_gateway')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/payment/gateway/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:PaymentGateway:collection'],
            ],
        ),

        new GetCollection(
            uriTemplate: '/payment/gateway/get',
            normalizationContext: [
                'groups' => ['get:PaymentGateway:collection'],
            ],
        ),

        new Post(
            uriTemplate: '/payment/gateway/create',
            denormalizationContext: [
                'groups' => ['write:PaymentGateway'],
            ],
        //deserialize: false,
        ),
        new Put(
            uriTemplate: '/payment/gateway/edit/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:PaymentGateway'],
            ],
        ),
        new Delete(
            uriTemplate: '/payment/gateway/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]

)]
//#[UniqueEntity(
//    fields: ['name'],
//    message: 'This gateway already exist.',
//)]
class PaymentGateway
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:PaymentGateway:collection', 'get:Fee:collection', 'get:SaleSettlement:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:PaymentGateway:collection', 'get:Fee:collection', 'write:PaymentGateway', 'get:SaleSettlement:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Groups(['get:PaymentGateway:collection', 'get:Fee:collection', 'write:PaymentGateway', 'get:SaleSettlement:collection'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:PaymentGateway:collection', 'get:Fee:collection', 'write:PaymentGateway', 'get:SaleSettlement:collection'])]
    private ?string $key1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:PaymentGateway:collection', 'get:Fee:collection', 'write:PaymentGateway', 'get:SaleSettlement:collection'])]
    private ?string $key2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:PaymentGateway:collection', 'get:Fee:collection', 'write:PaymentGateway', 'get:SaleSettlement:collection'])]
    private ?string $key3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:PaymentGateway:collection', 'get:Fee:collection', 'write:PaymentGateway', 'get:SaleSettlement:collection'])]
    private ?string $betterPayCode = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $status = null;

    #[ORM\Column]
    private ?bool $isEnable = null;

    #[ORM\Column(length: 110, nullable: true)]
    #[Groups(['get:PaymentGateway:collection', 'get:Fee:collection', 'write:PaymentGateway', 'get:SaleSettlement:collection'])]
    private ?string $username = null;

    #[ORM\Column(length: 110, nullable: true)]
    #[Groups(['get:PaymentGateway:collection', 'get:Fee:collection', 'write:PaymentGateway', 'get:SaleSettlement:collection'])]
    private ?string $password = null;

    #[ORM\Column(length: 110, nullable: true)]
    #[Groups(['get:PaymentGateway:collection', 'get:Fee:collection', 'write:PaymentGateway', 'get:SaleSettlement:collection'])]
    private ?string $payTypeCode = null;

    #[ORM\Column(length: 110, nullable: true)]
    #[Groups(['get:PaymentGateway:collection', 'get:Fee:collection', 'write:PaymentGateway', 'get:SaleSettlement:collection'])]
    private ?string $accountNumber = null;

    #[ORM\ManyToOne]
    #[Groups(['get:PaymentGateway:collection', 'get:Fee:collection', 'write:PaymentGateway', 'get:SaleSettlement:collection'])]
    private ?PaymentMethod $paymentMethod = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PaymentGateway:collection', 'get:Fee:collection', 'write:PaymentGateway', 'get:SaleSettlement:collection'])]
    private ?int $feeRate = null;

    public function __construct()
    {
        $this->isEnable = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getKey1(): ?string
    {
        return $this->key1;
    }

    public function setKey1(?string $key1): static
    {
        $this->key1 = $key1;

        return $this;
    }

    public function getKey2(): ?string
    {
        return $this->key2;
    }

    public function setKey2(?string $key2): static
    {
        $this->key2 = $key2;

        return $this;
    }

    public function getKey3(): ?string
    {
        return $this->key3;
    }

    public function setKey3(?string $key3): static
    {
        $this->key3 = $key3;

        return $this;
    }

    public function getBetterPayCode(): ?string
    {
        return $this->betterPayCode;
    }

    public function setBetterPayCode(?string $betterPayCode): static
    {
        $this->betterPayCode = $betterPayCode;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isEnable(): ?bool
    {
        return $this->isEnable;
    }

    public function setEnable(bool $isEnable): static
    {
        $this->isEnable = $isEnable;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPayTypeCode(): ?string
    {
        return $this->payTypeCode;
    }

    public function setPayTypeCode(?string $payTypeCode): self
    {
        $this->payTypeCode = $payTypeCode;

        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): self
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?PaymentMethod $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getFeeRate(): ?int
    {
        return $this->feeRate;
    }

    public function setFeeRate(?int $feeRate): self
    {
        $this->feeRate = $feeRate;

        return $this;
    }
}
