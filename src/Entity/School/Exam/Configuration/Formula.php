<?php

namespace App\Entity\School\Exam\Configuration;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Security\Institution\Institution;
use App\Entity\Security\Session\Year;
use App\Repository\School\Exam\Configuration\FormulaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormulaRepository::class)]
#[ORM\Table(name: 'school_formula')]
#[ApiResource]
class Formula
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // year

    #[ORM\ManyToOne]
    private ?Institution $institution = null;

    #[ORM\Column(nullable: true)]
    private ?bool $avSeScrWeCoef = null;

    #[ORM\Column(nullable: true)]
    private ?bool $avSeAvSeEx = null;

    #[ORM\Column(nullable: true)]
    private ?bool $anAvGrWeCoef = null;

    #[ORM\Column(nullable: true)]
    private ?bool $anAvArtAvEx = null;

    #[ORM\Column(nullable: true)]
    private ?bool $anAvArtAVQtSe = null;


    private ?string $drWarn1 = null;

    #[ORM\Column(nullable: true)]
    private ?float $absences1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $drWarn2 = null;

    #[ORM\Column(nullable: true)]
    private ?float $exclusionDay1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bmCdt1 = null;

    #[ORM\Column(nullable: true)]
    private ?float $absences2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bmCdt2 = null;

    #[ORM\Column(nullable: true)]
    private ?float $exclusionDay2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bmWrk = null;

    #[ORM\Column(nullable: true)]
    private ?float $average1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $wrnWrk = null;

    #[ORM\Column(nullable: true)]
    private ?float $average2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $grtThCmp = null;

    #[ORM\Column(nullable: true)]
    private ?float $ExamAv = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $grtThAnn = null;

    #[ORM\Column(nullable: true)]
    private ?float $yearAv = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $encouragement = null;

    #[ORM\Column(nullable: true)]
    private ?float $average3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $congratulation = null;

    #[ORM\Column(nullable: true)]
    private ?float $average4 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $refuse1 = null;

    #[ORM\Column(nullable: true)]
    private ?float $absences3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $refuse2 = null;

    #[ORM\Column(nullable: true)]
    private ?float $exclusionDay3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $refuse3 = null;

    #[ORM\Column(nullable: true)]
    private ?float $blamenumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $refuse4 = null;

    #[ORM\Column(nullable: true)]
    private ?float $sethours = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstitution(): ?Institution
    {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

    public function isAvSeScrWeCoef(): ?bool
    {
        return $this->avSeScrWeCoef;
    }

    public function setAvSeScrWeCoef(?bool $avSeScrWeCoef): self
    {
        $this->avSeScrWeCoef = $avSeScrWeCoef;

        return $this;
    }

    public function isAvSeAvSeEx(): ?bool
    {
        return $this->avSeAvSeEx;
    }

    public function setAvSeAvSeEx(?bool $avSeAvSeEx): self
    {
        $this->avSeAvSeEx = $avSeAvSeEx;

        return $this;
    }

    public function isAnAvGrWeCoef(): ?bool
    {
        return $this->anAvGrWeCoef;
    }

    public function setAnAvGrWeCoef(?bool $anAvGrWeCoef): self
    {
        $this->anAvGrWeCoef = $anAvGrWeCoef;

        return $this;
    }

    public function isAnAvArtAvEx(): ?bool
    {
        return $this->anAvArtAvEx;
    }

    public function setAnAvArtAvEx(?bool $anAvArtAvEx): self
    {
        $this->anAvArtAvEx = $anAvArtAvEx;

        return $this;
    }

    public function isAnAvArtAVQtSe(): ?bool
    {
        return $this->anAvArtAVQtSe;
    }

    public function setAnAvArtAVQtSe(?bool $anAvArtAVQtSe): self
    {
        $this->anAvArtAVQtSe = $anAvArtAVQtSe;

        return $this;
    }

    public function getDrWarn1(): ?string
    {
        return $this->drWarn1;
    }

    public function setDrWarn1(?string $drWarn1): self
    {
        $this->drWarn1 = $drWarn1;

        return $this;
    }

    public function getAbsences1(): ?float
    {
        return $this->absences1;
    }

    public function setAbsences1(?float $absences1): self
    {
        $this->absences1 = $absences1;

        return $this;
    }

    public function getDrWarn2(): ?string
    {
        return $this->drWarn2;
    }

    public function setDrWarn2(?string $drWarn2): self
    {
        $this->drWarn2 = $drWarn2;

        return $this;
    }

    public function getExclusionDay1(): ?float
    {
        return $this->exclusionDay1;
    }

    public function setExclusionDay1(?float $exclusionDay1): self
    {
        $this->exclusionDay1 = $exclusionDay1;

        return $this;
    }

    public function getBmCdt1(): ?string
    {
        return $this->bmCdt1;
    }

    public function setBmCdt1(?string $bmCdt1): self
    {
        $this->bmCdt1 = $bmCdt1;

        return $this;
    }

    public function getAbsences2(): ?float
    {
        return $this->absences2;
    }

    public function setAbsences2(?float $absences2): self
    {
        $this->absences2 = $absences2;

        return $this;
    }

    public function getBmCdt2(): ?string
    {
        return $this->bmCdt2;
    }

    public function setBmCdt2(?string $bmCdt2): self
    {
        $this->bmCdt2 = $bmCdt2;

        return $this;
    }

    public function getExclusionDay2(): ?float
    {
        return $this->exclusionDay2;
    }

    public function setExclusionDay2(?float $exclusionDay2): self
    {
        $this->exclusionDay2 = $exclusionDay2;

        return $this;
    }

    public function getBmWrk(): ?string
    {
        return $this->bmWrk;
    }

    public function setBmWrk(?string $bmWrk): self
    {
        $this->bmWrk = $bmWrk;

        return $this;
    }

    public function getAverage1(): ?float
    {
        return $this->average1;
    }

    public function setAverage1(?float $average1): self
    {
        $this->average1 = $average1;

        return $this;
    }

    public function getWrnWrk(): ?string
    {
        return $this->wrnWrk;
    }

    public function setWrnWrk(?string $wrnWrk): self
    {
        $this->wrnWrk = $wrnWrk;

        return $this;
    }

    public function getAverage2(): ?float
    {
        return $this->average2;
    }

    public function setAverage2(?float $average2): self
    {
        $this->average2 = $average2;

        return $this;
    }

    public function getGrtThCmp(): ?string
    {
        return $this->grtThCmp;
    }

    public function setGrtThCmp(?string $grtThCmp): self
    {
        $this->grtThCmp = $grtThCmp;

        return $this;
    }

    public function getExamAv(): ?float
    {
        return $this->ExamAv;
    }

    public function setExamAv(?float $ExamAv): self
    {
        $this->ExamAv = $ExamAv;

        return $this;
    }

    public function getGrtThAnn(): ?string
    {
        return $this->grtThAnn;
    }

    public function setGrtThAnn(?string $grtThAnn): self
    {
        $this->grtThAnn = $grtThAnn;

        return $this;
    }

    public function getYearAv(): ?float
    {
        return $this->yearAv;
    }

    public function setYearAv(?float $yearAv): self
    {
        $this->yearAv = $yearAv;

        return $this;
    }

    public function getEncouragement(): ?string
    {
        return $this->encouragement;
    }

    public function setEncouragement(?string $encouragement): self
    {
        $this->encouragement = $encouragement;

        return $this;
    }

    public function getAverage3(): ?float
    {
        return $this->average3;
    }

    public function setAverage3(?float $average3): self
    {
        $this->average3 = $average3;

        return $this;
    }

    public function getCongratulation(): ?string
    {
        return $this->congratulation;
    }

    public function setCongratulation(?string $congratulation): self
    {
        $this->congratulation = $congratulation;

        return $this;
    }

    public function getAverage4(): ?float
    {
        return $this->average4;
    }

    public function setAverage4(?float $average4): self
    {
        $this->average4 = $average4;

        return $this;
    }

    public function getRefuse1(): ?string
    {
        return $this->refuse1;
    }

    public function setRefuse1(?string $refuse1): self
    {
        $this->refuse1 = $refuse1;

        return $this;
    }

    public function getAbsences3(): ?float
    {
        return $this->absences3;
    }

    public function setAbsences3(?float $absences3): self
    {
        $this->absences3 = $absences3;

        return $this;
    }

    public function getRefuse2(): ?string
    {
        return $this->refuse2;
    }

    public function setRefuse2(?string $refuse2): self
    {
        $this->refuse2 = $refuse2;

        return $this;
    }

    public function getExclusionDay3(): ?float
    {
        return $this->exclusionDay3;
    }

    public function setExclusionDay3(?float $exclusionDay3): self
    {
        $this->exclusionDay3 = $exclusionDay3;

        return $this;
    }

    public function getRefuse3(): ?string
    {
        return $this->refuse3;
    }

    public function setRefuse3(?string $refuse3): self
    {
        $this->refuse3 = $refuse3;

        return $this;
    }

    public function getBlamenumber(): ?float
    {
        return $this->blamenumber;
    }

    public function setBlamenumber(?float $blamenumber): self
    {
        $this->blamenumber = $blamenumber;

        return $this;
    }

    public function getRefuse4(): ?string
    {
        return $this->refuse4;
    }

    public function setRefuse4(?string $refuse4): self
    {
        $this->refuse4 = $refuse4;

        return $this;
    }

    public function getSethours(): ?float
    {
        return $this->sethours;
    }

    public function setSethours(?float $sethours): self
    {
        $this->sethours = $sethours;

        return $this;
    }
}
