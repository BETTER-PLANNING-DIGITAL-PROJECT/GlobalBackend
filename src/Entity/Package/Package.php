<?php

namespace App\Entity\Package;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Inventory\Location;
use App\Repository\Package\PackageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PackageRepository::class)]
#[ApiResource]
class Package
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?PackageType $packageType = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $packagingAt = null;

    #[ORM\ManyToOne]
    private ?Location $location = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPackageType(): ?PackageType
    {
        return $this->packageType;
    }

    public function setPackageType(?PackageType $packageType): self
    {
        $this->packageType = $packageType;

        return $this;
    }

    public function getPackagingAt(): ?\DateTimeInterface
    {
        return $this->packagingAt;
    }

    public function setPackagingAt(\DateTimeInterface $packagingAt): self
    {
        $this->packagingAt = $packagingAt;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }
}
