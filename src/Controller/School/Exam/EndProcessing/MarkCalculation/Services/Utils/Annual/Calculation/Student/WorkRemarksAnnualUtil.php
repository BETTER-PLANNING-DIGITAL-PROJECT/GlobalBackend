<?php

namespace App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\Annual\Calculation\Student;

use App\Controller\School\Exam\EndProcessing\MarkCalculation\Services\Utils\General\WorkRemarksUtil;
use App\Entity\School\Exam\Operation\Period\GeneralAverage\MarkPeriodGeneralAverageCalculated;
use App\Entity\School\Exam\Operation\Sequence\GeneralAverage\MarkSequenceGeneralAverageCalculated;

class WorkRemarksAnnualUtil extends WorkRemarksUtil
{

    private string $grantThAnnualMethod;

    public function __construct(
        // Remarques de l'evaluation (Encouragements , Blames , Felicitations etc...)
        protected ?float $blameWorkAverage,
        protected ?float $warningWorkAverage,
        protected ?float $grantEncouragementAverage,
        protected ?float $grantCongratulationAverage,
        protected ?float $grantThAnnualAverage,
    )
    {
        parent::__construct($this->blameWorkAverage,$this->warningWorkAverage,$this->grantEncouragementAverage,$this->grantCongratulationAverage);
        $this->grantThAnnualMethod = 'setGrantThAnnual'.(int) isset($this->grantThAnnualAverage);
        $hasBadRemarks = count($this->badRemarksData) > 0;
        $hasGoodRemarks = count($this->goodRemarksData) > 0;
        $this->setRemarksMethod = 'setRemarksBad'.(int)$hasBadRemarks.'Good'.(int)$hasGoodRemarks;
    }

    function setRemarks(mixed $markSequenceGeneralAverageCalculated) : ?float {
        $average = parent::setRemarks($markSequenceGeneralAverageCalculated);
        if ($average) $this->{$this->grantThAnnualMethod}($markSequenceGeneralAverageCalculated,$average);
//        if (isset($average) && $average >= $grantTh)
        return $average;
    }

    function setGrantThAnnual0(mixed $markSequenceGeneralAverageCalculated,$average) {

    }

    function setGrantThAnnual1(mixed $markSequenceGeneralAverageCalculated,$average) {
        if ($average >= $this->grantThAnnualAverage) $markSequenceGeneralAverageCalculated->setGrantThAnnual(true);
    }

}