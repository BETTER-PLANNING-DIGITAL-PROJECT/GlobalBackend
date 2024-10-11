<?php

namespace App\Entity\Security;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\Security\LayoutConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LayoutConfigurationRepository::class)]
#[ORM\Table(name: 'security_layout_configuration')]
#[ApiResource(
    operations:[
        new Get(
            uriTemplate: '/get/layout-configuration/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: [
                'groups' => ['get:LayoutConfiguration:collection'],
            ]
        ),
        new GetCollection(
            uriTemplate: '/get/layout-configuration',
            normalizationContext: [
                'groups' => ['get:LayoutConfiguration:collection'],
            ],
        ),
        ]
)]
class LayoutConfiguration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isProgram = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isLevel = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isSpeciality = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isOption = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isMinistryMatricule = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isInternalMatricule = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isDiploma = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isCycle = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isRegime = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isModule = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isSubject = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isNature = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isGuardianship = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isDepartment = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isTrainingType = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isSimpleHourlyRate = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isMultipleHourlyRate = null;

    #[ORM\Column]
    #[Groups(['get:LayoutConfiguration:collection'])]
    private ?bool $isBudget = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isIsProgram(): ?bool
    {
        return $this->isProgram;
    }

    public function setIsProgram(bool $isProgram): self
    {
        $this->isProgram = $isProgram;

        return $this;
    }

    public function isIsLevel(): ?bool
    {
        return $this->isLevel;
    }

    public function setIsLevel(bool $isLevel): self
    {
        $this->isLevel = $isLevel;

        return $this;
    }

    public function isIsSpeciality(): ?bool
    {
        return $this->isSpeciality;
    }

    public function setIsSpeciality(bool $isSpeciality): self
    {
        $this->isSpeciality = $isSpeciality;

        return $this;
    }

    public function isIsOption(): ?bool
    {
        return $this->isOption;
    }

    public function setIsOption(bool $isOption): self
    {
        $this->isOption = $isOption;

        return $this;
    }

    public function isIsMinistryMatricule(): ?bool
    {
        return $this->isMinistryMatricule;
    }

    public function setIsMinistryMatricule(bool $isMinistryMatricule): self
    {
        $this->isMinistryMatricule = $isMinistryMatricule;

        return $this;
    }

    public function isIsInternalMatricule(): ?bool
    {
        return $this->isInternalMatricule;
    }

    public function setIsInternalMatricule(bool $isInternalMatricule): self
    {
        $this->isInternalMatricule = $isInternalMatricule;

        return $this;
    }

    public function isIsDiploma(): ?bool
    {
        return $this->isDiploma;
    }

    public function setIsDiploma(bool $isDiploma): self
    {
        $this->isDiploma = $isDiploma;

        return $this;
    }

    public function isIsCycle(): ?bool
    {
        return $this->isCycle;
    }

    public function setIsCycle(bool $isCycle): self
    {
        $this->isCycle = $isCycle;

        return $this;
    }

    public function isIsRegime(): ?bool
    {
        return $this->isRegime;
    }

    public function setIsRegime(bool $isRegime): self
    {
        $this->isRegime = $isRegime;

        return $this;
    }

    public function isIsModule(): ?bool
    {
        return $this->isModule;
    }

    public function setIsModule(bool $isModule): self
    {
        $this->isModule = $isModule;

        return $this;
    }

    public function isIsSubject(): ?bool
    {
        return $this->isSubject;
    }

    public function setIsSubject(bool $isSubject): self
    {
        $this->isSubject = $isSubject;

        return $this;
    }

    public function isIsNature(): ?bool
    {
        return $this->isNature;
    }

    public function setIsNature(bool $isNature): self
    {
        $this->isNature = $isNature;

        return $this;
    }

    public function isIsMultipleHourlyRate(): ?bool
    {
        return $this->isMultipleHourlyRate;
    }

    public function setIsMultipleHourlyRate(bool $isMultipleHourlyRate): self
    {
        $this->isMultipleHourlyRate = $isMultipleHourlyRate;

        return $this;
    }

    public function isIsSimpleHourlyRate(): ?bool
    {
        return $this->isSimpleHourlyRate;
    }

    public function setIsSimpleHourlyRate(bool $isSimpleHourlyRate): self
    {
        $this->isSimpleHourlyRate = $isSimpleHourlyRate;

        return $this;
    }

    public function isIsTrainingType(): ?bool
    {
        return $this->isTrainingType;
    }

    public function setIsTrainingType(bool $isTrainingType): self
    {
        $this->isTrainingType = $isTrainingType;

        return $this;
    }

    public function isIsDepartment(): ?bool
    {
        return $this->isDepartment;
    }

    public function setIsDepartment(bool $isDepartment): self
    {
        $this->isDepartment = $isDepartment;

        return $this;
    }

    public function isIsGuardianship(): ?bool
    {
        return $this->isGuardianship;
    }

    public function setIsGuardianship(bool $isGuardianship): self
    {
        $this->isGuardianship = $isGuardianship;

        return $this;
    }

    public function isIsBudget(): ?bool
    {
        return $this->isBudget;
    }

    public function setIsBudget(bool $isBudget): self
    {
        $this->isBudget = $isBudget;

        return $this;
    }
}
