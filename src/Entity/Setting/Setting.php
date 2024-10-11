<?php

namespace App\Entity\Setting;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Setting\SettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
#[ApiResource]
class Setting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_barcode = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_batch_number_or_serial_number_on_delivery_note = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_batch_number_or_serial_number_on_invoice = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isIsBarcode(): ?bool
    {
        return $this->is_barcode;
    }

    public function setIsBarcode(?bool $is_barcode): self
    {
        $this->is_barcode = $is_barcode;

        return $this;
    }

    public function isIsBatchNumberOrSerialNumberOnDeliveryNote(): ?bool
    {
        return $this->is_batch_number_or_serial_number_on_delivery_note;
    }

    public function setIsBatchNumberOrSerialNumberOnDeliveryNote(?bool $is_batch_number_or_serial_number_on_delivery_note): self
    {
        $this->is_batch_number_or_serial_number_on_delivery_note = $is_batch_number_or_serial_number_on_delivery_note;

        return $this;
    }

    public function isIsBatchNumberOrSerialNumberOnInvoice(): ?bool
    {
        return $this->is_batch_number_or_serial_number_on_invoice;
    }

    public function setIsBatchNumberOrSerialNumberOnInvoice(?bool $is_batch_number_or_serial_number_on_invoice): self
    {
        $this->is_batch_number_or_serial_number_on_invoice = $is_batch_number_or_serial_number_on_invoice;

        return $this;
    }
}
