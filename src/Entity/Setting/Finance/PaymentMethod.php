<?php

namespace App\Entity\Setting\Finance;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Setting\Finance\GetPaymentMethodGatewayController;
use App\Repository\Setting\Finance\PaymentMethodRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PaymentMethodRepository::class)]
#[ORM\Table(name: 'setting_payment_method')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/payment/method/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:PaymentMethod:collection'],
            ],
        ),
        new Get(
            uriTemplate: '/payment/gateway/method/{id}',
            requirements: ['id' => '\d+'],
            controller: GetPaymentMethodGatewayController::class,
            normalizationContext: [
                'groups' => ['get:PaymentGateway:collection'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/get/payment/methods',
            normalizationContext: [
                'groups' => ['get:PaymentMethod:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/payment/method',
            denormalizationContext: [
                'groups' => ['write:PaymentMethod'],
            ],
        //processor: SystemProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/payment/method/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:PaymentMethod'],
            ],
        ),
        new Delete(
            uriTemplate: '/delete/payment/method/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class PaymentMethod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:PaymentMethod:collection', 'get:SaleSettlement:collection','get:SaleSettlement:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Groups(['get:PaymentMethod:collection', 'get:SaleSettlement:collection','get:SaleSettlement:collection'])]
    private ?string $code = null;

    #[ORM\Column(length: 110)]
    #[Groups(['get:PaymentMethod:collection', 'get:SaleSettlement:collection','get:SaleSettlement:collection'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PaymentMethod:collection', 'get:SaleSettlement:collection'])]
    private ?bool $isDefault = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PaymentMethod:collection', 'get:SaleSettlement:collection'])]
    private ?bool $isCashDesk = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PaymentMethod:collection', 'get:SaleSettlement:collection'])]
    private ?bool $isBank = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:PaymentMethod:collection', 'get:SaleSettlement:collection'])]
    private ?bool $isPaymentGateway = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(?bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function isIsCashDesk(): ?bool
    {
        return $this->isCashDesk;
    }

    public function setIsCashDesk(?bool $isCashDesk): self
    {
        $this->isCashDesk = $isCashDesk;

        return $this;
    }

    public function isIsBank(): ?bool
    {
        return $this->isBank;
    }

    public function setIsBank(?bool $isBank): self
    {
        $this->isBank = $isBank;

        return $this;
    }

    public function isIsPaymentGateway(): ?bool
    {
        return $this->isPaymentGateway;
    }

    public function setIsPaymentGateway(?bool $isPaymentGateway): self
    {
        $this->isPaymentGateway = $isPaymentGateway;

        return $this;
    }
}
