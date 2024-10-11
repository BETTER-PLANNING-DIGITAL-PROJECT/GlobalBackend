<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Period\Calculation\Student;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\WorkRemarksUtil;
use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated;

class WorkRemarksPeriodUtil extends WorkRemarksUtil
{

    private string $grantThCompositionMethod;

    public function __construct(
        // Remarques de l'evaluation (Encouragements , Blames , Felicitations etc...)
        protected ?float $blameWorkAverage,
        protected ?float $warningWorkAverage,
        protected ?float $grantEncouragementAverage,
        protected ?float $grantCongratulationAverage,
        protected ?float $grantThCompositionAverage,
    )
    {
        parent::__construct($this->blameWorkAverage,$this->warningWorkAverage,$this->grantEncouragementAverage,$this->grantCongratulationAverage);
        $this->grantThCompositionMethod = 'setGrantThComposition'.(int) isset($this->grantThCompositionAverage);
        $hasBadRemarks = count($this->badRemarksData) > 0;
        $hasGoodRemarks = count($this->goodRemarksData) > 0;
        $this->setRemarksMethod = 'setRemarksBad'.(int)$hasBadRemarks.'Good'.(int)$hasGoodRemarks;
    }

    function setRemarks(mixed $markSequenceGeneralAverageCalculated) : ?float {
        $average = parent::setRemarks($markSequenceGeneralAverageCalculated);
        if ($average) $this->{$this->grantThCompositionMethod}($markSequenceGeneralAverageCalculated,$average);
//        if (isset($average) && $average >= $grantTh)
        return $average;
    }

    function setGrantThComposition0(mixed $markSequenceGeneralAverageCalculated,$average) {

    }

    function setGrantThComposition1(mixed $markSequenceGeneralAverageCalculated,$average) {
        if ($average >= $this->grantThCompositionAverage) $markSequenceGeneralAverageCalculated->setGrantThComposition(true);
    }

}