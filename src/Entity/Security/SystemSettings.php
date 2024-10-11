<?php

namespace App\Entity\Security;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\Security\SystemSettingsRepository;
use App\State\Processor\Global\SettingProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SystemSettingsRepository::class)]
#[ORM\Table(name: 'security_system_settings')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/system-settings/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:SystemSettings:collection'],
            ],
        ),
        new GetCollection(
            uriTemplate: '/get/system-settings',
            normalizationContext: [
                'groups' => ['get:SystemSettings:collection'],
            ],
        ),
        new Post(
            uriTemplate: '/create/system-settings',
            denormalizationContext: [
                'groups' => ['write:SystemSettings'],
            ],
            processor: SettingProcessor::class,
        ),
        new Put(
            uriTemplate: '/edit/system-settings/{id}',
            requirements: ['id' => '\d+'],
            denormalizationContext: [
                'groups' => ['write:SystemSettings'],
            ],
        ),
        new Delete(
            uriTemplate: '/system-settings/delete/{id}',
            requirements: ['id' => '\d+'],
        ),
    ]
)]
class SystemSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:SystemSettings:collection'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(['get:SystemSettings:collection', 'write:SystemSettings'])]
    private ?User $user = null;

    #[ORM\Column]
    #[Groups(['get:SystemSettings:collection', 'write:SystemSettings'])]
    private ?bool $isSingleCompany = null;
    #[ORM\Column]
    #[Groups(['get:SystemSettings:collection', 'write:SystemSettings'])]
    private ?bool $isBranches = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(){
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function isIsSingleCompany(): ?bool
    {
        return $this->isSingleCompany;
    }

    public function setIsSingleCompany(bool $isSingleCompany): self
    {
        $this->isSingleCompany = $isSingleCompany;

        return $this;
    }

    public function isIsBranches(): ?bool
    {
        return $this->isBranches;
    }

    public function setIsBranches(bool $isBranches): self
    {
        $this->isBranches = $isBranches;

        return $this;
    }
}
